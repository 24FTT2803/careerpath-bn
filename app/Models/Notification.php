<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'link',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Get recent activities for admin/lecturer dashboard
     * Uses the existing notifications table
     */
    public static function getDashboardActivities($limit = 10)
    {
        return self::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function($notification) {
                $iconMap = [
                    'system' => 'bell',
                    'recommendation' => 'star',
                    'milestone' => 'flag-checkered',
                    'reminder' => 'clock',
                    'user' => 'user-plus',
                    'profile' => 'user-edit',
                    'career' => 'briefcase',
                ];
                
                return [
                    'type' => $notification->type,
                    'icon' => $iconMap[$notification->type] ?? 'bell',
                    'message' => $notification->title . ': ' . $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'user_name' => $notification->user?->name ?? 'System',
                    'link' => $notification->link,
                ];
            });
    }
}