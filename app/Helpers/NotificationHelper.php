<?php

namespace App\Helpers;

use App\Models\Notification;

class NotificationHelper
{
    /**
     * Create a notification for a user.
     */
    public static function create($userId, $type, $title, $message, $link = null)
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'link' => $link,
        ]);
    }

    /**
     * Notify user when profile is complete.
     */
    public static function notifyProfileComplete($userId)
    {
        return self::create(
            $userId,
            'system',
            'Profile Complete! 🎉',
            'Your profile is now complete. Run a career assessment to get personalized recommendations!',
            route('student.assessment', absolute: false)
        );
    }

    /**
     * Notify user about new career recommendation.
     */
    public static function notifyNewRecommendation($userId, $careerName)
    {
        return self::create(
            $userId,
            'recommendation',
            'New Career Recommendation',
            "We found a great match for you: {$careerName}. Check it out!",
            route('student.recommendations', absolute: false)
        );
    }

    /**
     * Notify user when milestone is completed.
     */
    public static function notifyMilestoneCompleted($userId, $milestoneName)
    {
        return self::create(
            $userId,
            'milestone',
            'Milestone Completed! 🎯',
            "You completed: {$milestoneName}. Keep up the great work!",
            route('student.milestones', absolute: false)
        );
    }

    /**
     * Notify user about a reminder.
     */
    public static function notifyReminder($userId, $title, $message)
    {
        return self::create(
            $userId,
            'reminder',
            $title,
            $message
        );
    }

    /**
     * Notify user about skill gap update.
     */
    public static function notifySkillGapUpdate($userId, $careerName, $gapCount)
    {
        return self::create(
            $userId,
            'system',
            'Skill Gap Update',
            "For {$careerName}, you need to develop {$gapCount} more skill(s). Check your development plan!",
            route('student.recommendations', absolute: false)
        );
    }
}