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
        Schema::table('voters', function (Blueprint $table) {
            $table->unsignedBigInteger('panchayat_id')->nullable()->after('panchayat');
            $table->foreign('panchayat_id')->references('id')->on('panchayats')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('voters', function (Blueprint $table) {
            $table->dropForeign(['panchayat_id']);
            $table->dropColumn('panchayat_id');
        });
    }
};
