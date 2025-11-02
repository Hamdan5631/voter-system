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
        // Migrate existing status data to voter_statuses table
        // Check if voter_statuses table exists and has no data yet
        if (Schema::hasTable('voter_statuses') && \DB::table('voter_statuses')->count() === 0) {
            // Migrate existing boolean status to voter_statuses table
            $voters = \DB::table('voters')
                ->whereNotNull('status')
                ->get();
            
            foreach ($voters as $voter) {
                $statusValue = $voter->status == 1 || $voter->status === true ? 'voted' : 'not_voted';
                
                // Use a default user_id (first superadmin or system user) if status_updated_by is null
                $userId = $voter->status_updated_by ?? \DB::table('users')->where('role', 'superadmin')->value('id') ?? 1;
                
                \DB::table('voter_statuses')->insert([
                    'voter_id' => $voter->id,
                    'user_id' => $userId,
                    'status' => $statusValue,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
        
        // Drop status and status_updated_by columns from voters table
        Schema::table('voters', function (Blueprint $table) {
            if (Schema::hasColumn('voters', 'status_updated_by')) {
                $table->dropForeign(['status_updated_by']);
            }
        });
        
        Schema::table('voters', function (Blueprint $table) {
            if (Schema::hasColumn('voters', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('voters', 'status_updated_by')) {
                $table->dropColumn('status_updated_by');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore status columns in voters table
        Schema::table('voters', function (Blueprint $table) {
            $table->boolean('status')->default(false)->after('image_path');
            $table->foreignId('status_updated_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
        });
        
        // Migrate latest status from voter_statuses back to voters table
        $statuses = \DB::table('voter_statuses')
            ->select('voter_id', 'status', 'user_id')
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('voter_id');
        
        foreach ($statuses as $voterId => $voterStatuses) {
            $latestStatus = $voterStatuses->first();
            $statusBool = $latestStatus->status === 'voted' ? true : false;
            
            \DB::table('voters')
                ->where('id', $voterId)
                ->update([
                    'status' => $statusBool,
                    'status_updated_by' => $latestStatus->user_id,
                ]);
        }
    }
};
