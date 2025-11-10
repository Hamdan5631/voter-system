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
        Schema::create('voter_category_voter', function (Blueprint $table) {
            $table->unsignedBigInteger('voter_id');
            $table->unsignedBigInteger('voter_category_id');

            $table->foreign('voter_id')->references('id')->on('voters')->onDelete('cascade');
            $table->foreign('voter_category_id')->references('id')->on('voter_categories')->onDelete('cascade');
            
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->timestamps();
            
            $table->primary(['voter_id', 'voter_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voter_category_voter');
    }
};
