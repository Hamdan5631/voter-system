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
        Schema::create('voter_worker_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voter_id')->constrained('voters')->onDelete('cascade');
            $table->foreignId('worker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade'); // team_lead_id
            $table->text('remark')->nullable();
            $table->timestamps();

            // Ensure one voter can only be assigned to one worker
            $table->unique('voter_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voter_worker_assignments');
    }
};
