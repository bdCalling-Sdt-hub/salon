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
        Schema::create('service_ratings', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('user_id');
            // $table->foreign('user_id')->references('id')->on('users');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('service_id');
            $table->integer('provider_id');
            $table->integer('catalogue_id')->nullable();
            $table->text('review');
            $table->double('rating', 5, 1);
            $table->integer('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_ratings');
    }
};
