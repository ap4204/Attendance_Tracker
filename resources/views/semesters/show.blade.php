@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold">{{ $semester->name }}</h1>
            <p class="text-gray-400 mt-1">
                {{ $semester->start_date->format('F j, Y') }} - {{ $semester->end_date->format('F j, Y') }}
            </p>
        </div>
        <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-2 sm:space-x-2">
            <a href="{{ route('semesters.calendar', $semester->id) }}" 
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-center">
                📅 Calendar View
            </a>
            <a href="{{ route('semesters.edit', $semester->id) }}" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-center">
                Edit
            </a>
        </div>
    </div>

    <!-- Attendance Statistics -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6 border border-gray-700">
        <h2 class="text-xl font-bold mb-4">Overall Attendance Statistics</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 mb-4">
            <div class="text-center">
                <p class="text-2xl sm:text-3xl font-bold text-blue-400">{{ $attendanceStats['total'] }}</p>
                <p class="text-xs sm:text-sm text-gray-400">Total Classes</p>
            </div>
            <div class="text-center">
                <p class="text-2xl sm:text-3xl font-bold text-green-400">{{ $attendanceStats['present'] }}</p>
                <p class="text-xs sm:text-sm text-gray-400">Present</p>
            </div>
            <div class="text-center">
                <p class="text-2xl sm:text-3xl font-bold text-red-400">{{ $attendanceStats['absent'] }}</p>
                <p class="text-xs sm:text-sm text-gray-400">Absent</p>
            </div>
            <div class="text-center">
                <p class="text-2xl sm:text-3xl font-bold {{ $attendanceStats['percentage'] >= 75 ? 'text-green-400' : 'text-red-400' }}">
                    {{ $attendanceStats['percentage'] }}%
                </p>
                <p class="text-xs sm:text-sm text-gray-400">Attendance %</p>
            </div>
        </div>

        <!-- Subject-wise Stats -->
        @if($attendanceStats['subject_stats']->isNotEmpty())
        <div class="mt-6">
            <h3 class="text-lg font-semibold mb-3">Subject-wise Attendance</h3>
            <div class="space-y-3">
                @foreach($attendanceStats['subject_stats'] as $stat)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium">{{ $stat['subject'] }}</span>
                        <span class="{{ $stat['percentage'] >= 75 ? 'text-green-400' : 'text-red-400' }}">
                            {{ $stat['percentage'] }}%
                        </span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $stat['percentage'] >= 75 ? 'bg-green-600' : 'bg-red-600' }}" 
                            style="width: {{ min($stat['percentage'], 100) }}%"></div>
                    </div>
                    <p class="text-xs text-gray-400 mt-1">{{ $stat['present'] }}/{{ $stat['total'] }} classes</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Timetable -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6 border border-gray-700">
        <h2 class="text-xl font-bold mb-4">Timetable</h2>
        @if($timetable->isEmpty())
        <p class="text-gray-400">No timetable entries for this semester.</p>
        @else
        <div class="space-y-6">
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                @if($timetable->has($day))
                <div>
                    <h3 class="text-lg font-semibold mb-3">{{ $day }}</h3>
                    <div class="space-y-2">
                        @foreach($timetable[$day] as $entry)
                        <div class="bg-gray-700 rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                @if($entry->lecture_number)
                                <span class="bg-blue-600 text-white px-2 py-1 rounded text-xs font-semibold mr-2">
                                    Lec {{ $entry->lecture_number }}
                                </span>
                                @endif
                                <span class="font-semibold text-white">{{ $entry->subject->name }}</span>
                                <p class="text-gray-400 text-sm mt-1">
                                    {{ $entry->start_time->format('H:i') }} - {{ $entry->end_time->format('H:i') }}
                                    @if($entry->instructor) | {{ $entry->instructor }} @endif
                                    @if($entry->location) | {{ $entry->location }} @endif
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endforeach
        </div>
        @endif
    </div>

    <!-- Recent Attendances -->
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <h2 class="text-xl font-bold mb-4">Recent Attendance Records</h2>
        @if($attendancesByDate->isEmpty())
        <p class="text-gray-400">No attendance records for this semester.</p>
        @else
        <div class="space-y-4">
            @foreach($attendancesByDate->take(10) as $date => $dayAttendances)
            <div class="bg-gray-700 rounded-lg p-4">
                <h4 class="font-semibold text-white mb-2">
                    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                </h4>
                <div class="space-y-2">
                    @foreach($dayAttendances as $attendance)
                    <div class="flex items-center justify-between text-sm">
                        <div>
                            <span class="font-medium">{{ $attendance->subject->name }}</span>
                            @if($attendance->timetableEntry && $attendance->timetableEntry->lecture_number)
                            <span class="text-gray-400">(Lec {{ $attendance->timetableEntry->lecture_number }})</span>
                            @endif
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            {{ $attendance->status === 'present' ? 'bg-green-900 text-green-200' : '' }}
                            {{ $attendance->status === 'absent' ? 'bg-red-900 text-red-200' : '' }}
                            {{ $attendance->status === 'cancelled' ? 'bg-gray-600 text-gray-300' : '' }}">
                            {{ ucfirst($attendance->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection

