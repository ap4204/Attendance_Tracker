<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // PostgreSQL & MySQL safe
            $table->dropUnique([
                'user_id',
                'subject_id',
                'date'
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'subject_id', 'date'],
                'attendances_user_id_subject_id_date_unique'
            );
        });
    }
};
