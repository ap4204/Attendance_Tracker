<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->integer('lecture_number')->nullable()->after('day_of_week');
            $table->string('division')->nullable()->after('lecture_number');
            $table->string('instructor')->nullable()->after('division');
            $table->string('location')->nullable()->after('instructor');
        });
    }

    public function down(): void
    {
        Schema::table('timetable_entries', function (Blueprint $table) {
            $table->dropColumn(['lecture_number', 'division', 'instructor', 'location']);
        });
    }
};

