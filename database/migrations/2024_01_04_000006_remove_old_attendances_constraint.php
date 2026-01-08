<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if old unique constraint still exists
        $oldIndexExists = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_user_id_subject_id_date_unique'");
        
        if (!empty($oldIndexExists)) {
            // Create a non-unique index for foreign key performance first
            $fkIndexExists = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_user_id_subject_id_index'");
            if (empty($fkIndexExists)) {
                DB::statement('CREATE INDEX attendances_user_id_subject_id_index ON attendances (user_id, subject_id)');
            }
            
            // Now try to drop the old unique constraint
            try {
                DB::statement('ALTER TABLE attendances DROP INDEX attendances_user_id_subject_id_date_unique');
                \Log::info('Successfully removed old unique constraint from attendances table');
            } catch (\Exception $e) {
                \Log::warning('Could not remove old unique constraint: ' . $e->getMessage());
                \Log::warning('The new constraint should still work, but you may need to handle duplicates manually');
            }
        }
    }

    public function down(): void
    {
        // Restore the old unique constraint if needed
        $oldIndexExists = DB::select("SHOW INDEX FROM attendances WHERE Key_name = 'attendances_user_id_subject_id_date_unique'");
        
        if (empty($oldIndexExists)) {
            DB::statement('ALTER TABLE attendances ADD UNIQUE KEY attendances_user_id_subject_id_date_unique (user_id, subject_id, date)');
        }
    }
};

