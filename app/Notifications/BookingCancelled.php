<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingCancelled extends Notification
{
    public function __construct(public Booking $booking) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->booking->loadMissing(['customer', 'trade']);

        return [
            'type' => 'booking_cancelled',
            'booking_id' => $this->booking->id,
            'title' => __('notifications.booking_cancelled.title'),
            'message' => __('notifications.booking_cancelled.message', [
                'customer' => $this->booking->customer->name,
                'trade' => $this->booking->trade->localizedName(),
                'date' => $this->booking->preferred_date->format('d/m/Y'),
            ]),
            'url' => route('dashboard'),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->booking->loadMissing(['customer', 'trade']);

        return (new MailMessage)
            ->subject(__('notifications.booking_cancelled.title'))
            ->greeting(__('notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.booking_cancelled.message', [
                'customer' => $this->booking->customer->name,
                'trade' => $this->booking->trade->localizedName(),
                'date' => $this->booking->preferred_date->format('d/m/Y'),
            ]))
            ->action(__('notifications.booking_cancelled.action'), route('dashboard'))
            ->salutation(__('notifications.mail.salutation'));
    }
}
