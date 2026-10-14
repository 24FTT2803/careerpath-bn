<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiicfEdition;
use App\Models\BiicfEditionChange;
use App\Services\Biicf\EditionApplier;
use App\Services\Biicf\EditionComparer;
use App\Services\Biicf\EditionExtractor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Loading a new edition of the reference book.
 *
 * Four steps, and they are deliberately separate: upload, read,
 * review, apply. The framework is what every recommendation and
 * every role page rests on, so nothing an upload says reaches a
 * student until an administrator has been through the
 * differences one at a time.
 */
class BiicfEditionController extends Controller
{
    /**
     * How long reading an uploaded book may take.
     *
     * Public because the page watching the upload waits exactly
     * this long before giving up. Two limits that have to agree
     * should be one number.
     */
    public const READING_SECONDS = 300;

    /** How much memory reading an uploaded book may use. */
    private const READING_MEMORY = '512M';

    public function __construct(
        private readonly EditionExtractor $extractor,
    ) {}

    public function index()
    {
        $editions = BiicfEdition::query()
            ->withCount([
                'changes',
                'changes as pending_changes_count' => fn ($query) => $query->pending(),
            ])
            ->orderByDesc('edition_number')
            ->get();

        return view('admin.biicf.editions.index', [
            'editions' => $editions,
            'accepts' => $this->extractor->extensions(),
            'describes' => $this->extractor->describe(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'edition_number' => [
                'required',
                'integer',
                'min:1',
                'unique:biicf_editions,edition_number',
            ],
            'published_on' => ['nullable', 'date'],
            /*
             * Checked by extension rather than by sniffed MIME
             * type: what a .json upload is guessed to be differs
             * between platforms, and an edition refused for
             * being "text/plain" on one machine and accepted on
             * another is not a rule anyone can work with. What
             * the file actually holds is the extractor's
             * business, and it says so plainly.
             */
            'source' => [
                'required',
                'file',
                'max:65536',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $allowed = $this->extractor->extensions();

                    if (! in_array(strtolower($value->getClientOriginalExtension()), $allowed, true)) {
                        $fail('An edition is uploaded as '.implode(' or ', $allowed).'.');
                    }
                },
            ],
            'strategy' => ['required', 'string', 'in:'.implode(',', EditionComparer::STRATEGIES)],
        ]);

        $file = $request->file('source');

        $path = $file->store('biicf-editions', 'local');

        /*
         * Reading the book is not a normal request and cannot be
         * held to a normal request's limits. The second edition
         * is three hundred pages and ten megabytes, and takes
         * about a minute and a hundred and thirty megabytes to
         * read — against defaults of thirty seconds and a hundred
         * and twenty-eight.
         *
         * Raised here rather than in php.ini because this is the
         * only place in the application that needs it, and a
         * limit lifted everywhere would hide a runaway page
         * instead of this one honest minute. Behind a web server
         * of its own, that server's own timeout has to allow for
         * it as well: nginx reads from PHP for sixty seconds by
         * default, which is not enough room.
         *
         * For a book bigger than this one there is
         * "php artisan biicf:read-book", which has no request
         * waiting on it at all.
         */
        $this->allowTheReadingRoom();

        /*
         * Read before the edition exists, never after.
         *
         * Reading a large book can end the process outright
         * rather than raise anything catchable — PHP running out
         * of memory is not an exception and no cleanup runs. An
         * edition created first and read second therefore
         * survives its own failure, and the only sign of it is
         * that its number is taken the next time somebody tries.
         */
        try {
            $payload = $this->extractor->extract(Storage::disk('local')->path($path));
        } catch (Throwable $e) {
            Storage::disk('local')->delete($path);

            return $this->refuse($request, $e->getMessage());
        }

        $edition = BiicfEdition::create([
            'name' => $request->string('name')->toString(),
            'slug' => Str::slug($request->string('name')->toString()),
            'edition_number' => $request->integer('edition_number'),
            'published_on' => $request->date('published_on'),
            'source_file_path' => $path,
            'source_original_name' => $file->getClientOriginalName(),
        ]);

        $comparer = new EditionComparer;

        try {
            $count = $comparer->compare(
                $edition,
                $payload,
                $request->string('strategy')->toString()
            );
        } catch (Throwable $e) {
            /*
             * The edition goes with the comparison that failed.
             * Left behind it holds its number and shows an empty
             * review screen, which reads as an edition that said
             * nothing rather than one that went wrong.
             */
            $edition->changes()->delete();
            $edition->delete();
            Storage::disk('local')->delete($path);

            return $this->refuse(
                $request,
                'That edition was read, but comparing it against the framework '
                .'failed: '.$e->getMessage()
            );
        }

        $message = match (true) {
            $request->string('strategy')->toString() === EditionComparer::STRATEGY_KEEP => 'The book is on record and can be opened from here. Nothing was compared, '
                    .'and the framework is unchanged. Apply it to mark it as the edition in use.',
            $count === 0 => 'That edition says nothing this framework does not already say.',
            default => "{$count} differences are waiting for a decision.",
        };

        /*
         * What the reader could not make sense of, said out
         * loud. Those fields raise no difference at all, so
         * the framework quietly keeps what it has — and an
         * administrator who is not told would have no way of
         * knowing the book was ever asked about them.
         */
        $problems = array_merge($payload['problems'] ?? [], $comparer->skipped());

        if ($request->boolean('ajax')) {
            return response()->json([
                'redirect' => route('admin.biicf.editions.show', $edition),
                'message' => $message,
                'problems' => $problems,
            ]);
        }

        return redirect()
            ->route('admin.biicf.editions.show', $edition)
            ->with('success', $message)
            ->with('problems', $problems);
    }

