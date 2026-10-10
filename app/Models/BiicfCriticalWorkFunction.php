<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One duty a job role performs.
 *
 * The book divides these in two. Generic functions belong to the
 * role however it is practised; specific functions belong to one
 * of the role's job families, and under some families they nest
 * a second level — a heading duty with its own sub-duties.
 */
class BiicfCriticalWorkFunction extends Model
{
    public const KIND_GENERIC = 'generic';

    public const KIND_SPECIFIC = 'specific';

    protected $table = 'biicf_critical_work_functions';

    protected $fillable = [
        'job_role_id',
        'kind',
        'job_family_id',
        'parent_id',
        'text',
        'sort_order',
    ];

    public function scopeGeneric(Builder $query): Builder
    {
        return $query->where('kind', self::KIND_GENERIC);
    }

    public function scopeSpecific(Builder $query): Builder
    {
        return $query->where('kind', self::KIND_SPECIFIC);
    }

    /**
     * Only the functions that sit at the top of their list.
     *
     * Sub-duties are reached through their parent, so a listing
     * that wants the structure rather than every row starts
     * here.
     */
    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function jobRole(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobRole::class,
            'job_role_id'
        );
    }

    /**
     * The job family this function belongs to, for specific
     * functions. Null for generic ones.
     */
    public function jobFamily(): BelongsTo
    {
        return $this->belongsTo(
            BiicfJobFamily::class,
            'job_family_id'
        );
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            self::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            self::class,
            'parent_id'
        )->orderBy('sort_order');
    }

    public function isGeneric(): bool
    {
        return $this->kind === self::KIND_GENERIC;
    }
}
