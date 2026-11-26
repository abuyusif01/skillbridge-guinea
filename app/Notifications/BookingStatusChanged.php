<?php

namespace App\Notifications;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingStatusChanged extends Notification
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
        $this->booking->loadMissing(['worker', 'trade']);

        $key = $this->statusKey();

        return [
            'type' => 'booking_status',
            'booking_id' => $this->booking->id,
            'status' => $this->booking->status->value,
            'title' => __("notifications.booking.{$key}.title"),
            'message' => __("notifications.booking.{$key}.message", [
                'worker' => $this->booking->worker->name,
                'trade' => $this->booking->trade->localizedName(),
            ]),
            'url' => $this->destinationUrl(),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->booking->loadMissing(['worker', 'trade']);

        $key = $this->statusKey();

        return (new MailMessage)
            ->subject(__("notifications.booking.{$key}.title"))
            ->greeting(__('notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(__("notifications.booking.{$key}.message", [
                'worker' => $this->booking->worker->name,
                'trade' => $this->booking->trade->localizedName(),
            ]))
            ->action(__('notifications.booking.action'), $this->destinationUrl())
            ->salutation(__('notifications.mail.salutation'));
    }

    private function statusKey(): string
    {
        return match ($this->booking->status) {
            BookingStatus::Accepted => 'accepted',
            BookingStatus::Declined => 'declined',
            BookingStatus::Completed => 'completed',
            default => 'updated',
        };
    }

    private function destinationUrl(): string
    {
        if ($this->booking->status === BookingStatus::Completed && $this->booking->canBeReviewed()) {
            return route('reviews.create', $this->booking);
        }

        return route('dashboard');
    }
}
