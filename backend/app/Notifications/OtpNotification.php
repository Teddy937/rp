<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OtpNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $otp,
        private readonly string $userName
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your KK Wholesalers Login OTP')
            ->view('emails.auth.otp', [
                'otp'      => $this->otp,
                'userName' => $this->userName,
                'expiry'   => 10,
            ]);
    }
}
