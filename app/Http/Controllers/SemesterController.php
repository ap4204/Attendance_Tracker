<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Models\TimetableEntry;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SemesterController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $semesters = $user->semesters()->withCount(['timetableEntries', 'attendances'])->get();
        
        return view('semesters.index', compact('semesters'));
    }

    public function create()
    {
        return view('semesters.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:50',
            'semester_number' => 'nullable|integer|min:1|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        
        Semester::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'course' => $request->course ?: $user->course,
            'batch' => $request->batch ?: $user->batch,
            'semester_number' => $request->semester_number ?: $user->semester,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'notes' => $request->notes,
        ]);

        return redirect()->route('semesters.index')
            ->with('success', 'Semester created successfully.');
    }

    public function show($id)
    {
        $user = auth()->user();
        $semester = $user->semesters()->with(['timetableEntries.subject', 'attendances.subject'])->findOrFail($id);
        
        // Get timetable entries grouped by day
        $timetable = $semester->timetableEntries()
            ->with('subject')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');
        
        // Get attendance statistics
        $attendanceStats = $this->getAttendanceStats($semester);
        
        // Get attendance by date
        $attendancesByDate = $semester->attendances()
            ->with(['subject', 'timetableEntry'])
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('date');
        
        // Get calendar data
        $calendarData = $this->getCalendarData($semester);
        
        return view('semesters.show', compact('semester', 'timetable', 'attendanceStats', 'attendancesByDate', 'calendarData'));
    }

    public function edit($id)
    {
        $user = auth()->user();
        $semester = $user->semesters()->findOrFail($id);
        
        return view('semesters.edit', compact('semester'));
    }

    public function update(Request $request, $id)
    {
        $user = auth()->user();
        $semester = $user->semesters()->findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255',
            'course' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:50',
            'semester_number' => 'nullable|integer|min:1|max:10',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $semester->update($request->all());

        return redirect()->route('semesters.show', $semester->id)
            ->with('success', 'Semester updated successfully.');
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $semester = $user->semesters()->findOrFail($id);
        
        $semester->delete();

        return redirect()->route('semesters.index')
            ->with('success', 'Semester deleted successfully.');
    }

    public function calendar($id, Request $request)
    {
        $user = auth()->user();
        $semester = $user->semesters()->findOrFail($id);
        
        // Get month from request or default to current month within semester
        $requestMonth = $request->input('month');
        if ($requestMonth) {
            $viewDate = \Carbon\Carbon::parse($requestMonth . '-01');
        } else {
            $today = \Carbon\Carbon::today();
            // Use current month if it's within semester, otherwise use semester start month
            $viewDate = ($today >= $semester->start_date && $today <= $semester->end_date) 
                ? $today->copy()->startOfMonth() 
                : $semester->start_date->copy()->startOfMonth();
        }
        
        // Ensure view date is within semester
        if ($viewDate < $semester->start_date->copy()->startOfMonth()) {
            $viewDate = $semester->start_date->copy()->startOfMonth();
        }
        if ($viewDate > $semester->end_date->copy()->startOfMonth()) {
            $viewDate = $semester->end_date->copy()->startOfMonth();
        }
        
        $calendarData = $this->getCalendarDataForMonth($semester, $viewDate);
        
        // Calculate month statistics
        $monthStats = $this->getMonthStats($semester, $viewDate);
        
        // Navigation months
        $prevMonth = $viewDate->copy()->subMonth();
        $nextMonth = $viewDate->copy()->addMonth();
        
        // Check if navigation is allowed
        $canGoPrev = $prevMonth >= $semester->start_date->copy()->startOfMonth();
        $canGoNext = $nextMonth <= $semester->end_date->copy()->startOfMonth();
        
        return view('semesters.calendar', compact(
            'semester', 
            'calendarData', 
            'viewDate',
            'monthStats',
            'prevMonth',
            'nextMonth',
            'canGoPrev',
            'canGoNext'
        ));
    }
    
    private function getCalendarDataForMonth($semester, $viewDate)
    {
        $startDate = $semester->start_date->copy();
        $endDate = $semester->end_date->copy();
        
        // Month boundaries
        $monthStart = $viewDate->copy()->startOfMonth();
        $monthEnd = $viewDate->copy()->endOfMonth();
        
        // Get attendances in this month
        $attendances = $semester->attendances()
            ->whereBetween('date', [
                max($monthStart, $startDate),
                min($monthEnd, $endDate)
            ])
            ->with(['subject', 'timetableEntry'])
            ->get()
            ->groupBy('date');
        
        // Get all timetable entries
        $timetableEntries = $semester->timetableEntries()
            ->with('subject')
            ->get();
        
        $calendar = [];
        $calendarStart = $monthStart->copy()->startOfWeek(); // Start from Monday
        $calendarEnd = $monthEnd->copy()->endOfWeek(); // End on Sunday
        
        $currentDate = $calendarStart->copy();
        
        while ($currentDate <= $calendarEnd) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->format('l');
            
            // Check if date is within semester
            $isInSemester = $currentDate >= $startDate && $currentDate <= $endDate;
            $isInMonth = $currentDate->month === $viewDate->month;
            
            // Get classes for this day
            $dayClasses = $timetableEntries->filter(function ($entry) use ($dayOfWeek, $currentDate) {
                if ($entry->day_of_week === $dayOfWeek) {
                    if ($entry->specific_date === null || $entry->specific_date->format('Y-m-d') === $currentDate->format('Y-m-d')) {
                        return true;
                    }
                }
                return false;
            });
            
            // Get attendances for this date
            $dayAttendances = $attendances->get($dateKey, collect());
            
            $presentCount = $dayAttendances->where('status', 'present')->count();
            $absentCount = $dayAttendances->where('status', 'absent')->count();
            $totalAttendance = $dayAttendances->where('status', '!=', 'cancelled')->count();
            $attendancePercentage = $totalAttendance > 0 ? round(($presentCount / $totalAttendance) * 100) : 0;
            
            $calendar[$dateKey] = [
                'date' => $currentDate->copy(),
                'day_of_week' => $dayOfWeek,
                'classes' => $dayClasses,
                'attendances' => $dayAttendances,
                'total_classes' => $dayClasses->count(),
                'marked_attendance' => $dayAttendances->count(),
                'present_count' => $presentCount,
                'absent_count' => $absentCount,
                'attendance_percentage' => $attendancePercentage,
                'is_in_semester' => $isInSemester,
                'is_in_month' => $isInMonth,
            ];
            
            $currentDate->addDay();
        }
        
        return $calendar;
    }
    
    private function getMonthStats($semester, $viewDate)
    {
        $monthStart = $viewDate->copy()->startOfMonth();
        $monthEnd = $viewDate->copy()->endOfMonth();
        $startDate = $semester->start_date->copy();
        $endDate = $semester->end_date->copy();
        
        $effectiveStart = max($monthStart, $startDate);
        $effectiveEnd = min($monthEnd, $endDate);
        
        $attendances = $semester->attendances()
            ->whereBetween('date', [$effectiveStart, $effectiveEnd])
            ->where('status', '!=', 'cancelled')
            ->get();
        
        $total = $attendances->count();
        $present = $attendances->where('status', 'present')->count();
        $absent = $attendances->where('status', 'absent')->count();
        $percentage = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'percentage' => $percentage,
        ];
    }

    private function getAttendanceStats($semester)
    {
        $total = $semester->attendances()->where('status', '!=', 'cancelled')->count();
        $present = $semester->attendances()->where('status', 'present')->count();
        $absent = $semester->attendances()->where('status', 'absent')->count();
        $cancelled = $semester->attendances()->where('status', 'cancelled')->count();
        $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
        
        // Subject-wise stats
        $subjectStats = $semester->attendances()
            ->selectRaw('subject_id, 
                COUNT(*) as total,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent')
            ->where('status', '!=', 'cancelled')
            ->groupBy('subject_id')
            ->with('subject')
            ->get()
            ->map(function ($stat) {
                $percentage = $stat->total > 0 ? round(($stat->present / $stat->total) * 100, 2) : 0;
                return [
                    'subject' => $stat->subject->name,
                    'total' => $stat->total,
                    'present' => $stat->present,
                    'absent' => $stat->absent,
                    'percentage' => $percentage,
                ];
            });
        
        return [
            'total' => $total,
            'present' => $present,
            'absent' => $absent,
            'cancelled' => $cancelled,
            'percentage' => $percentage,
            'subject_stats' => $subjectStats,
        ];
    }

    private function getCalendarData($semester)
    {
        $startDate = $semester->start_date->copy();
        $endDate = $semester->end_date->copy();
        
        // Get all attendances in date range
        $attendances = $semester->attendances()
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['subject', 'timetableEntry'])
            ->get()
            ->groupBy('date');
        
        // Get all timetable entries
        $timetableEntries = $semester->timetableEntries()
            ->with('subject')
            ->get();
        
        $calendar = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->format('l');
            
            // Get classes for this day
            $dayClasses = $timetableEntries->filter(function ($entry) use ($dayOfWeek, $currentDate) {
                if ($entry->day_of_week === $dayOfWeek) {
                    // Check if it's a recurring entry or specific date matches
                    if ($entry->specific_date === null || $entry->specific_date->format('Y-m-d') === $currentDate->format('Y-m-d')) {
                        return true;
                    }
                }
                return false;
            });
            
            // Get attendances for this date
            $dayAttendances = $attendances->get($dateKey, collect());
            
            $calendar[$dateKey] = [
                'date' => $currentDate->copy(),
                'day_of_week' => $dayOfWeek,
                'classes' => $dayClasses,
                'attendances' => $dayAttendances,
                'total_classes' => $dayClasses->count(),
                'marked_attendance' => $dayAttendances->count(),
            ];
            
            $currentDate->addDay();
        }
        
        return $calendar;
    }
}

