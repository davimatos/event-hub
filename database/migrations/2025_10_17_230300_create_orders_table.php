<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('event_id')->index();
            $table->ulid('participant_id')->index();
            $table->integer('quantity');
            $table->decimal('ticket_price', 10, 2);
            $table->decimal('discount', 10, 2);
            $table->decimal('total_amount', 10, 2);
            $table->string('status');
            $table->timestamps();

            $table->foreign('participant_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
