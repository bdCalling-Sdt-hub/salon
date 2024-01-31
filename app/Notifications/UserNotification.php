<?php

namespace App\Notifications;
use App\Events\SendNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserNotification extends Notification
{
    use Queueable;

    protected $user;
    protected $message;
    protected $description;

    public function __construct($message,$description,$user)
    {
        $this->message = $message;
        $this->description = $description;
        $this->user = $user;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'message' => $this->message,
            'description' => $this->description,
            'user' => $this->user,
        ];
    }
}
