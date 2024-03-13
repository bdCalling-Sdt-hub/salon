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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('category_id')->constrained()->onDelete('cascade');
            // $table->foreignId('provider_id')->constrained()->onDelete('cascade');
            $table->integer('category_id');
            $table->integer('provider_id');
            $table->text('service_name');
            $table->text('service_description');
            $table->json('gallary_photo');
            $table->text('service_duration');
            $table->text('salon_service_charge');
            $table->text('home_service_charge');
            $table->string('set_booking_mony');
            $table->json('available_service_our');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
