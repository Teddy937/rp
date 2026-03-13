<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $token,
        private readonly string $userName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = config('frontend.url') . '/reset-password?token=' . $this->token . '&email=' . urlencode($notifiable->email);

        return (new MailMessage)
            ->subject('Reset Your KK Wholesalers Password')
            ->view('emails.auth.password-reset', [
                'resetUrl' => $resetUrl,
                'userName' => $this->userName,
                'expiry'   => 60,
            ]);
    }
}
