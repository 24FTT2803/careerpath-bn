<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * The address confirmation sent when someone signs up.
 *
 * The parent builds the signed URL and decides when it expires.
 * Only the body is replaced, so nothing about what makes the
 * link trustworthy changes — the same arrangement as the
 * password reset mail beside it.
 */
class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $url = $this->verificationUrl($notifiable);

        $expiryMinutes = (int) config(
            'auth.verification.expire',
            60
        );

        return (new MailMessage)
            ->subject('Confirm your CareerPath BN email address')
            ->view('emails.verify-email', [
                'url' => $url,
                'user' => $notifiable,
                'expiryMinutes' => $expiryMinutes,
            ]);
    }
}
