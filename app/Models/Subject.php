<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'semester_id',
        'name',
        'target_percentage',
    ];

    protected $casts = [
        'target_percentage' => 'decimal:2',
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

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function getAttendancePercentageAttribute()
    {
        $total = $this->attendances()->where('status', '!=', 'cancelled')->count();
        if ($total === 0) {
            return 0;
        }
        $present = $this->attendances()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }
}

