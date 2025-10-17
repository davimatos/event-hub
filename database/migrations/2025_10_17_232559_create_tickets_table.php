<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('order_id')->nullable()->default(null)->index();
            $table->ulid('event_id')->index();
            $table->ulid('participant_id')->index();
            $table->dateTime('used_at')->nullable()->default(null);
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('participant_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
