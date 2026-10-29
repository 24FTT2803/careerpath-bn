<?php

namespace App\Models;

use App\Support\SectionGuideDefaults;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * The help shown when a student clicks the '?' beside a section.
 */
class SectionGuide extends Model
{
    protected $fillable = [
        'title',
        'body',
        'video_url',
    ];

    /**
     * Where the loaded guides are kept for the rest of the
     * request. The container rather than a static, so nothing
     * outlives the request (or a test).
     */
    private const LOADED = 'section-guides.loaded';

    protected static function booted(): void
    {
        static::saved(fn () => self::forgetLoaded());
    }

    /**
     * A guide by its key, with every guide loaded once per
     * request however many icons a page shows.
     */
    public static function forKey(string $key): ?self
    {
        $app = app();

        if (! $app->bound(self::LOADED)) {
            $app->instance(self::LOADED, self::query()->get()->keyBy('key'));
        }

        return $app->make(self::LOADED)->get($key);
    }

    public static function forgetLoaded(): void
    {
        app()->forgetInstance(self::LOADED);
    }

    public function pageLabel(): string
    {
        return $this->page === SectionGuideDefaults::PAGE_ANALYSIS
            ? 'Career Analysis'
            : 'Edit Profile';
    }

    /**
     * The body as paragraphs: a blank line starts a new one.
     *
     * @return array<int, string>
     */
    public function paragraphs(): array
    {
        return collect(preg_split('/\R\s*\R/', trim((string) $this->body)))
            ->map(fn (string $paragraph) => trim($paragraph))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * A YouTube link as its privacy-enhanced embed address, or
     * null when the video is not on YouTube.
     */
    public function youtubeEmbedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $patterns = [
            '~^https?://(?:www\.|m\.)?youtube\.com/watch\?(?:.*&)?v=([A-Za-z0-9_-]{11})~',
            '~^https?://youtu\.be/([A-Za-z0-9_-]{11})~',
            '~^https?://(?:www\.)?youtube\.com/(?:embed|shorts)/([A-Za-z0-9_-]{11})~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->video_url, $matches)) {
                return 'https://www.youtube-nocookie.com/embed/'.$matches[1];
            }
        }

        return null;
    }

    /**
     * A direct link to a video file the browser can play itself.
     */
    public function videoFileUrl(): ?string
    {
        if (! $this->video_url || $this->youtubeEmbedUrl() !== null) {
            return null;
        }

        $path = Str::lower((string) parse_url($this->video_url, PHP_URL_PATH));

        return Str::endsWith($path, ['.mp4', '.webm', '.ogg'])
            ? $this->video_url
            : null;
    }
}
