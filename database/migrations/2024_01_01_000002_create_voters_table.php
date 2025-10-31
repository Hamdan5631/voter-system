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
        Schema::create('voters', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique()->index();
            $table->foreignId('ward_id')->constrained('wards')->onDelete('cascade');
            $table->string('panchayat');
            $table->string('image_path')->nullable();
            $table->boolean('status')->default(false); // voted/unvoted            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voters');
    }
};
