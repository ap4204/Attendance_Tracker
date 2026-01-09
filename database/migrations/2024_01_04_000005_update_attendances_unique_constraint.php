<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'subject_id', 'date', 'status'],
                'attendances_user_subject_date_status_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropUnique(
                'attendances_user_subject_date_status_unique'
            );
        });
    }
};
