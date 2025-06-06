<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

class CustomVerifyEmail extends Notification  
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the verification URL.
     */
    protected function verificationUrl(object $notifiable): string
    {
        return URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);
        $appName = config('app.name');

        return (new MailMessage)
            ->subject("Verify Your Email Address for ChainScholar")
            ->greeting("Hello {$notifiable->name}!")
            ->line("Thank you for registering with ChainScholar. Please click the button below to verify your email address.")
            ->action('Verify Email Address', $verificationUrl)
            ->line('This verification link will expire in '.Config::get('auth.verification.expire', 60).' minutes.')
            ->line("If you didn't create an account with us, please ignore this email.")
            ->salutation("\nRegards,\nChainScholar Team")
            ->markdown('emails.verify-email', [
                'verificationUrl' => $verificationUrl,
                'notifiable' => $notifiable
            ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}