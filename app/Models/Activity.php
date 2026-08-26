<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'user_type',
        'activity_type',
        'description',
        'icon',
        'link',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Log an activity
     */
    public static function log($userId, $type, $description, $icon = 'bell', $link = null, $metadata = null)
    {
        return self::create([
            'user_id' => $userId,
            'user_type' => 'student',
            'activity_type' => $type,
            'description' => $description,
            'icon' => $icon,
            'link' => $link,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Get recent activities for admin dashboard
     */
    public static function getRecent($limit = 10)
    {
        return self::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($activity) {
                return [
                    'type' => $activity->activity_type,
                    'icon' => $activity->icon ?? 'bell',
                    'message' => $activity->description,
                    'time' => $activity->created_at->diffForHumans(),
                    'user_name' => $activity->user?->name ?? 'System',
                    'link' => $activity->link,
                ];
            });
    }
}