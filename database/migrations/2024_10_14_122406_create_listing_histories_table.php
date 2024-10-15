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
        Schema::create('listing_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listingId');
            $table->json('before');
            $table->json('after');
            $table->json('changes');
            $table->timestamps();

            $table->foreign('listingId')->references('id')->on('listings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_histories');
    }
};
