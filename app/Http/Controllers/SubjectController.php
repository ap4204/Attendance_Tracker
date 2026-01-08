<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Models\Semester;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Get current semester or filter by selected semester
        $selectedSemesterId = $request->semester_id ?: null;
        $currentSemester = $user->currentSemester();
        
        $query = $user->subjects()->with(['semester', 'attendances']);
        
        // Filter by semester if provided
        if ($selectedSemesterId) {
            // Show subjects for selected semester OR subjects with NULL semester_id (legacy subjects)
            $query->where(function($q) use ($selectedSemesterId) {
                $q->where('semester_id', $selectedSemesterId)
                  ->orWhereNull('semester_id');
            });
        } elseif ($currentSemester) {
            // Default to current semester if no filter, but also show NULL subjects
            $query->where(function($q) use ($currentSemester) {
                $q->where('semester_id', $currentSemester->id)
                  ->orWhereNull('semester_id');
            });
        }
        // If no current semester and no filter (All Semesters selected), show all subjects (including NULL)
        
        $subjects = $query->withCount('attendances')->get();
        $semesters = $user->semesters()->orderBy('start_date', 'desc')->get();
        
        return view('subjects.index', compact('subjects', 'semesters', 'currentSemester', 'selectedSemesterId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_percentage' => 'nullable|numeric|min:0|max:100',
            'semester_id' => 'nullable|exists:semesters,id',
        ]);

        $user = auth()->user();
        
        // Get or create current semester if not provided
        $semesterId = $request->semester_id;
        if (!$semesterId) {
            $currentSemester = $user->currentSemester();
            if (!$currentSemester && $user->semester_start_date) {
                // Create semester if it doesn't exist
                $startDate = \Carbon\Carbon::parse($user->semester_start_date);
                $endDate = $startDate->copy()->addMonths(6);
                
                $semesterName = $user->course && $user->semester 
                    ? "{$user->course} - Semester {$user->semester} ({$user->batch})"
                    : "Semester - " . $startDate->format('M Y');
                
                $currentSemester = Semester::create([
                    'user_id' => $user->id,
                    'name' => $semesterName,
                    'course' => $user->course,
                    'batch' => $user->batch,
                    'semester_number' => $user->semester,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ]);
                $semesterId = $currentSemester->id;
            } elseif ($currentSemester) {
                $semesterId = $currentSemester->id;
            }
        } else {
            // Verify semester belongs to user
            $semester = Semester::where('id', $semesterId)
                ->where('user_id', $user->id)
                ->firstOrFail();
        }

        Subject::create([
            'user_id' => $user->id,
            'semester_id' => $semesterId,
            'name' => $request->name,
            'target_percentage' => $request->target_percentage ?? 75,
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject created successfully.');
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'target_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        $subject->update([
            'name' => $request->name,
            'target_percentage' => $request->target_percentage ?? 75,
        ]);

        return redirect()->route('subjects.index')
            ->with('success', 'Subject updated successfully.');
    }

    public function destroy($id)
    {
        $subject = Subject::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $subject->delete();

        return redirect()->route('subjects.index')
            ->with('success', 'Subject deleted successfully.');
    }

    public function assignSemester(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semesters,id',
        ]);

        $user = auth()->user();
        
        // Verify semester belongs to user
        $semester = Semester::where('id', $request->semester_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Assign all NULL semester_id subjects to the selected semester
        $updated = Subject::where('user_id', $user->id)
            ->whereNull('semester_id')
            ->update(['semester_id' => $semester->id]);

        return redirect()->route('subjects.index', ['semester_id' => $semester->id])
            ->with('success', "Successfully assigned {$updated} subject(s) to {$semester->name}.");
    }
}

