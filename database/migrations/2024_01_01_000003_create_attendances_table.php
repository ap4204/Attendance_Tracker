<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('subject_id')->constrained()->onDelete('cascade');
            $table->foreignId('timetable_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['present', 'absent', 'cancelled'])->default('absent');
            $table->date('date');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'subject_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};

