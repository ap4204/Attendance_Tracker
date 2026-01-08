<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\TimetableEntry;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AttendanceController extends Controller
{
    public function mark(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'subject_id' => 'required|exists:subjects,id',
            'timetable_entry_id' => 'nullable|exists:timetable_entries,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,cancelled',
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $user = auth()->user();

        // Verify subject belongs to user
        $subject = $user->subjects()->find($request->subject_id);
        
        if (!$subject) {
            if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Subject not found or does not belong to you.',
                ], 404);
            }
            return back()->with('error', 'Subject not found or does not belong to you.');
        }
        
        $attendanceDate = Carbon::parse($request->date);
        
        // Get semester for this date
        $semester = Semester::where('user_id', $user->id)
            ->where('start_date', '<=', $attendanceDate)
            ->where('end_date', '>=', $attendanceDate)
            ->first();
        
        // If no semester found, try to get current semester or create one
        if (!$semester && $user->semester_start_date) {
            $semester = $this->getOrCreateSemesterForDate($user, $attendanceDate);
        }

        // Check if attendance already exists for this specific timetable entry
        // This allows multiple lectures of the same subject on the same day to have separate attendance records
        $timetableEntryId = $request->timetable_entry_id ?: null;
        
        $attendanceQuery = Attendance::where('user_id', $user->id)
            ->where('subject_id', $request->subject_id)
            ->where('date', $request->date);
        
        if ($timetableEntryId) {
            $attendanceQuery->where('timetable_entry_id', $timetableEntryId);
        } else {
            $attendanceQuery->whereNull('timetable_entry_id');
        }
        
        $attendance = $attendanceQuery->first();

        if ($attendance) {
            $attendance->update([
                'semester_id' => $semester ? $semester->id : null,
                'status' => $request->status,
                'timetable_entry_id' => $timetableEntryId,
                'remarks' => $request->remarks,
            ]);
        } else {
            $attendance = Attendance::create([
                'user_id' => $user->id,
                'semester_id' => $semester ? $semester->id : null,
                'subject_id' => $request->subject_id,
                'timetable_entry_id' => $timetableEntryId,
                'status' => $request->status,
                'date' => $request->date,
                'remarks' => $request->remarks,
            ]);
        }

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance marked successfully.',
                'attendance' => $attendance,
            ]);
        }

        return back()->with('success', 'Attendance marked successfully.');
    }

    /**
     * Get or create semester for a specific date
     */
    private function getOrCreateSemesterForDate($user, $date)
    {
        $startDate = $user->semester_start_date ? Carbon::parse($user->semester_start_date) : $date->copy()->startOfMonth();
        $endDate = $startDate->copy()->addMonths(6);
        
        $semester = Semester::where('user_id', $user->id)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
        
        if (!$semester) {
            $semesterName = $user->course && $user->semester 
                ? "{$user->course} - Semester {$user->semester} ({$user->batch})"
                : "Semester - " . $startDate->format('M Y');
            
            $semester = Semester::create([
                'user_id' => $user->id,
                'name' => $semesterName,
                'course' => $user->course,
                'batch' => $user->batch,
                'semester_number' => $user->semester,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
        }
        
        return $semester;
    }
}

