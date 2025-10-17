<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->ulid('organizer_id')->index();
            $table->string('title');
            $table->text('description');
            $table->dateTime('date');
            $table->decimal('ticket_price', 10, 2);
            $table->integer('capacity');
            $table->timestamps();

            $table->foreign('organizer_id')->references('id')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
