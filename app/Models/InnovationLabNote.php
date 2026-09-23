<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
