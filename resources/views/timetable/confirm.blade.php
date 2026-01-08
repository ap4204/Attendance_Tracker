@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-6">Confirm Timetable Entries</h1>

    @if($errors->any())
    <div class="bg-red-800 border border-red-700 text-red-100 px-4 py-3 rounded mb-6">
        <p class="font-semibold mb-2">⚠️ Validation Errors:</p>
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-800 border border-red-700 text-red-100 px-4 py-3 rounded mb-6">
        <p class="font-semibold">{{ session('error') }}</p>
    </div>
    @endif

    @if(session('warnings'))
    <div class="bg-yellow-800 border border-yellow-700 text-yellow-100 px-4 py-3 rounded mb-6">
        <p class="font-semibold mb-2">⚠️ Warnings:</p>
        <ul class="list-disc list-inside space-y-1 text-sm">
            @foreach(session('warnings') as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('timetable.confirm') }}">
        @csrf
        
        @if(isset($semesterStartDate) && $semesterStartDate)
        <div class="bg-blue-800 border border-blue-700 text-blue-100 px-4 py-3 rounded mb-6">
            <p class="font-semibold">📅 Semester Start Date: {{ \Carbon\Carbon::parse($semesterStartDate)->format('F j, Y') }}</p>
            <p class="text-sm mt-1">Timetable entries will be created for specific dates based on this start date and the day of week.</p>
            <p class="text-xs mt-1">Change in <a href="{{ route('profile.edit') }}" class="underline">Profile</a> if needed</p>
        </div>
        @else
        <div class="bg-yellow-800 border border-yellow-700 text-yellow-100 px-4 py-3 rounded mb-6">
            <p class="font-semibold">⚠️ Semester Start Date Not Set</p>
            <p class="text-sm mt-1">Set your semester start date in <a href="{{ route('profile.edit') }}" class="underline font-semibold">Profile</a> to automatically calculate dates for timetable entries.</p>
            <p class="text-xs mt-1">Without it, entries will be created as recurring (no specific dates).</p>
        </div>
        @endif

        @if(empty($parsedData))
        <div class="bg-yellow-800 border border-yellow-700 text-yellow-100 px-4 py-3 rounded mb-6">
            <p class="font-semibold mb-2">⚠️ No data was automatically extracted.</p>
            @if(!isset($tesseractAvailable) || !$tesseractAvailable)
            <div class="mt-2 text-sm">
                <p class="mb-1"><strong>Possible reasons:</strong></p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Tesseract OCR is not installed or not found</li>
                    <li>TESSERACT_PATH is not set correctly in .env file</li>
                    <li>The image quality might be too low for OCR</li>
                </ul>
                <p class="mt-2">
                    <strong>Solution:</strong> Install Tesseract OCR and set <code class="bg-gray-900 px-1 rounded">TESSERACT_PATH="C:\\Program Files\\Tesseract-OCR\\tesseract.exe"</code> in your .env file, or add entries manually below.
                </p>
            </div>
            @else
            <div class="mt-2 text-sm">
                <p class="mb-2">✓ Tesseract is installed at: <code class="bg-gray-900 px-1 rounded">{{ $tesseractPath ?? 'Not specified' }}</code></p>
                <p class="mb-1">However, no data could be extracted. Possible reasons:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>The image quality might be too low for OCR</li>
                    <li>The timetable format might not be recognized</li>
                    <li>The text extraction succeeded but parsing failed</li>
                </ul>
                @if(isset($rawOcrText) && !empty($rawOcrText))
                <details class="mt-3">
                    <summary class="cursor-pointer font-semibold hover:text-yellow-200">📄 Show Extracted Text (for debugging)</summary>
                    <div class="mt-2 p-3 bg-gray-900 rounded text-xs font-mono whitespace-pre-wrap break-words max-h-40 overflow-y-auto border border-gray-700">
                        {{ $rawOcrText }}
                    </div>
                    <p class="mt-1 text-xs text-gray-300">Length: {{ strlen($rawOcrText) }} characters</p>
                    <p class="mt-1 text-xs text-gray-400">
                        💡 <strong>Tip:</strong> If the extracted text is incomplete, try:
                        <ul class="list-disc list-inside mt-1 ml-2">
                            <li>Uploading a higher resolution image</li>
                            <li>Using a clearer/less blurry image</li>
                            <li>Ensuring the image is well-lit and in focus</li>
                        </ul>
                    </p>
                </details>
                @else
                <p class="mt-2 text-xs text-gray-300">No text was extracted from the image. This usually means the image quality is too low for OCR.</p>
                @endif
                <p class="mt-2">
                    <strong>Solution:</strong> The OCR extracted some text but couldn't parse it. Please add entries manually below, or try uploading a clearer/higher resolution image.
                </p>
            </div>
            @endif
        </div>
        @else
        <div class="bg-green-800 border border-green-700 text-green-100 px-4 py-3 rounded mb-6">
            <p class="font-semibold mb-2">✓ Successfully extracted {{ count($parsedData) }} timetable entries!</p>
            <p class="text-sm">Extracted for: <strong>Division {{ $division }}</strong></p>
            <p class="text-sm mt-1">Please review and confirm below. Times are auto-fetched from the image.</p>
        </div>
        @endif
        
        @if(!empty($parsedData))
        <div class="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700">
            <h3 class="text-lg font-semibold mb-3">Extracted Lectures Summary (Division {{ $division }})</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($parsedData as $index => $entry)
                <div class="bg-gray-700 rounded p-3 text-sm">
                    <div class="flex items-center gap-2 mb-1">
                        @if(isset($entry['lecture_number']))
                        <span class="bg-blue-600 text-white px-2 py-0.5 rounded text-xs font-semibold">
                            Lec {{ $entry['lecture_number'] }}
                        </span>
                        @endif
                        <span class="text-gray-300 font-medium">{{ $entry['subject_name'] ?? 'N/A' }}</span>
                    </div>
                    <p class="text-gray-400 text-xs">
                        ⏰ {{ $entry['start_time'] ?? 'N/A' }} - {{ $entry['end_time'] ?? 'N/A' }}
                    </p>
                    @if(isset($entry['instructor']) && $entry['instructor'])
                    <p class="text-gray-400 text-xs">👤 {{ $entry['instructor'] }}</p>
                    @endif
                    @if(isset($entry['location']) && $entry['location'])
                    <p class="text-gray-400 text-xs">📍 {{ $entry['location'] }}</p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div id="entries-container" class="space-y-4 mb-6">
            @if(!empty($parsedData))
                @foreach($parsedData as $index => $entry)
                <div class="entry-row bg-gray-800 rounded-lg p-4 sm:p-6 border border-gray-700">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Lecture Number</label>
                            <input type="number" name="entries[{{ $index }}][lecture_number]" 
                                value="{{ $entry['lecture_number'] ?? '' }}" min="1" max="10"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Subject</label>
                            <select name="entries[{{ $index }}][subject_id]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" 
                                        {{ (isset($entry['subject_name']) && stripos($subject->name, $entry['subject_name']) !== false) ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Day of Week</label>
                            <select name="entries[{{ $index }}][day_of_week]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <option value="{{ $day }}" {{ (isset($entry['day_of_week']) && $entry['day_of_week'] === $day) ? 'selected' : '' }}>
                                        {{ $day }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Division</label>
                            <select name="entries[{{ $index }}][division]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                                <option value="{{ $division ?? auth()->user()->division }}" selected>
                                    Division {{ $division ?? auth()->user()->division ?? 'A' }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Start Time</label>
                            <input type="time" name="entries[{{ $index }}][start_time]" 
                                value="{{ $entry['start_time'] ?? '' }}" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">End Time</label>
                            <input type="time" name="entries[{{ $index }}][end_time]" 
                                value="{{ $entry['end_time'] ?? '' }}" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Instructor</label>
                            <input type="text" name="entries[{{ $index }}][instructor]" 
                                value="{{ $entry['instructor'] ?? '' }}"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white"
                                placeholder="e.g., NNS, JCB">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Location</label>
                            <input type="text" name="entries[{{ $index }}][location]" 
                                value="{{ $entry['location'] ?? '' }}"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white"
                                placeholder="e.g., Class #502, Lab #409">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Specific Date (optional, leave empty for recurring)</label>
                            <input type="date" name="entries[{{ $index }}][specific_date]"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                    </div>
                    <button type="button" onclick="this.closest('.entry-row').remove()" 
                        class="mt-4 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Remove
                    </button>
                </div>
                @endforeach
            @else
                <div class="entry-row bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">Subject</label>
                            <select name="entries[0][subject_id]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                                <option value="">Select Subject</option>
                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Day of Week</label>
                            <select name="entries[0][day_of_week]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                                    <option value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">Start Time</label>
                            <input type="time" name="entries[0][start_time]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">End Time</label>
                            <input type="time" name="entries[0][end_time]" required
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Specific Date (optional)</label>
                            <input type="date" name="entries[0][specific_date]"
                                class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
            <button type="button" onclick="addEntry()" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                + Add Another Entry
            </button>
            <button type="submit" 
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Save All Entries
            </button>
            <a href="{{ route('timetable.index') }}" 
                class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-center">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
let entryIndex = {{ !empty($parsedData) ? count($parsedData) : 1 }};

function addEntry() {
    const container = document.getElementById('entries-container');
    const newEntry = document.createElement('div');
    newEntry.className = 'entry-row bg-gray-800 rounded-lg p-4 sm:p-6 border border-gray-700';
    newEntry.innerHTML = `
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium mb-2">Subject</label>
                <select name="entries[${entryIndex}][subject_id]" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Day of Week</label>
                <select name="entries[${entryIndex}][day_of_week]" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                        <option value="{{ $day }}">{{ $day }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">Start Time</label>
                <input type="time" name="entries[${entryIndex}][start_time]" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
            </div>
            <div>
                <label class="block text-sm font-medium mb-2">End Time</label>
                <input type="time" name="entries[${entryIndex}][end_time]" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-2">Specific Date (optional)</label>
                <input type="date" name="entries[${entryIndex}][specific_date]"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
            </div>
        </div>
        <button type="button" onclick="this.closest('.entry-row').remove()" 
            class="mt-4 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
            Remove
        </button>
    `;
    container.appendChild(newEntry);
    entryIndex++;
}
</script>
@endsection

