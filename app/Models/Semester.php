<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Semester extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'course',
        'batch',
        'semester_number',
        'start_date',
        'end_date',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function getTotalLecturesAttribute()
    {
        return $this->timetableEntries()->count();
    }

    public function getTotalAttendancesAttribute()
    {
        return $this->attendances()->where('status', '!=', 'cancelled')->count();
    }

    public function getPresentCountAttribute()
    {
        return $this->attendances()->where('status', 'present')->count();
    }

    public function getAttendancePercentageAttribute()
    {
        $total = $this->total_attendances;
        if ($total === 0) {
            return 0;
        }
        return round(($this->present_count / $total) * 100, 2);
    }
}

