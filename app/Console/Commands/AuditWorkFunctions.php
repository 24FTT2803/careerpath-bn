<?php

namespace App\Console\Commands;

use App\Models\BiicfCriticalWorkFunction;
use App\Models\BiicfJobRole;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use RuntimeException;

/**
 * Say what is actually in the duties table.
 *
 * The duplicates were written by a reading that appended instead
 * of replacing, and they were counted by hand each time the
 * question came up — through a shell that mangles the quoting of
 * anything long enough to be useful.
 *
 * So: one command, no arguments, and the answer in a form that
 * can be read or pasted. It changes nothing.
 */
class AuditWorkFunctions extends Command
{
    protected $signature = 'biicf:audit-work-functions
                            {--all : list every role, not only the ones with something wrong}
                            {--when : say when the rows were written, newest first}
                            {--role= : dump one role\'s rows as they are stored}';

    protected $description = 'Count a role\'s duties and say where they disagree with the data file';

    public function handle(): int
    {
        if ($this->option('role')) {
            return $this->dumpRole((string) $this->option('role'));
        }

        if ($this->option('when')) {
            return $this->whenWritten();
        }

        $expected = $this->fromDataFile();

        $rows = [];
        $problems = 0;

        foreach (BiicfJobRole::orderBy('title')->get() as $role) {
            $duties = BiicfCriticalWorkFunction::where('job_role_id', $role->id)->get();

            $repeats = $duties
                ->groupBy('text')
                ->filter(fn ($group) => $group->count() > 1);

            $orphans = $duties
                ->filter(fn ($duty) => $duty->parent_id !== null
                    && ! $duties->contains('id', $duty->parent_id));

            $unfiled = $duties->filter(
                fn ($duty) => $duty->kind === BiicfCriticalWorkFunction::KIND_SPECIFIC
                    && $duty->job_family_id === null
            );

            $held = $duties->count();
            $says = $expected[$role->title] ?? null;

            /*
             * A repeat is only a fault when it is a copy nobody
             * asked for. Where the count matches the data file,
             * the book itself says the duty twice — Digital
             * Marketing Executive repeats three of its own under
             * different headings — and calling that a
             * disagreement sends the reader looking for a problem
             * that is not there.
             */
            $wrong = $orphans->isNotEmpty()
                || ($says !== null && $says !== $held)
                || ($says === null && $repeats->isNotEmpty());

            if ($wrong) {
                $problems++;
            }

            if (! $wrong && ! $this->option('all')) {
                continue;
            }

            $rows[] = [
                $role->title,
                $held,
                $says ?? '—',
                $duties->whereNull('parent_id')->count(),
                $this->deepest($duties),
                $repeats->count() ?: '',
                $orphans->count() ?: '',
                $unfiled->count() ?: '',
            ];
        }

        $this->newLine();
        $this->table(
            ['Role', 'Held', 'Data file', 'Top level', 'Depth', 'Repeated', 'Orphaned', 'No family'],
            $rows
        );

        $total = BiicfCriticalWorkFunction::count();

        $this->line("{$total} duties in all; the data file describes ".array_sum($expected).'.');

        if ($problems === 0) {
            $this->info('Every role matches the data file, with nothing repeated and nothing orphaned.');

            return self::SUCCESS;
        }

        $this->warn($problems.' role(s) disagree with the data file. Listed above.');
        $this->line('A repeat is not always wrong: the book prints three of Digital Marketing');
        $this->line('Executive\'s duties twice, under different headings.');

        return self::SUCCESS;
    }

    /**
     * When the rows were written, grouped by the minute.
     *
     * The decisive question about duplicates is not how many
     * there are but when they arrived: one batch at one minute is
     * a command or a migration, a scatter of minutes is something
     * done through a screen, and rows older than the rest are the
     * ones that were supposed to be replaced.
     */
    private function whenWritten(): int
    {
        $rows = BiicfCriticalWorkFunction::query()
            ->selectRaw('created_at, count(*) as written')
            ->groupBy('created_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn ($row) => substr((string) $row->created_at, 0, 16))
            ->map(fn ($group) => $group->sum('written'));

        $this->newLine();
        $this->table(
            ['Written at', 'Rows'],
            $rows->map(fn ($written, $minute) => [$minute, $written])->values()->all()
        );

        $this->line($rows->count().' distinct minute(s); '
            .BiicfCriticalWorkFunction::count().' rows in all.');

        if ($rows->count() > 1) {
            $this->warn('More than one writing means a write that did not clear what was there.');
        }

        return self::SUCCESS;
    }

    /**
     * One role's rows, exactly as they are stored.
     *
     * Parent, order and kind alongside the text, because what
     * went wrong shows in the structure rather than in the
     * wording: the same duty under three different parents is a
     * different fault from the same duty three times at the top.
     */
    private function dumpRole(string $title): int
    {
        $role = BiicfJobRole::where('title', $title)->first();

        if ($role === null) {
            $this->error("No role is called \"{$title}\".");

            return self::FAILURE;
        }

        $rows = BiicfCriticalWorkFunction::where('job_role_id', $role->id)
            ->orderBy('id')
            ->get()
            ->map(fn ($duty) => [
                $duty->id,
                $duty->parent_id ?? '—',
                $duty->sort_order,
                $duty->kind,
                $duty->job_family_id ?? '—',
                substr((string) $duty->created_at, 0, 19),
                mb_substr($duty->text, 0, 46),
            ]);

        $this->newLine();
        $this->table(
            ['id', 'parent', 'order', 'kind', 'family', 'written at', 'text'],
            $rows->all()
        );

        $this->line($rows->count().' rows for '.$role->title.'.');

        return self::SUCCESS;
    }

    /**
     * How many duties the data file describes for each role.
     *
     * The file is the record of what was read from the book, so
     * it is what the table should hold — any difference is either
     * an edit made since, or a reading that went wrong.
     *
     * @return array<string, int>
     */
    private function fromDataFile(): array
    {
        $path = database_path('data/biicf-job-roles.json');

        if (! is_file($path)) {
            throw new RuntimeException("The job role data file is missing: {$path}");
        }

        $roles = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        $counts = [];

        foreach ($roles as $role) {
            $counts[$role['title']] = $this->countNodes($role['critical_work_functions'] ?? []);
        }

        return $counts;
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     */
    private function countNodes(array $nodes): int
    {
        $total = 0;

        foreach ($nodes as $node) {
            $total += 1 + $this->countNodes($node['children'] ?? []);
        }

        return $total;
    }

    /**
     * How deep this role's duties nest.
     *
     * @param  Collection<int, BiicfCriticalWorkFunction>  $duties
     */
    private function deepest($duties): int
    {
        $depth = 0;

        foreach ($duties as $duty) {
            $here = 1;
            $walk = $duty;

            /*
             * Up through the parents rather than down through the
             * children, so a row whose parent is missing does not
             * hide the nesting it was part of.
             */
            while ($walk->parent_id !== null) {
                $walk = $duties->firstWhere('id', $walk->parent_id);

                if ($walk === null) {
                    break;
                }

                $here++;
            }

            $depth = max($depth, $here);
        }

        return $depth;
    }
}
