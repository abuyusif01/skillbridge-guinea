<?php

use App\Enums\BookingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('worker_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('trade_id')->constrained()->restrictOnDelete();
            $table->text('description');
            $table->date('preferred_date');
            $table->string('time_slot');
            $table->string('address');
            $table->string('contact_phone');
            $table->string('status')->default(BookingStatus::Pending->value);
            $table->timestamp('responded_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['worker_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['preferred_date', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
