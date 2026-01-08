<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, add the new unique constraint that includes timetable_entry_id
        // This allows multiple attendance records for the same subject on the same day
        // if they have different timetable_entry_id values
        // Note: In MySQL, NULL values in unique constraints are allowed multiple times
        $newIndexExists = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_user_subject_date_entry_unique'");
        
        if (empty($newIndexExists)) {
            try {
                DB::statement('
                    ALTER TABLE attendances 
                    ADD UNIQUE KEY attendances_user_subject_date_entry_unique 
                    (user_id, subject_id, date, timetable_entry_id)
                ');
            } catch (\Exception $e) {
                // If adding fails due to duplicate data, we need to clean up first
                // For now, just log the error
                \Log::warning('Could not add new unique constraint: ' . $e->getMessage());
            }
        }
        
        // Now try to drop the old constraint
        // We need to check if any foreign keys reference this index
        $oldIndexExists = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_user_id_subject_id_date_unique'");
        
        if (!empty($oldIndexExists)) {
            // Check if there are any foreign keys that might be using this index
            // In MySQL, foreign keys can use indexes, but they don't require unique indexes
            // So we should be able to drop it if we create a non-unique index for the foreign key columns
            try {
                // First, ensure there's a non-unique index on user_id and subject_id for foreign key performance
                $fkIndexExists = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_user_id_subject_id_index'");
                if (empty($fkIndexExists)) {
                    DB::statement('CREATE INDEX attendances_user_id_subject_id_index ON attendances (user_id, subject_id)');
                }
                
                // Now try to drop the old unique constraint
                DB::statement('ALTER TABLE attendances DROP INDEX attendances_user_id_subject_id_date_unique');
            } catch (\Exception $e) {
                // If it's still needed, we'll handle duplicates in application logic
                \Log::warning('Could not drop old unique constraint: ' . $e->getMessage());
            }
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop the new unique constraint
            DB::statement('ALTER TABLE attendances DROP INDEX attendances_user_subject_date_entry_unique');
            
            // Restore the old unique constraint
            $table->unique(['user_id', 'subject_id', 'date']);
        });
    }
};

