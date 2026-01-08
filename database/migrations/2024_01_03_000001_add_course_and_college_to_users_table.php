<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('course')->nullable()->after('division');
            $table->string('college_name')->nullable()->after('course');
            $table->string('batch')->nullable()->after('college_name');
            $table->integer('semester')->nullable()->after('batch');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['course', 'college_name', 'batch', 'semester']);
        });
    }
};

