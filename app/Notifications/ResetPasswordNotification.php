<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    /**
     * Build the reset password email.
     *
     * The parent class handles token creation and the reset
     * URL. We replace only the message body, so nothing about
     * the security of the reset flow changes.
     */
    public function toMail($notifiable): MailMessage
    {
        $url = $this->resetUrl($notifiable);

        $expiryMinutes = (int) config(
            'auth.passwords.'
            .config('auth.defaults.passwords')
            .'.expire',
            60
        );

        return (new MailMessage)
            ->subject('Reset your CareerPath BN password')
            ->view('emails.reset-password', [
                'url' => $url,
                'user' => $notifiable,
                'expiryMinutes' => $expiryMinutes,
            ]);
    }
}