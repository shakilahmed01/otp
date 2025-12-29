<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SendOtpNotification extends Notification
{
    use Queueable;

    public $token;
    public $channel;

    public function __construct(string $token, string $channel = 'email')
    {
        $this->token = $token;
        $this->channel = $channel;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Your verification code')
                    ->line('Your verification code is:')
                    ->line("**{$this->token}**")
                    ->line('This code will expire in 10 minutes.')
                    ->line('If you did not request this, please ignore this message.');
    }
}
