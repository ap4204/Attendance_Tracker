<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimetableEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'semester_id',
        'subject_id',
        'day_of_week',
        'lecture_number',
        'division',
        'instructor',
        'location',
        'start_time',
        'end_time',
        'specific_date',
    ];

    protected $casts = [
        'specific_date' => 'date',
    ];

    public function getStartTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function getEndTimeAttribute($value)
    {
        return $value ? \Carbon\Carbon::parse($value) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }
}

