@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
        <h2 class="text-2xl font-bold mb-6">Create New Semester</h2>

        <form method="POST" action="{{ route('semesters.store') }}">
            @csrf

            <div class="mb-4">
                <label for="name" class="block text-sm font-medium mb-2">Semester Name</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required
                    placeholder="e.g., Semester 2 - 2025"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="course" class="block text-sm font-medium mb-2">Course</label>
                    <input type="text" id="course" name="course" value="{{ old('course', auth()->user()->course) }}"
                        placeholder="e.g., MCA"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="batch" class="block text-sm font-medium mb-2">Batch</label>
                    <input type="text" id="batch" name="batch" value="{{ old('batch', auth()->user()->batch) }}"
                        placeholder="e.g., 2025"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div class="mb-4">
                <label for="semester_number" class="block text-sm font-medium mb-2">Semester Number</label>
                <input type="number" id="semester_number" name="semester_number" 
                    value="{{ old('semester_number', auth()->user()->semester) }}" min="1" max="10"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium mb-2">Start Date <span class="text-red-400">*</span></label>
                    <input type="date" id="start_date" name="start_date" 
                        value="{{ old('start_date', optional(auth()->user()->semester_start_date)->format('Y-m-d') ?? '') }}" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium mb-2">End Date <span class="text-red-400">*</span></label>
                    <input type="date" id="end_date" name="end_date" value="{{ old('end_date') }}" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="block text-sm font-medium mb-2">Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="3"
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Any additional notes about this semester...">{{ old('notes') }}</textarea>
            </div>

            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                    Create Semester
                </button>
                <a href="{{ route('semesters.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-6 rounded text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

