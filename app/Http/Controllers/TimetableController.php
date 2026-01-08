<?php

namespace App\Http\Controllers;

use App\Models\TimetableEntry;
use App\Models\Subject;
use App\Models\Semester;
use App\Services\OcrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

class TimetableController extends Controller
{
    protected $ocrService;

    public function __construct(OcrService $ocrService)
    {
        $this->ocrService = $ocrService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = TimetableEntry::where('user_id', $user->id)
            ->with(['subject', 'semester']);
        
        // Filter by semester if provided
        if ($request->has('semester_id') && $request->semester_id) {
            $query->where('semester_id', $request->semester_id);
        }
        
        $entries = $query->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');
        
        // Get subjects for the selected semester or current semester
        $selectedSemesterId = $request->semester_id;
        $currentSemester = $user->currentSemester();
        
        $subjectsQuery = Subject::where('user_id', $user->id);
        if ($selectedSemesterId) {
            $subjectsQuery->where('semester_id', $selectedSemesterId);
        } elseif ($currentSemester) {
            $subjectsQuery->where('semester_id', $currentSemester->id);
        }
        $subjects = $subjectsQuery->get();
        
        $semesters = $user->semesters()->orderBy('start_date', 'desc')->get();
        $selectedSemester = $selectedSemesterId ? $semesters->find($selectedSemesterId) : null;
        
        return view('timetable.index', compact('entries', 'subjects', 'semesters', 'selectedSemester', 'currentSemester'));
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        
        // Use division from user profile
        if (!$user->division) {
            return redirect()->route('profile.edit')
                ->with('error', 'Please set your division in your profile before uploading timetable.');
        }
        
        $division = $user->division;
        $file = $request->file('image');
        
        // Use semester start date from user profile (set once in profile)
        $semesterStartDate = optional($user->semester_start_date)->format('Y-m-d');
        
        // Extract timetable for the user's division
        $parsedData = $this->ocrService->extractDivisionTimetable($file, $division);
        
        // Check if Tesseract is available
        $tesseractAvailable = $this->ocrService->isTesseractAvailable();
        $tesseractPath = Config::get('ocr.tesseract_path') ?: env('TESSERACT_PATH');

        // Get or create current semester
        $semesterStartDateCarbon = $user->semester_start_date 
            ? \Carbon\Carbon::parse($user->semester_start_date) 
            : null;
        $semester = $this->getOrCreateCurrentSemester($user, $semesterStartDateCarbon);

        // Get user's subjects for the current semester
        // Include both semester-specific subjects and NULL subjects (for backward compatibility)
        $subjectsQuery = $user->subjects();
        if ($semester) {
            $subjectsQuery->where(function($q) use ($semester) {
                $q->where('semester_id', $semester->id)
                  ->orWhereNull('semester_id');
            });
        } else {
            $subjectsQuery->whereNull('semester_id');
        }
        $subjects = $subjectsQuery->get();

        // Get raw OCR text for debugging
        $rawOcrText = $this->ocrService->getRawOcrText();

        return view('timetable.confirm', [
            'parsedData' => $parsedData,
            'subjects' => $subjects,
            'division' => $division,
            'semesterStartDate' => $semesterStartDate,
            'tesseractAvailable' => $tesseractAvailable,
            'tesseractPath' => $tesseractPath,
            'rawOcrText' => $rawOcrText,
        ]);
    }

