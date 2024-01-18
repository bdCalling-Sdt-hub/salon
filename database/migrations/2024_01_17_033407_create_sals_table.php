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
        Schema::create('sals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cat_id');
            $table->foreign('cat_id')->references('id')->on('users');
            $table->unsignedBigInteger('provider_id');
            $table->foreign('provider_id')->references('id')->on('users');
            $table->string('salon_name');
            $table->longText('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sals');
    }
};
