<?php

namespace App\Notifications;

use App\Models\PendingRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

/**
 * Sent to the address someone signed up with.
 *
 * It carries two links. Authenticate creates the account; "This
 * isn't me" throws the sign-up away, for the owner of an address
 * someone else typed in. Both are signed and expire, and both
 * carry the sign-up's token, so signing up again with the same
 * address leaves the earlier email's links dead.
 */
class AuthenticateRegistrationNotification extends Notification
{
    use Queueable;

    public function __construct(
        public PendingRegistration $pending,
        public string $token,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Authenticate your CareerPath BN account')
            ->view('emails.authenticate-registration', [
                'name' => $this->pending->name,
                'email' => $this->pending->email,
                'authenticateUrl' => $this->linkTo('register.authenticate'),
                'declineUrl' => $this->linkTo('register.decline'),
                'expiryMinutes' => (int) config('auth.verification.expire', 60),
            ]);
    }

    public function linkTo(string $routeName): string
    {
        return URL::temporarySignedRoute(
            $routeName,
            $this->pending->expires_at,
            [
                'pending' => $this->pending->id,
                'token' => $this->token,
            ]
        );
    }
}
