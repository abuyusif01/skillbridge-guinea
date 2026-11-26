<?php

namespace App\Notifications;

use App\Models\Review;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ReviewReceived extends Notification
{
    public function __construct(public Review $review) {}

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
        $this->review->loadMissing(['customer', 'worker']);

        return [
            'type' => 'review_received',
            'review_id' => $this->review->id,
            'worker_id' => $this->review->worker_id,
            'title' => __('notifications.review_received.title'),
            'message' => __('notifications.review_received.message', [
                'customer' => $this->review->customer->name,
                'rating' => $this->review->rating,
                'comment' => Str::limit(
                    $this->review->comment ?: __('notifications.review_received.no_comment'),
                    80
                ),
            ]),
            'url' => route('workers.show', $this->review->worker),
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->review->loadMissing(['customer', 'worker']);

        return (new MailMessage)
            ->subject(__('notifications.review_received.title'))
            ->greeting(__('notifications.mail.greeting', ['name' => $notifiable->name]))
            ->line(__('notifications.review_received.message', [
                'customer' => $this->review->customer->name,
                'rating' => $this->review->rating,
                'comment' => Str::limit(
                    $this->review->comment ?: __('notifications.review_received.no_comment'),
                    120
                ),
            ]))
            ->action(__('notifications.review_received.action'), route('workers.show', $this->review->worker))
            ->salutation(__('notifications.mail.salutation'));
    }
}
