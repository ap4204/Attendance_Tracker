@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    @php
        $viewDate = isset($selectedDate) ? $selectedDate : $today;
        $isToday = $viewDate->isToday();
        $isPastDate = $viewDate->isPast() && !$isToday;
    @endphp

    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold">{{ $isToday ? "Today's Schedule" : "Schedule for " . $viewDate->format('l, F j, Y') }}</h1>
                <p class="text-gray-400 mt-1">{{ $viewDate->format('l, F j, Y') }}</p>
                @if($isPastDate)
                <p class="text-yellow-400 text-sm mt-1">⚠️ Past Date - Mark attendance for missed entries</p>
                @endif
                @if(auth()->user()->division)
                    <p class="text-gray-500 text-sm mt-1">Division: {{ auth()->user()->division }}</p>
                @else
                    <a href="{{ route('profile.edit') }}" class="text-blue-400 hover:text-blue-300 text-sm">Set your division</a>
                @endif
            </div>
            @if(isset($overallPercentage))
            <div class="mt-4 sm:mt-0 text-right">
                <p class="text-sm text-gray-400">Overall Attendance</p>
                <p class="text-2xl font-bold {{ $overallPercentage >= 75 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $overallPercentage }}%
                </p>
                <p class="text-xs text-gray-500">{{ $presentLectures }}/{{ $totalLectures }} lectures</p>
            </div>
            @endif
        </div>

        <!-- Date Selector -->
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700 mb-4">
            <form method="GET" action="{{ route('dashboard.date') }}" class="flex flex-col sm:flex-row sm:items-center gap-4">
                <label class="text-sm font-medium">View Schedule for Date:</label>
                <input type="date" name="date" value="{{ $viewDate->format('Y-m-d') }}" max="{{ \Carbon\Carbon::today()->format('Y-m-d') }}"
                    class="px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onchange="this.form.submit()">
                @if(!$isToday)
                <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    View Today
                </a>
                @endif
            </form>
        </div>
    </div>

    @if($currentSemester && $semesterStats)
    <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-lg p-6 mb-6 border border-blue-700">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold mb-1">Current Semester: {{ $currentSemester->name }}</h2>
                <p class="text-gray-300 text-sm">
                    {{ $currentSemester->start_date->format('M j, Y') }} - {{ $currentSemester->end_date->format('M j, Y') }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 text-right">
                <p class="text-sm text-gray-300">Semester Attendance</p>
                <p class="text-3xl font-bold {{ $semesterStats['percentage'] >= 75 ? 'text-green-300' : 'text-red-300' }}">
                    {{ $semesterStats['percentage'] }}%
                </p>
                <p class="text-xs text-gray-400">{{ $semesterStats['present'] }}/{{ $semesterStats['total'] }} classes</p>
            </div>
        </div>
        <div class="mt-4 flex space-x-2">
            <a href="{{ route('semesters.show', $currentSemester->id) }}" 
                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-bold py-2 px-4 rounded text-sm">
                View Full Details
            </a>
            <a href="{{ route('semesters.calendar', $currentSemester->id) }}" 
                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white font-bold py-2 px-4 rounded text-sm">
                📅 Calendar View
            </a>
        </div>
    </div>
    @endif

    @if($schedule->isEmpty())
    <div class="bg-gray-800 rounded-lg p-8 text-center">
        <p class="text-gray-400 text-lg">No classes scheduled for {{ $isToday ? 'today' : 'this date' }}.</p>
        <a href="{{ route('timetable.index') }}" class="mt-4 inline-block text-blue-400 hover:text-blue-300">Add timetable entries</a>
    </div>
    @else
    <div class="space-y-4">
        @foreach($schedule as $class)
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0 flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        @if(isset($class['lecture_number']))
                        <span class="bg-blue-600 text-white px-2 py-1 rounded text-sm font-semibold">
                            Lec {{ $class['lecture_number'] }}
                        </span>
                        @endif
                        <h3 class="text-xl font-semibold text-white">{{ $class['subject'] }}</h3>
                    </div>
                    <div class="space-y-1 text-sm text-gray-400">
                        <p>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            {{ $class['start_time'] }} - {{ $class['end_time'] }}
                        </p>
                        @if(isset($class['instructor']) && $class['instructor'])
                        <p>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            {{ $class['instructor'] }}
                        </p>
                        @endif
                        @if(isset($class['location']) && $class['location'])
                        <p>
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            {{ $class['location'] }}
                        </p>
                        @endif
                    </div>
                    @if($class['status'])
                    <span class="inline-block mt-3 px-3 py-1 rounded-full text-sm font-medium
                        {{ $class['status'] === 'present' ? 'bg-green-900 text-green-200' : '' }}
                        {{ $class['status'] === 'absent' ? 'bg-red-900 text-red-200' : '' }}
                        {{ $class['status'] === 'cancelled' ? 'bg-gray-700 text-gray-300' : '' }}">
                        {{ ucfirst($class['status']) }}
                    </span>
                    @endif
                </div>
                <div class="flex space-x-2">
                    <button onclick="markAttendance({{ $class['subject_id'] }}, {{ $class['id'] }}, 'present')" 
                        class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline {{ $class['status'] === 'present' ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $class['status'] === 'present' ? 'disabled' : '' }}>
                        ✓ Present
                    </button>
                    <button onclick="markAttendance({{ $class['subject_id'] }}, {{ $class['id'] }}, 'absent')" 
                        class="flex-1 sm:flex-none bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline {{ $class['status'] === 'absent' ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ $class['status'] === 'absent' ? 'disabled' : '' }}>
                        ✗ Absent
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<script>
function markAttendance(subjectId, timetableEntryId, status) {
    const formData = new FormData();
    formData.append('subject_id', subjectId);
    formData.append('timetable_entry_id', timetableEntryId);
    formData.append('date', '{{ $viewDate->format('Y-m-d') }}');
    formData.append('status', status);
    formData.append('_token', '{{ csrf_token() }}');

    fetch('{{ route('attendance.mark') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(data => {
                throw new Error(data.message || 'Error marking attendance');
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Reload the page to show updated attendance
            location.reload();
        } else {
            alert(data.message || 'Error marking attendance');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(error.message || 'Error marking attendance. Please try again.');
    });
}
</script>
@endsection


