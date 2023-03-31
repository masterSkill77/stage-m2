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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->float('current_bid');
            $table->float('start_price');

            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('nft_id');

            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreign('nft_id')->references('id')->on('nfts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
