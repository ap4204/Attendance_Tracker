@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                    Calendar View
                </h1>
                <p class="text-gray-400 mt-1">{{ $semester->name }}</p>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $semester->start_date->format('M j, Y') }} - {{ $semester->end_date->format('M j, Y') }}
                </p>
            </div>
            <div class="mt-4 sm:mt-0 flex space-x-2">
                <a href="{{ route('semesters.show', $semester->id) }}" 
                    class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    ← Back to Details
                </a>
            </div>
        </div>

        <!-- Month Navigation -->
        <div class="bg-gradient-to-r from-blue-900 to-purple-900 rounded-lg p-4 border border-blue-700 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    @if($canGoPrev)
                    <a href="{{ route('semesters.calendar', ['id' => $semester->id, 'month' => $prevMonth->format('Y-m')]) }}" 
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    @else
                    <div class="bg-white bg-opacity-10 text-white p-2 rounded-lg opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </div>
                    @endif
                    
                    <div class="text-center">
                        <h2 class="text-2xl font-bold text-white">{{ $viewDate->format('F Y') }}</h2>
                        <p class="text-blue-200 text-sm">Month Overview</p>
                    </div>
                    
                    @if($canGoNext)
                    <a href="{{ route('semesters.calendar', ['id' => $semester->id, 'month' => $nextMonth->format('Y-m')]) }}" 
                        class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white p-2 rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                    @else
                    <div class="bg-white bg-opacity-10 text-white p-2 rounded-lg opacity-50 cursor-not-allowed">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </div>
                    @endif
                </div>
                
                <!-- Month Stats -->
                <div class="text-center sm:text-right">
                    <div class="text-white text-sm opacity-90">Month Attendance</div>
                    <div class="text-2xl sm:text-3xl font-bold text-white">{{ $monthStats['percentage'] }}%</div>
                    <div class="text-blue-200 text-xs">{{ $monthStats['present'] }}/{{ $monthStats['total'] }} classes</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Grid -->
    <div class="bg-gray-800 rounded-xl p-2 sm:p-6 border border-gray-700 shadow-xl">
        <div class="overflow-x-auto -mx-2 sm:mx-0 px-2 sm:px-0">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2 sm:mb-4 min-w-[500px] sm:min-w-0">
            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
            <div class="text-center font-bold text-gray-300 py-2 text-sm">
                {{ substr($day, 0, 3) }}
            </div>
            @endforeach
        </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-1 sm:gap-2 min-w-[500px] sm:min-w-0">
            @php
                $weekStart = \Carbon\Carbon::parse(key($calendarData))->startOfWeek();
                $currentDate = $weekStart->copy();
                $endDate = \Carbon\Carbon::parse(array_key_last($calendarData));
            @endphp

            @while($currentDate <= $endDate)
                @php
                    $dateKey = $currentDate->format('Y-m-d');
                    $dayData = $calendarData[$dateKey] ?? null;
                    $isToday = $currentDate->isToday();
                @endphp
                
                <a href="{{ $dayData && $dayData['is_in_semester'] && ($isToday || $currentDate->isPast()) ? route('dashboard.date', ['date' => $dateKey]) : '#' }}" 
                    class="block min-h-24 sm:min-h-32 border rounded-lg p-1.5 sm:p-2 transition-all duration-200 hover:shadow-lg hover:scale-105
                    @if($dayData && $dayData['is_in_semester'])
                        cursor-pointer
                    @else
                        cursor-default
                    @endif
                    @if($dayData && $dayData['is_in_month'])
                        @if($dayData['is_in_semester'])
                            bg-gray-800 border-gray-600 hover:bg-gray-700
                        @else
                            bg-gray-900 border-gray-700 opacity-60
                        @endif
                    @else
                        bg-gray-900 border-gray-800 opacity-40
                    @endif
                    @if($isToday)
                        ring-2 ring-blue-500 ring-opacity-50
                    @endif
                ">
                    <!-- Date Number -->
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-bold 
                            @if($isToday) text-blue-400 @elseif($dayData && $dayData['is_in_month']) text-gray-200 @else text-gray-500 @endif">
                            {{ $currentDate->format('j') }}
                        </span>
                        @if($dayData && $dayData['attendance_percentage'] > 0)
                        <span class="text-xs px-1.5 py-0.5 rounded
                            @if($dayData['attendance_percentage'] >= 75) bg-green-900 text-green-200
                            @elseif($dayData['attendance_percentage'] >= 50) bg-yellow-900 text-yellow-200
                            @else bg-red-900 text-red-200 @endif">
                            {{ $dayData['attendance_percentage'] }}%
                        </span>
                        @endif
                    </div>
                    
                    @if($dayData && $dayData['is_in_semester'])
                        <!-- Classes -->
                        @if($dayData['total_classes'] > 0)
                        <div class="space-y-1 mb-2">
                            @foreach($dayData['classes']->take(2) as $class)
                            @php
                                $attendance = $dayData['attendances']->firstWhere('timetable_entry_id', $class->id);
                                $status = $attendance ? $attendance->status : null;
                            @endphp
                            <div class="group relative">
                                <div class="bg-blue-600 hover:bg-blue-700 text-white text-xs p-1.5 rounded cursor-pointer transition-all
                                    @if($status === 'present') ring-2 ring-green-400
                                    @elseif($status === 'absent') ring-2 ring-red-400
                                    @endif"
                                    title="{{ $class->subject->name }} ({{ $class->start_time->format('H:i') }}-{{ $class->end_time->format('H:i') }})">
                                    <div class="font-semibold truncate">{{ $class->subject->name }}</div>
                                    <div class="text-xs opacity-90">{{ $class->start_time->format('H:i') }}</div>
                                    @if($status)
                                    <div class="text-xs mt-0.5
                                        @if($status === 'present') text-green-200
                                        @elseif($status === 'absent') text-red-200 @endif">
                                        {{ $status === 'present' ? '✓' : '✗' }}
                                    </div>
                                    @endif
                                </div>
                                <!-- Tooltip -->
                                <div class="hidden group-hover:block absolute z-10 bg-gray-900 text-white text-xs rounded-lg p-2 shadow-xl border border-gray-700 w-48 bottom-full left-0 mb-2">
                                    <div class="font-semibold mb-1">{{ $class->subject->name }}</div>
                                    <div class="text-gray-300">{{ $class->start_time->format('H:i') }} - {{ $class->end_time->format('H:i') }}</div>
                                    @if($class->instructor)
                                    <div class="text-gray-400 mt-1">👤 {{ $class->instructor }}</div>
                                    @endif
                                    @if($class->location)
                                    <div class="text-gray-400">📍 {{ $class->location }}</div>
                                    @endif
                                    @if($class->lecture_number)
                                    <div class="text-gray-400">📚 Lecture {{ $class->lecture_number }}</div>
                                    @endif
                                    @if($status)
                                    <div class="mt-2 pt-2 border-t border-gray-700">
                                        <span class="px-2 py-1 rounded text-xs
                                            @if($status === 'present') bg-green-900 text-green-200
                                            @else bg-red-900 text-red-200 @endif">
                                            {{ ucfirst($status) }}
                                        </span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            @if($dayData['total_classes'] > 2)
                            <div class="text-xs text-gray-400 text-center pt-1">
                                +{{ $dayData['total_classes'] - 2 }} more
                            </div>
                            @endif
                        </div>
                        @endif
                        
                        <!-- Attendance Summary -->
                        @if($dayData['marked_attendance'] > 0)
                        <div class="mt-2 pt-2 border-t border-gray-700">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-green-400">✓ {{ $dayData['present_count'] }}</span>
                                <span class="text-red-400">✗ {{ $dayData['absent_count'] }}</span>
                            </div>
                        </div>
                        @endif
                    @endif
                </a>
                
                @php $currentDate->addDay(); @endphp
            @endwhile
            </div>
        </div>
    </div>

    <!-- Statistics and Legend -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6 mt-4 sm:mt-6">
        <!-- Month Statistics -->
        <div class="bg-gradient-to-br from-blue-900 to-purple-900 rounded-xl p-6 border border-blue-700">
            <h3 class="text-xl font-bold text-white mb-4">Month Statistics</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-blue-200">Total Classes</span>
                    <span class="text-2xl font-bold text-white">{{ $monthStats['total'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-green-200">Present</span>
                    <span class="text-2xl font-bold text-green-300">{{ $monthStats['present'] }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-red-200">Absent</span>
                    <span class="text-2xl font-bold text-red-300">{{ $monthStats['absent'] }}</span>
                </div>
                <div class="mt-4 pt-4 border-t border-blue-700">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-white font-semibold">Attendance Rate</span>
                        <span class="text-3xl font-bold text-white">{{ $monthStats['percentage'] }}%</span>
                    </div>
                    <div class="w-full bg-blue-900 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all duration-500
                            @if($monthStats['percentage'] >= 75) bg-green-500
                            @elseif($monthStats['percentage'] >= 50) bg-yellow-500
                            @else bg-red-500 @endif" 
                            style="width: {{ min($monthStats['percentage'], 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
            <h3 class="text-xl font-bold text-white mb-4">Legend</h3>
            <div class="space-y-3 text-sm">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center text-white text-xs font-bold">C</div>
                    <span class="text-gray-300">Scheduled Class</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-blue-600 rounded ring-2 ring-green-400"></div>
                    <span class="text-gray-300">Present (✓)</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-blue-600 rounded ring-2 ring-red-400"></div>
                    <span class="text-gray-300">Absent (✗)</span>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 bg-gray-800 border-2 border-blue-500 rounded"></div>
                    <span class="text-gray-300">Today</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded bg-green-900 text-green-200">75%+</span>
                    <span class="text-gray-300">Good Attendance</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded bg-yellow-900 text-yellow-200">50-74%</span>
                    <span class="text-gray-300">Average Attendance</span>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs px-2 py-1 rounded bg-red-900 text-red-200">&lt;50%</span>
                    <span class="text-gray-300">Low Attendance</span>
                </div>
            </div>
            <div class="mt-4 pt-4 border-t border-gray-700">
                <p class="text-xs text-gray-400">
                    💡 <strong>Tip:</strong> Hover over classes to see detailed information. Click on a date to mark attendance for past dates.
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .calendar-day {
        animation: fadeIn 0.3s ease-out;
    }
</style>
@endsection
