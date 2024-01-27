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
        Schema::create('catalogues', function (Blueprint $table) {
            $table->id();
            $table->integer('provider_id');
            $table->integer('service_id');
            $table->integer('booking_id');
            $table->string('catalog_name');
            $table->text('catalog_description');
            $table->json('image');
            $table->text('service_duration');
            $table->string('salon_service_charge')->nullable();
            $table->string('home_service_charge')->nullable();
            $table->string('booking_money');
            $table->json('service_hour');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogues');
    }
};
