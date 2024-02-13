<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('booking_id');
            $table->foreign('booking_id')->references('id')->on('bookings');
            $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')->references('id')->on('providers');
            $table->text('payment_type');
            $table->string('amount');
            $table->string('email');
            $table->string('name');
            $table->text('currency');
            $table->text('tx_ref');
            $table->string('status');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payments');
    }
};