    /**
     * Give the reading the time and memory it needs — and only
     * ever more than it has, never less.
     *
     * Both of these are settings for the whole process, not for
     * this request, and on the command line the process is the
     * whole test suite. Setting them flat put a five-minute
     * ceiling on a run that had none, and the suite died partway
     * through at exactly three hundred seconds.
     *
     * So an unlimited limit is left unlimited, and a generous one
     * is left alone. Only a limit too small to read a book by is
     * raised.
     */
    private function allowTheReadingRoom(): void
    {
        $seconds = (int) ini_get('max_execution_time');

        if ($seconds !== 0 && $seconds < self::READING_SECONDS) {
            set_time_limit(self::READING_SECONDS);
        }

        $memory = ini_get('memory_limit');

        if ($memory !== false && $memory !== '-1') {
            $bytes = (int) $memory * match (strtolower(substr((string) $memory, -1))) {
                'g' => 1024 ** 3,
                'm' => 1024 ** 2,
                'k' => 1024,
                default => 1,
            };

            if ($bytes < 512 * 1024 ** 2) {
                ini_set('memory_limit', self::READING_MEMORY);
            }
        }
    }

    /**
     * Turn an upload away, saying why.
     *
     * The page watching the upload is waiting on an answer it
     * can read, so it gets one. Falling back to a redirect keeps
     * the form working with no JavaScript at all.
     */
    private function refuse(Request $request, string $why)
    {
        if ($request->boolean('ajax')) {
            return response()->json(['message' => $why], 422);
        }

        return back()->withInput()->withErrors(['source' => $why]);
    }

    public function show(Request $request, BiicfEdition $edition)
    {
        /*
         * What is still waiting, and nothing else.
         *
         * A decided difference has been dealt with and does not
         * need deciding again. A hundred and twenty of them
         * folded into one-line summaries still fill the screen,
         * and what should fill it is the ones still wanting an
         * answer.
         *
         * They are not deleted: an applied edition is the record
         * of why the framework says what it does, so "show
         * everything" still reaches them.
         */
        $everything = $request->string('show')->toString() === 'all';

        $changes = $edition->changes()
            ->unless($everything, fn ($query) => $query->pending())
            ->orderBy('subject_type')
            ->orderBy('subject_label')
            ->orderBy('field')
            ->get()
            ->groupBy('subject_label');

        return view('admin.biicf.editions.show', [
            'edition' => $edition,
            'groups' => $changes,
            'everything' => $everything,
            'pending' => $edition->changes()->pending()->count(),
            'decided' => $edition->changes()->where('decision', '!=', BiicfEditionChange::DECISION_PENDING)->count(),
        ]);
    }

    /**
     * Record what an administrator decided about one difference.
     */
    public function decide(Request $request, BiicfEdition $edition, BiicfEditionChange $change)
    {
        abort_unless($change->edition_id === $edition->id, 404);

        if ($change->applied_at !== null) {
            return back()->withErrors([
                'decision' => 'That change has already been applied and cannot be decided again.',
            ]);
        }

        $request->validate([
            'decision' => [
                'required',
                'in:'.implode(',', [
                    BiicfEditionChange::DECISION_ACCEPT,
                    BiicfEditionChange::DECISION_KEEP,
                    BiicfEditionChange::DECISION_EDIT,
                ]),
            ],
            'edited_value' => [
                'required_if:decision,'.BiicfEditionChange::DECISION_EDIT,
                'nullable',
                'string',
            ],
        ]);

        $decision = $request->string('decision')->toString();

        $change->update([
            'decision' => $decision,
            'edited_value' => $decision === BiicfEditionChange::DECISION_EDIT
                ? $this->readEdit($change, $request->string('edited_value')->toString())
                : null,
            'decided_at' => now(),
            'decided_by' => $request->user()->id,
        ]);

        /*
         * Answered rather than redirected, so the page can show
         * the decision where it was made. A hundred and twenty
         * differences is a lot of scrolling to lose each time.
         */
        if ($request->boolean('ajax')) {
            return response()->json([
                'decision' => $change->decision,
                'edited' => $change->edited_value,
                'pending' => $edition->changes()->pending()->count(),
            ]);
        }

        return back()->with('success', 'Decision recorded.');
    }