    public function confirmAndSave(Request $request)
    {
        // Filter out empty entries (entries with no subject_id)
        $entries = array_filter($request->entries ?? [], function($entry) {
            return !empty($entry['subject_id']);
        });
        
        // Re-index the array to ensure sequential keys
        $entries = array_values($entries);
        
        if (empty($entries)) {
            return back()
                ->withInput()
                ->with('error', 'No valid entries to save. Please select subjects for at least one entry.');
        }
        
        $validator = Validator::make(['entries' => $entries], [
            'entries' => 'required|array|min:1',
            'entries.*.day_of_week' => 'required|string',
            'entries.*.start_time' => 'required|date_format:H:i',
            'entries.*.end_time' => 'required|date_format:H:i',
            'entries.*.subject_id' => 'required|exists:subjects,id',
            'entries.*.lecture_number' => 'nullable|integer',
            'entries.*.division' => 'nullable|string',
            'entries.*.instructor' => 'nullable|string',
            'entries.*.location' => 'nullable|string',
            'entries.*.specific_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $errorMessages = [];
            
            // Collect all validation errors
            foreach ($errors->all() as $error) {
                $errorMessages[] = $error;
            }
            
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('error', 'Please fix the errors below: ' . implode(', ', $errorMessages));
        }

        $user = auth()->user();
        $saved = 0;
        $skipped = 0;
        $errors = [];
        
        // Use semester start date from user profile (set once in profile)
        // Ensure it's a Carbon instance
        $semesterStartDate = $user->semester_start_date 
            ? \Carbon\Carbon::parse($user->semester_start_date) 
            : null;
        
        // Get or create current semester
        $semester = $this->getOrCreateCurrentSemester($user, $semesterStartDate);

        foreach ($entries as $index => $entryData) {
            // Verify subject belongs to user and semester
            $subjectQuery = Subject::where('id', $entryData['subject_id'])
                ->where('user_id', $user->id);
            
            if ($semester) {
                $subjectQuery->where(function($q) use ($semester) {
                    $q->where('semester_id', $semester->id)
                      ->orWhereNull('semester_id'); // Also allow NULL subjects
                });
            } else {
                $subjectQuery->whereNull('semester_id');
            }
            
            $subject = $subjectQuery->first();

            if (!$subject) {
                $skipped++;
                $errors[] = "Entry " . ($index + 1) . ": Subject not found or doesn't belong to this semester.";
                continue;
            }

            // Determine specific_date
            $specificDate = null;
            if (!empty($entryData['specific_date'])) {
                // Use the date provided in the form
                $specificDate = \Carbon\Carbon::parse($entryData['specific_date']);
            } elseif ($semesterStartDate) {
                // Calculate date based on semester start date and day of week
                $dayOfWeek = $entryData['day_of_week'];
                $dayMap = [
                    'Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 
                    'Thursday' => 4, 'Friday' => 5, 'Saturday' => 6, 'Sunday' => 7
                ];
                $targetDay = $dayMap[$dayOfWeek] ?? null;
                
                if ($targetDay) {
                    $startDayOfWeek = $semesterStartDate->dayOfWeekIso; // 1=Monday, 7=Sunday
                    $daysToAdd = ($targetDay - $startDayOfWeek + 7) % 7;
                    if ($daysToAdd == 0 && $semesterStartDate->dayOfWeekIso == $targetDay) {
                        $daysToAdd = 0; // Same day
                    } elseif ($daysToAdd == 0) {
                        $daysToAdd = 7; // Next week
                    }
                    $specificDate = $semesterStartDate->copy()->addDays($daysToAdd);
                }
            }

            TimetableEntry::create([
                'user_id' => $user->id,
                'semester_id' => $semester ? $semester->id : null,
                'subject_id' => $subject->id,
                'day_of_week' => $entryData['day_of_week'],
                'lecture_number' => $entryData['lecture_number'] ?? null,
                'division' => $entryData['division'] ?? $user->division,
                'instructor' => $entryData['instructor'] ?? null,
                'location' => $entryData['location'] ?? null,
                'start_time' => $entryData['start_time'],
                'end_time' => $entryData['end_time'],
                'specific_date' => $specificDate ? $specificDate->format('Y-m-d') : null,
            ]);

            $saved++;
        }

        $message = "Successfully saved {$saved} timetable entries.";
        if ($skipped > 0) {
            $message .= " {$skipped} entry(ies) were skipped due to errors.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(' ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= " and " . (count($errors) - 3) . " more.";
                }
            }
        }

        if ($saved === 0) {
            return back()
                ->withInput()
                ->with('error', 'No entries were saved. ' . implode(' ', $errors));
        }

        return redirect()->route('timetable.index')
            ->with('success', $message)
            ->with('warnings', $errors);
    }

    /**
     * Get or create current semester based on user's semester start date
     */
    private function getOrCreateCurrentSemester($user, $semesterStartDate)
    {
        if (!$semesterStartDate) {
            return null;
        }
        
        // Ensure it's a Carbon instance
        $startDate = $semesterStartDate instanceof \Carbon\Carbon 
            ? $semesterStartDate 
            : \Carbon\Carbon::parse($semesterStartDate);
        // Assume semester is 6 months (can be adjusted)
        $endDate = $startDate->copy()->addMonths(6);
        
        // Check if semester already exists for this date range
        $semester = Semester::where('user_id', $user->id)
            ->where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $startDate)
            ->first();
        
        if (!$semester) {
            // Create new semester
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

    public function store(Request $request)
    {
        $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'day_of_week' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'specific_date' => 'nullable|date',
        ]);

        $user = auth()->user();

        // Verify subject belongs to user
        $subject = Subject::where('id', $request->subject_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        TimetableEntry::create([
            'user_id' => $user->id,
            'subject_id' => $request->subject_id,
            'day_of_week' => $request->day_of_week,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'specific_date' => $request->specific_date,
        ]);

        return redirect()->route('timetable.index')
            ->with('success', 'Timetable entry created successfully.');
    }

    public function destroy($id)
    {
        $entry = TimetableEntry::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        $entry->delete();

        return redirect()->route('timetable.index')
            ->with('success', 'Timetable entry deleted successfully.');
    }
}
