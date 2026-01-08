<?php

namespace App\Http\Controllers;

use App\Models\TimetableEntry;
use App\Models\Attendance;
use App\Models\Semester;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today();
        $dayOfWeek = $today->format('l'); // Monday, Tuesday, etc.
        
        // Get today's timetable entries filtered by user's division
        $todayEntries = TimetableEntry::where('user_id', $user->id)
            ->where(function ($query) use ($user) {
                if ($user->division) {
                    $query->where('division', $user->division);
                }
            })
            ->where(function ($query) use ($today, $dayOfWeek) {
                $query->where('day_of_week', $dayOfWeek)
                    ->whereNull('specific_date')
                    ->orWhere('specific_date', $today);
            })
            ->with(['subject', 'attendances' => function ($query) use ($today) {
                $query->where('date', $today);
            }])
            ->orderBy('lecture_number')
            ->orderBy('start_time')
            ->get();
        
        // Get attendance status for each entry with lecture details
        $schedule = $todayEntries->map(function ($entry) use ($today) {
            $attendance = $entry->attendances->first();
            return [
                'id' => $entry->id,
                'lecture_number' => $entry->lecture_number,
                'subject' => $entry->subject->name,
                'subject_id' => $entry->subject_id,
                'instructor' => $entry->instructor,
                'location' => $entry->location,
                'start_time' => $entry->start_time->format('H:i'),
                'end_time' => $entry->end_time->format('H:i'),
                'status' => $attendance ? $attendance->status : null,
                'attendance_id' => $attendance ? $attendance->id : null,
            ];
        });
        
        // Get overall attendance statistics
        $totalLectures = $user->attendances()
            ->where('status', '!=', 'cancelled')
            ->where('date', '<=', $today)
            ->count();
        
        $presentLectures = $user->attendances()
            ->where('status', 'present')
            ->where('date', '<=', $today)
            ->count();
        
        $overallPercentage = $totalLectures > 0 
            ? round(($presentLectures / $totalLectures) * 100, 2) 
            : 0;
        
        // Get current semester
        $currentSemester = $user->currentSemester();
        
        // Get semester-specific stats if available
        $semesterStats = null;
        if ($currentSemester) {
            $semesterTotal = $currentSemester->attendances()
                ->where('status', '!=', 'cancelled')
                ->where('date', '<=', $today)
                ->count();
            $semesterPresent = $currentSemester->attendances()
                ->where('status', 'present')
                ->where('date', '<=', $today)
                ->count();
            $semesterPercentage = $semesterTotal > 0 
                ? round(($semesterPresent / $semesterTotal) * 100, 2) 
                : 0;
            
            $semesterStats = [
                'name' => $currentSemester->name,
                'total' => $semesterTotal,
                'present' => $semesterPresent,
                'percentage' => $semesterPercentage,
            ];
        }
        
        $selectedDate = null; // Not used in index, but needed for view compatibility
        return view('dashboard', compact('schedule', 'today', 'selectedDate', 'totalLectures', 'presentLectures', 'overallPercentage', 'currentSemester', 'semesterStats'));
    }

    public function byDate(Request $request)
    {
        $user = auth()->user();
        
        // Get the date from request or default to today
        $selectedDate = $request->has('date') && $request->date 
            ? Carbon::parse($request->date) 
            : Carbon::today();
        
        $dayOfWeek = $selectedDate->format('l'); // Monday, Tuesday, etc.
        
        // Get timetable entries for the selected date filtered by user's division
        $dateEntries = TimetableEntry::where('user_id', $user->id)
            ->where(function ($query) use ($user) {
                if ($user->division) {
                    $query->where('division', $user->division);
                }
            })
            ->where(function ($query) use ($selectedDate, $dayOfWeek) {
                $query->where(function($q) use ($dayOfWeek) {
                    $q->where('day_of_week', $dayOfWeek)
                      ->whereNull('specific_date');
                })
                ->orWhere('specific_date', $selectedDate->format('Y-m-d'));
            })
            ->with(['subject', 'attendances' => function ($query) use ($selectedDate) {
                $query->where('date', $selectedDate->format('Y-m-d'));
            }])
            ->orderBy('lecture_number')
            ->orderBy('start_time')
            ->get();
        
        // Get attendance status for each entry
        $schedule = $dateEntries->map(function ($entry) use ($selectedDate) {
            $attendance = $entry->attendances->first();
            return [
                'id' => $entry->id,
                'lecture_number' => $entry->lecture_number,
                'subject' => $entry->subject->name,
                'subject_id' => $entry->subject_id,
                'instructor' => $entry->instructor,
                'location' => $entry->location,
                'start_time' => $entry->start_time->format('H:i'),
                'end_time' => $entry->end_time->format('H:i'),
                'status' => $attendance ? $attendance->status : null,
                'attendance_id' => $attendance ? $attendance->id : null,
            ];
        });
        
        // Get overall attendance statistics
        $totalLectures = $user->attendances()
            ->where('status', '!=', 'cancelled')
            ->where('date', '<=', Carbon::today())
            ->count();
        
        $presentLectures = $user->attendances()
            ->where('status', 'present')
            ->where('date', '<=', Carbon::today())
            ->count();
        
        $overallPercentage = $totalLectures > 0 
            ? round(($presentLectures / $totalLectures) * 100, 2) 
            : 0;
        
        // Get current semester
        $currentSemester = $user->currentSemester();
        
        // Get semester-specific stats if available
        $semesterStats = null;
        if ($currentSemester) {
            $semesterTotal = $currentSemester->attendances()
                ->where('status', '!=', 'cancelled')
                ->where('date', '<=', Carbon::today())
                ->count();
            $semesterPresent = $currentSemester->attendances()
                ->where('status', 'present')
                ->where('date', '<=', Carbon::today())
                ->count();
            $semesterPercentage = $semesterTotal > 0 
                ? round(($semesterPresent / $semesterTotal) * 100, 2) 
                : 0;
            
            $semesterStats = [
                'name' => $currentSemester->name,
                'total' => $semesterTotal,
                'present' => $semesterPresent,
                'percentage' => $semesterPercentage,
            ];
        }
        
        return view('dashboard', compact('schedule', 'selectedDate', 'totalLectures', 'presentLectures', 'overallPercentage', 'currentSemester', 'semesterStats'));
    }
}

