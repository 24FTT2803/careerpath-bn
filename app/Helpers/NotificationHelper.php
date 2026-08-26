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
            'Your profile is now complete. View your personalised career recommendations on the dashboard!',
            route('student.dashboard', absolute: false)
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
            route('student.dashboard', absolute: false)
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
            route('student.dashboard', absolute: false)
        );
    }

    /**
     * Log student registration activity (for admin dashboard)
     */
    public static function logStudentRegistration($userId, $name)
    {
        return self::create(
            $userId,
            'user',
            'New Student Registered',
            "{$name} has joined the platform",
            route('admin.students.show', $userId, absolute: false)
        );
    }

    /**
     * Log profile update activity (for admin dashboard)
     */
    public static function logProfileUpdate($userId, $name)
    {
        return self::create(
            $userId,
            'profile',
            'Profile Updated',
            "{$name} updated their profile information",
            route('student.profile', absolute: false)
        );
    }

    /**
     * Log career recommendation generation activity (for admin dashboard)
     */
    public static function logCareerRecommendation($userId, $name, $count)
    {
        return self::create(
            $userId,
            'career',
            'Career Recommendations Generated',
            "{$count} new career recommendations generated for {$name}",
            route('student.dashboard', absolute: false)
        );
    }

    /**
     * Log milestone activity (for admin dashboard)
     */
    public static function logMilestoneActivity($userId, $milestoneName, $action = 'completed')
    {
        $actionMap = [
            'completed' => 'Completed Milestone',
            'added' => 'Added Milestone',
            'deleted' => 'Deleted Milestone',
        ];

        $actionLabel = $actionMap[$action] ?? ucfirst($action) . ' Milestone';

        return self::create(
            $userId,
            'milestone',
            $actionLabel,
            "{$actionLabel}: {$milestoneName}",
            route('student.milestones', absolute: false)
        );
    }

    /**
     * Log generic system activity
     */
    public static function logSystemActivity($userId, $title, $message, $link = null)
    {
        return self::create(
            $userId,
            'system',
            $title,
            $message,
            $link
        );
    }
}
