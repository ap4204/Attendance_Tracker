@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <h1 class="text-3xl font-bold mb-4 sm:mb-0">Timetable</h1>
            <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    📷 Upload Image
                </button>
                <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    + Add Entry
                </button>
            </div>
        </div>
        
        @if($semesters->isNotEmpty())
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <form method="GET" action="{{ route('timetable.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-4">
                <label class="text-sm font-medium">Filter by Semester:</label>
                <select name="semester_id" onchange="this.form.submit()" 
                    class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    <option value="">All Semesters</option>
                    @foreach($semesters as $semester)
                        <option value="{{ $semester->id }}" {{ $selectedSemester && $selectedSemester->id == $semester->id ? 'selected' : '' }}>
                            {{ $semester->name }} ({{ $semester->start_date->format('M Y') }} - {{ $semester->end_date->format('M Y') }})
                        </option>
                    @endforeach
                </select>
                @if($selectedSemester)
                <a href="{{ route('semesters.show', $selectedSemester->id) }}" 
                    class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm">
                    View Semester Details
                </a>
                @endif
            </form>
        </div>
        @endif
    </div>

    <!-- Upload Modal -->
    <div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-gray-800 rounded-lg p-4 sm:p-6 max-w-md w-full mx-2 sm:mx-4 my-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4">Upload Timetable Image</h2>
            @if(!auth()->user()->division)
            <div class="bg-red-800 border border-red-700 text-red-100 px-4 py-3 rounded mb-4">
                <p class="font-semibold">⚠️ Division not set!</p>
                <p class="text-sm mt-1">Please set your division in <a href="{{ route('profile.edit') }}" class="underline">Profile</a> first.</p>
            </div>
            @else
            <div class="bg-blue-800 border border-blue-700 text-blue-100 px-4 py-3 rounded mb-4">
                <p class="text-sm">Extracting timetable for: <strong>Division {{ auth()->user()->division }}</strong></p>
                <p class="text-xs mt-1">Change division in <a href="{{ route('profile.edit') }}" class="underline">Profile</a> if needed</p>
            </div>
            @endif
            <form method="POST" action="{{ route('timetable.upload') }}" enctype="multipart/form-data">
                @csrf
                @if(!auth()->user()->semester_start_date)
                <div class="bg-yellow-800 border border-yellow-700 text-yellow-100 px-4 py-3 rounded mb-4">
                    <p class="text-sm">⚠️ Semester start date not set in profile.</p>
                    <p class="text-xs mt-1">Set it in <a href="{{ route('profile.edit') }}" class="underline">Profile</a> to automatically calculate dates for timetable entries.</p>
                </div>
                @else
                <div class="bg-green-800 border border-green-700 text-green-100 px-4 py-3 rounded mb-4">
                    <p class="text-sm">✓ Using semester start date: <strong>{{ optional(auth()->user()->semester_start_date)->format('F j, Y') }}</strong></p>
                    <p class="text-xs mt-1">Change in <a href="{{ route('profile.edit') }}" class="underline">Profile</a> if needed</p>
                </div>
                @endif
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Select Timetable Image</label>
                    <input type="file" name="image" accept="image/*" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    <p class="text-xs text-gray-400 mt-1">Upload the timetable image (JPG, PNG, GIF, max 2MB)</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                    <button type="submit" {{ !auth()->user()->division ? 'disabled' : '' }} 
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded {{ !auth()->user()->division ? 'opacity-50 cursor-not-allowed' : '' }}">
                        Upload & Parse
                    </button>
                    <button type="button" onclick="document.getElementById('uploadModal').classList.add('hidden')" 
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Entry Modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-gray-800 rounded-lg p-4 sm:p-6 max-w-md w-full mx-2 sm:mx-4 my-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4">Add Timetable Entry</h2>
            <form method="POST" action="{{ route('timetable.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Subject</label>
                    <select name="subject_id" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        <option value="">Select Subject</option>
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Day of Week</label>
                    <select name="day_of_week" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                </div>
                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Start Time</label>
                        <input type="time" name="start_time" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-2">End Time</label>
                        <input type="time" name="end_time" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Specific Date (optional, leave empty for recurring)</label>
                    <input type="date" name="specific_date"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                </div>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Add
                    </button>
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" 
                        class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($entries->isEmpty())
    <div class="bg-gray-800 rounded-lg p-8 text-center">
        <p class="text-gray-400 text-lg">No timetable entries yet.</p>
    </div>
    @else
    <div class="space-y-6">
        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
            @if($entries->has($day))
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-xl font-bold mb-4">{{ $day }}</h2>
                <div class="space-y-3">
                    @foreach($entries[$day] as $entry)
                    <div class="bg-gray-700 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-white">{{ $entry->subject->name }}</h3>
                                @if($entry->semester)
                                <span class="bg-purple-600 text-white px-2 py-1 rounded text-xs">
                                    {{ $entry->semester->name }}
                                </span>
                                @endif
                                @if($entry->lecture_number)
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                    Lec {{ $entry->lecture_number }}
                                </span>
                                @endif
                            </div>
                            <p class="text-gray-400 text-sm mt-1">
                                {{ \Carbon\Carbon::parse($entry->start_time)->format('H:i') }} - 
                                {{ \Carbon\Carbon::parse($entry->end_time)->format('H:i') }}
                                @if($entry->instructor) | {{ $entry->instructor }} @endif
                                @if($entry->location) | {{ $entry->location }} @endif
                            </p>
                            @if($entry->specific_date)
                                <p class="text-gray-500 text-xs mt-1">One-time: {{ $entry->specific_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('timetable.destroy', $entry->id) }}" class="mt-2 sm:mt-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Are you sure?')" 
                                class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                                Delete
                            </button>
                        </form>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach
    </div>
    @endif
</div>
@endsection

