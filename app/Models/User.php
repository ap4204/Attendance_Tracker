<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'division',
        'course',
        'college_name',
        'batch',
        'semester',
        'semester_start_date',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'semester_start_date' => 'date',
        ];
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function timetableEntries()
    {
        return $this->hasMany(TimetableEntry::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function semesters()
    {
        return $this->hasMany(Semester::class)->orderBy('start_date', 'desc');
    }

    public function currentSemester()
    {
        $today = now();
        return $this->semesters()
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today)
            ->first();
    }
}

