<?php

namespace App\Models;

use App\Rules\IsEmoji;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\HtmlString;

class InnovationLabNote extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'created_by',
        'is_published',
        'accepts_interest',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'accepts_interest' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * The emoji the page offers, in the order it draws them.
     *
     * An offer rather than a rule: what a reaction may be is
     * decided by {@see IsEmoji}, so a student whose
     * own keyboard has something better is not held to this
     * list. These are the ones worth a single press, grouped so
     * the picker reads as rows rather than a wall — approval,
     * delight, feeling, thinking, hands up, and the few objects
     * that come up when the subject is something being built.
     *
     * @var array<string, array<int, string>>
     */
    public const EMOJI_GROUPS = [
        'Yes' => ['👍', '🙌', '👏', '💯', '✅', '🤝'],
        'Love it' => ['🎉', '🔥', '❤️', '😍', '🤩', '✨'],
        'Feeling' => ['😀', '😂', '🥳', '😮', '😢', '😬'],
        'Thinking' => ['💡', '🤔', '🧠', '👀', '📌', '❓'],
        'Count me in' => ['🙋', '✋', '🚀', '💪', '🏃', '🫡'],
        'Building' => ['💻', '⌨️', '🛠️', '📱', '🧪', '📚'],
    ];

    /**
     * The same set, flat, for anywhere that wants a plain list.
     *
     * @var array<int, string>
     */
    public const EMOJI = [
        '👍', '🙌', '👏', '💯', '✅', '🤝',
        '🎉', '🔥', '❤️', '😍', '🤩', '✨',
        '😀', '😂', '🥳', '😮', '😢', '😬',
        '💡', '🤔', '🧠', '👀', '📌', '❓',
        '🙋', '✋', '🚀', '💪', '🏃', '🫡',
        '💻', '⌨️', '🛠️', '📱', '🧪', '📚',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(InnovationLabReaction::class, 'note_id');
    }

    public function interests(): HasMany
    {
        return $this->hasMany(InnovationLabInterest::class, 'note_id');
    }

    /**
     * How many of each emoji this post has, in the palette's
     * order and leaving out the ones nobody has used.
     *
     * @return array<string, int>
     */
    public function reactionTally(): array
    {
        $counts = $this->reactions
            ->groupBy('emoji')
            ->map(fn ($rows) => $rows->count());

        $tally = [];

        /* The palette's order first, so the common ones line up. */
        foreach (self::EMOJI as $emoji) {
            if (($counts[$emoji] ?? 0) > 0) {
                $tally[$emoji] = $counts[$emoji];
            }
        }

        /*
         * Then anything a student brought from their own
         * keyboard, most used first. Left out, a reaction the
         * palette has never heard of would be saved and never
         * drawn.
         */
        $counts
            ->except(array_keys($tally))
            ->sortDesc()
            ->each(function (int $count, string $emoji) use (&$tally) {
                $tally[$emoji] = $count;
            });

        return $tally;
    }

    /**
     * Which emoji this student has already put on the post.
     *
     * @return array<int, string>
     */
    public function emojiFrom(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        return $this->reactions
            ->where('user_id', $user->id)
            ->pluck('emoji')
            ->all();
    }

    public function interestCount(): int
    {
        return $this->interests->count();
    }

    public function isInterested(?User $user): bool
    {
        return $user !== null
            && $this->interests->contains('user_id', $user->id);
    }

    /**
     * The body as safe HTML with web addresses turned into links.
     *
     * The text is escaped before linking, so anything an admin
     * types stays plain text and cannot inject markup.
     */
    public function bodyHtml(): HtmlString
    {
        $linked = preg_replace_callback(
            '~https?://(?:(?!&quot;|&#039;|&lt;|&gt;)[^\s<])+~i',
            function (array $match): string {
                $url = $match[0];
                $trailing = '';

                if (preg_match('~[.,;:!?)]+$~', $url, $end)) {
                    $trailing = $end[0];
                    $url = substr($url, 0, -strlen($trailing));
                }

                return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer nofollow">'
                    .$url.'</a>'.$trailing;
            },
            e($this->body)
        );

        return new HtmlString($linked);
    }

    public function imageUrl(): ?string
    {
        /*
         * Built from the request host rather than APP_URL, so the
         * picture still loads when APP_URL does not match the
         * address the site is actually served from.
         */
        return $this->image_path
            ? asset('storage/'.$this->image_path)
            : null;
    }
}
