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
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('category_id');
            $table->integer('provider_id');
            $table->text('business_name');
            $table->text('address');
            $table->text('description');
            $table->json('available_service_our');
            $table->string('cover_photo');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->json('gallary_photo');
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
