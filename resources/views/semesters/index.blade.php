@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold mb-4 sm:mb-0">My Semesters</h1>
        <a href="{{ route('semesters.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            + Create New Semester
        </a>
    </div>

    @if($semesters->isEmpty())
    <div class="bg-gray-800 rounded-lg p-8 text-center border border-gray-700">
        <p class="text-gray-400 text-lg mb-4">No semesters yet.</p>
        <p class="text-gray-500 text-sm mb-4">Semesters are automatically created when you upload timetables, or you can create them manually.</p>
        <a href="{{ route('semesters.create') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            Create Your First Semester
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($semesters as $semester)
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-blue-500 transition">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <h3 class="text-xl font-semibold text-white mb-1">{{ $semester->name }}</h3>
                    <p class="text-gray-400 text-sm">
                        {{ $semester->start_date->format('M j, Y') }} - {{ $semester->end_date->format('M j, Y') }}
                    </p>
                    @if($semester->course)
                    <p class="text-gray-500 text-xs mt-1">{{ $semester->course }} | Batch: {{ $semester->batch }}</p>
                    @endif
                </div>
                @if($semester->start_date <= now() && $semester->end_date >= now())
                <span class="bg-green-600 text-white px-2 py-1 rounded text-xs font-semibold">Current</span>
                @endif
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-400">{{ $semester->timetable_entries_count }}</p>
                    <p class="text-xs text-gray-400">Timetable Entries</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-yellow-400">{{ $semester->attendances_count }}</p>
                    <p class="text-xs text-gray-400">Attendance Records</p>
                </div>
            </div>

            <div class="flex space-x-2">
                <a href="{{ route('semesters.show', $semester->id) }}" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                    View Details
                </a>
                <a href="{{ route('semesters.calendar', $semester->id) }}" 
                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded text-sm text-center">
                    Calendar
                </a>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

