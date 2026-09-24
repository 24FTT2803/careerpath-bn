<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\HtmlString;

class InnovationLabNote extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'created_by',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
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
