<?php

namespace App\Notifications\Channels;

use Illuminate\Notifications\Notification;
use App\Services\WaGateway;

class WhatsappChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (method_exists($notification, 'toWhatsapp')) {
            $notification->toWhatsapp($notifiable);
        }
    }
}
