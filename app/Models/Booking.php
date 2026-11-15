<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'worker_id',
        'trade_id',
        'description',
        'preferred_date',
        'time_slot',
        'address',
        'contact_phone',
        'status',
        'responded_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'preferred_date' => 'date',
            'responded_at' => 'datetime',
            'completed_at' => 'datetime',
            'status' => BookingStatus::class,
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function isPending(): bool
    {
        return $this->status === BookingStatus::Pending;
    }

    public function isCompleted(): bool
    {
        return $this->status === BookingStatus::Completed;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', BookingStatus::Pending);
    }

    public function scopeForWorker(Builder $query, User|int $worker): Builder
    {
        return $query->where('worker_id', $worker instanceof User ? $worker->id : $worker);
    }
}
