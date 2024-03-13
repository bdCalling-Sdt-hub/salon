<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            // $table->integer('user_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            // $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->integer('provider_id');
            $table->json('service');
            $table->integer('catalouge_id')->nullable();
            $table->text('service_type');
            $table->text('service_duration');
            $table->text('price');
            $table->string('date');
            $table->string('time');
            $table->integer('status')->default(0);
            $table->double('advance_money');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