    /**
     * Decide everything still pending in one go.
     *
     * For the common shape of a new edition: a long tail of
     * differences an administrator is happy to take wholesale,
     * after reading the handful they are not.
     */
    public function decideAll(Request $request, BiicfEdition $edition)
    {
        $request->validate([
            'decision' => [
                'required',
                'in:'.implode(',', [
                    BiicfEditionChange::DECISION_ACCEPT,
                    BiicfEditionChange::DECISION_KEEP,
                ]),
            ],
        ]);

        $count = $edition->changes()->pending()->update([
            'decision' => $request->string('decision')->toString(),
            'decided_at' => now(),
            'decided_by' => $request->user()->id,
        ]);

        return back()->with('success', "{$count} differences decided.");
    }

    public function apply(Request $request, BiicfEdition $edition, EditionApplier $applier)
    {
        try {
            $written = $applier->apply($edition, $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['apply' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.biicf.editions.index')
            ->with('success', "{$edition->name} is now the current edition. {$written} changes were written.");
    }

    /**
     * Open the book an edition was read from.
     *
     * The framework is a reading of a document, and every
     * difference on the review screen is a claim about what that
     * document says. Someone deciding those claims should be
     * able to go and look, rather than taking the reading's word
     * for it — so the book opens in the browser rather than
     * downloading, which is the difference between checking a
     * page and filing a copy somewhere.
     */
    /**
     * Read the book this edition was loaded from, again.
     *
     * The file is kept, so re-reading it needs no upload. That
     * matters whenever the reader itself has been corrected
     * rather than the book: a fix to how a page is numbered or a
     * section is folded has to reach an edition already staged,
     * and uploading the same book a second time would stage a
     * second edition of it.
     *
     * Decisions already taken are kept, as they are for any
     * re-reading. Their location in the book is refreshed
     * though: where a value was printed is not something an
     * administrator decided.
     */
    public function reread(Request $request, BiicfEdition $edition)
    {
        if ($edition->source_file_path === null
            || ! Storage::disk('local')->exists($edition->source_file_path)) {
            return back()->withErrors([
                'reread' => 'The file this edition was read from is no longer here, '
                    .'so it cannot be read again. Upload the book to stage it afresh.',
            ]);
        }

        $this->allowTheReadingRoom();

        try {
            $payload = $this->extractor->extract(
                Storage::disk('local')->path($edition->source_file_path)
            );
        } catch (Throwable $e) {
            return back()->withErrors(['reread' => $e->getMessage()]);
        }

        $comparer = new EditionComparer;

        $strategy = $request->string('strategy')->toString();

        if (! in_array($strategy, EditionComparer::STRATEGIES, true)) {
            $strategy = EditionComparer::STRATEGY_CHANGED;
        }

        try {
            $comparer->compare($edition, $payload, $strategy);
        } catch (Throwable $e) {
            return back()->withErrors([
                'reread' => 'That book was read, but comparing it against the framework '
                    .'failed: '.$e->getMessage(),
            ]);
        }

        return redirect()
            ->route('admin.biicf.editions.show', $edition)
            ->with('success', sprintf(
                '%s was read again. %d still undecided; the decisions you had '
                .'already taken were kept.',
                $edition->source_original_name ?? $edition->name,
                $edition->changes()->pending()->count()
            ))
            ->with('problems', array_merge($payload['problems'] ?? [], $comparer->skipped()));
    }

    public function source(BiicfEdition $edition)
    {
        abort_if($edition->source_file_path === null, 404);
        abort_unless(Storage::disk('local')->exists($edition->source_file_path), 404);

        return Storage::disk('local')->response(
            $edition->source_file_path,
            $edition->source_original_name ?? 'edition',
            ['Content-Disposition' => 'inline'],
        );
    }

    /**
     * Throw away an edition that was staged by mistake.
     *
     * Only one that has not been applied. Once an edition's
     * decisions have been written to the framework it is the
     * record of why the framework says what it does, and
     * deleting it would leave those changes with no account of
     * where they came from.
     */
    public function destroy(BiicfEdition $edition)
    {
        if ($edition->applied_at !== null) {
            return back()->withErrors([
                'delete' => 'That edition has been applied, and is the record of '
                    .'what it changed. It cannot be removed.',
            ]);
        }

        if ($edition->source_file_path) {
            Storage::disk('local')->delete($edition->source_file_path);
        }

        $edition->changes()->delete();
        $edition->delete();

        return redirect()
            ->route('admin.biicf.editions.index')
            ->with('success', "{$edition->name} and everything staged with it are gone.");
    }

    /**
     * Read what an administrator typed back into the shape the
     * field holds.
     *
     * A list is edited as lines, because that is how it reads;
     * a proficiency is a number. Typing into a textarea cannot
     * say which, so the shape of what is being replaced decides.
     */
    private function readEdit(BiicfEditionChange $change, string $input): mixed
    {
        $reference = $change->proposed_value ?? $change->current_value;

        if (is_array($reference)) {
            return collect(preg_split('/\r?\n/', $input))
                ->map(fn (string $line) => trim($line))
                ->filter()
                ->values()
                ->all();
        }

        if (is_int($reference) || (is_string($reference) && is_numeric($reference))) {
            return is_numeric(trim($input)) ? (int) trim($input) : trim($input);
        }

        return trim($input);
    }
}
