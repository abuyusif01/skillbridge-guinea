<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('trade_id')->constrained()->restrictOnDelete();
            $table->text('bio');
            $table->unsignedTinyInteger('experience_years')->default(0);
            $table->string('service_area');
            $table->unsignedInteger('rate_min')->nullable();
            $table->boolean('is_available')->default(true)->index();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['trade_id', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_profiles');
    }
};
