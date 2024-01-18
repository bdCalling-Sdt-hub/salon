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
        Schema::create('sers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sal_id');
            $table->foreign('sal_id')->references('id')->on('sals');
            $table->string('service_name');
            $table->longText('service_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sers');
    }
};
