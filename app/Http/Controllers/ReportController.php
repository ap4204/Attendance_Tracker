<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get current semester or filter by selected semester
        $selectedSemesterId = $request->semester_id;
        $currentSemester = $user->currentSemester();
        
        $subjectsQuery = $user->subjects()->with(['semester', 'attendances']);
        
        // Filter by semester if provided
        if ($selectedSemesterId) {
            $subjectsQuery->where('semester_id', $selectedSemesterId);
        } elseif ($currentSemester) {
            // Default to current semester if no filter
            $subjectsQuery->where('semester_id', $currentSemester->id);
        }
        
        $subjects = $subjectsQuery->get();
        
        $subjects = $subjects->map(function ($subject) {
            $total = $subject->attendances()->where('status', '!=', 'cancelled')->count();
            $present = $subject->attendances()->where('status', 'present')->count();
            $percentage = $total > 0 ? round(($present / $total) * 100, 2) : 0;
            
            return [
                'id' => $subject->id,
                'name' => $subject->name,
                'target_percentage' => $subject->target_percentage,
                'total' => $total,
                'present' => $present,
                'absent' => $total - $present,
                'percentage' => $percentage,
                'status' => $percentage >= $subject->target_percentage ? 'good' : 'warning',
            ];
        });
        
        $semesters = $user->semesters()->orderBy('start_date', 'desc')->get();
        
        return view('reports.index', compact('subjects', 'semesters', 'currentSemester', 'selectedSemesterId'));
    }

    public function downloadPdf(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        $user = auth()->user();
        $month = Carbon::createFromFormat('Y-m', $request->month);
        $startDate = $month->copy()->startOfMonth();
        $endDate = $month->copy()->endOfMonth();

        $query = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with(['subject', 'timetableEntry']);

        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }

        $attendances = $query->orderBy('date')->orderBy('subject_id')->get();

        $data = [
            'user' => $user,
            'month' => $month->format('F Y'),
            'attendances' => $attendances,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        $pdf = Pdf::loadView('reports.pdf', $data);
        
        $filename = 'attendance_report_' . $month->format('Y_m') . '.pdf';
        
        return $pdf->download($filename);
    }
}

