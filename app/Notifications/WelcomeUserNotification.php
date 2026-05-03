<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\User;

class WelcomeUserNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected User $user;
    protected ?string $journalName;

    /**
     * Create a new notification instance.
     * 
     * @param User $user
     * @param string|null $journalName Optional journal name context
     */
    public function __construct(User $user, ?string $journalName = null)
    {
        $this->user = $user;
        $this->journalName = $journalName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $greeting = 'Dear ' . $notifiable->name . ',';
        
        $message = (new MailMessage)
            ->subject('Welcome to IAMJOS' . ($this->journalName ? ' - ' . $this->journalName : ''))
            ->greeting($greeting);

        if ($this->journalName) {
            $message->line("You have successfully registered as a user with {$this->journalName}. We are thrilled to have you on board!");
        } else {
            $message->line('You have successfully registered an account with IAMJOS. We are thrilled to have you on board!');
        }

        $message->line('**Your Account Details:**')
            ->line('- **Username:** ' . $notifiable->username)
            ->line('- **Email:** ' . $notifiable->email)
            ->line('*(For security reasons, your password has been securely encrypted and is not displayed here. If you forget your password, you can use the "Forgot Password" feature on the login page.)*')
            ->action('Log in to your account', route('login'))
            ->line('Once logged in, you can update your profile, submit articles, and track your submission progress.')
            ->salutation('Best regards, Editorial Team');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
