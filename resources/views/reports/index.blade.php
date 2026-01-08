@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold mb-2">Attendance Reports</h1>
        <p class="text-gray-400">Track your attendance progress for each subject</p>
    </div>

    @if(isset($semesters) && $semesters->isNotEmpty())
    <div class="bg-gray-800 rounded-lg p-4 mb-6 border border-gray-700">
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-4">
            <label class="text-sm font-medium">Filter by Semester:</label>
            <select name="semester_id" onchange="this.form.submit()" 
                class="flex-1 px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                <option value="">All Semesters</option>
                @foreach($semesters as $sem)
                    <option value="{{ $sem->id }}" {{ $selectedSemesterId == $sem->id ? 'selected' : '' }}>
                        {{ $sem->name }} ({{ $sem->start_date->format('M Y') }} - {{ $sem->end_date->format('M Y') }})
                    </option>
                @endforeach
            </select>
            @if($currentSemester && !$selectedSemesterId)
            <span class="text-sm text-gray-400">Showing: Current Semester</span>
            @endif
        </form>
    </div>
    @endif

    <!-- PDF Download Form -->
    <div class="bg-gray-800 rounded-lg p-6 mb-6 border border-gray-700">
        <h2 class="text-xl font-semibold mb-4">Download Monthly Report</h2>
        <form method="POST" action="{{ route('reports.download') }}" class="flex flex-col sm:flex-row gap-4">
            @csrf
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Month</label>
                <input type="month" name="month" value="{{ date('Y-m') }}" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
            </div>
            <div class="flex-1">
                <label class="block text-sm font-medium mb-2">Subject (optional)</label>
                <select name="subject_id"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white">
                    <option value="">All Subjects</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject['id'] }}">{{ $subject['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" 
                    class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    📄 Download PDF
                </button>
            </div>
        </form>
    </div>

    @if(empty($subjects))
    <div class="bg-gray-800 rounded-lg p-8 text-center">
        <p class="text-gray-400 text-lg">No subjects found. Add subjects to track attendance.</p>
    </div>
    @else
    <div class="space-y-6">
        @foreach($subjects as $subject)
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
                <div>
                    <h3 class="text-xl font-semibold text-white">{{ $subject['name'] }}</h3>
                    <p class="text-gray-400 text-sm mt-1">Target: {{ $subject['target_percentage'] }}%</p>
                </div>
                <div class="mt-2 sm:mt-0 text-right">
                    <p class="text-2xl font-bold {{ $subject['status'] === 'good' ? 'text-green-400' : 'text-red-400' }}">
                        {{ $subject['percentage'] }}%
                    </p>
                    <p class="text-gray-400 text-sm">
                        {{ $subject['present'] }}/{{ $subject['total'] }} classes
                    </p>
                </div>
            </div>
            
            <div class="mb-2">
                <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-400">Progress</span>
                    <span class="text-gray-400">{{ $subject['percentage'] }}%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-4">
                    <div class="h-4 rounded-full {{ $subject['status'] === 'good' ? 'bg-green-600' : 'bg-red-600' }}" 
                        style="width: {{ min($subject['percentage'], 100) }}%"></div>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-2 sm:gap-4 text-center">
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-green-400">{{ $subject['present'] }}</p>
                    <p class="text-gray-400 text-xs sm:text-sm">Present</p>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-red-400">{{ $subject['absent'] }}</p>
                    <p class="text-gray-400 text-xs sm:text-sm">Absent</p>
                </div>
                <div>
                    <p class="text-xl sm:text-2xl font-bold text-gray-400">{{ $subject['total'] }}</p>
                    <p class="text-gray-400 text-xs sm:text-sm">Total</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection

