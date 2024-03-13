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
        Schema::create('post_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->unsignedBigInteger('salon_id');
            $table->foreign('salon_id')->references('id')->on('providers');
            // $table->foreignId('category_id')->constrained()->onDelete('cascade');
            // $table->foreignId('salon_id')->constrained()->onDelete('cascade');
            $table->json('service');
            $table->string('time');
            $table->string('date');
            $table->string('service_type');
            $table->integer('price');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_bookings');
    }
};
