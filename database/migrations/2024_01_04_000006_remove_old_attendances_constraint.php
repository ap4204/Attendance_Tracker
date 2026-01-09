<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Safely try to drop the old unique constraint
            try {
                $table->dropUnique(
                    'attendances_user_id_subject_id_date_unique'
                );
            } catch (\Throwable $e) {
                // Ignore if it does not exist (PostgreSQL safe)
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Recreate old constraint only if needed
            $table->unique(
                ['user_id', 'subject_id', 'date'],
                'attendances_user_id_subject_id_date_unique'
            );
        });
    }
};
