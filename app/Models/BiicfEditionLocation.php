<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Where in an edition's book one value is printed.
 *
 * Recorded for every value the book states, not only the ones
 * that disagreed with the framework, so that checking is
 * possible before anybody knows whether there is anything wrong.
 */
class BiicfEditionLocation extends Model
{
    protected $table = 'biicf_edition_locations';

    protected $fillable = [
        'edition_id',
        'subject_type',
        'subject_key',
        'field',
        'page',
        'spans',
    ];

    protected function casts(): array
    {
        return [
            'page' => 'integer',
            'spans' => 'array',
        ];
    }

    public function edition(): BelongsTo
    {
        return $this->belongsTo(BiicfEdition::class, 'edition_id');
    }

    /**
     * The key a staged difference would be found under.
     */
    public function key(): string
    {
        return $this->subject_type.'|'.$this->subject_key.'|'.$this->field;
    }
}
