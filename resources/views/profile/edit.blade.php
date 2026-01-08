@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold mb-6">My Profile</h1>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Information Card -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 class="text-xl font-bold mb-4">User Information</h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Name</label>
                    <p class="text-white text-lg">{{ $user->name }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Email</label>
                    <p class="text-white text-lg">{{ $user->email }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">College/University</label>
                    <p class="text-white text-lg">{{ $user->college_name ?: 'Not set' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Course</label>
                    <p class="text-white text-lg">{{ $user->course ?: 'Not set' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Batch</label>
                    <p class="text-white text-lg">{{ $user->batch ?: 'Not set' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Semester</label>
                    <p class="text-white text-lg">{{ $user->semester ? 'Semester ' . $user->semester : 'Not set' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Semester Start Date</label>
                    <p class="text-white text-lg">{{ optional($user->semester_start_date)->format('F j, Y') ?? 'Not set' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Division</label>
                    <p class="text-white text-lg">{{ $user->division ? 'Division ' . $user->division : 'Not set' }}</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-1">Member Since</label>
                    <p class="text-white text-lg">{{ $user->created_at->format('F j, Y') }}</p>
                </div>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-700">
                <h3 class="text-sm font-medium text-gray-400 mb-3">Statistics</h3>
                <div class="grid grid-cols-3 gap-2 sm:gap-4">
                    <div class="text-center">
                        <p class="text-xl sm:text-2xl font-bold text-blue-400">{{ $totalSubjects }}</p>
                        <p class="text-xs text-gray-400">Subjects</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xl sm:text-2xl font-bold text-green-400">{{ $totalEntries }}</p>
                        <p class="text-xs text-gray-400">Timetable Entries</p>
                    </div>
                    <div class="text-center">
                        <p class="text-xl sm:text-2xl font-bold text-yellow-400">{{ $totalAttendances }}</p>
                        <p class="text-xs text-gray-400">Attendance Records</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Profile Form -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <h2 class="text-xl font-bold mb-4">Update Profile</h2>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium mb-2">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium mb-2">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('email')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="college_name" class="block text-sm font-medium mb-2">College/University Name</label>
                    <input type="text" id="college_name" name="college_name" value="{{ old('college_name', $user->college_name) }}"
                        placeholder="Enter your college or university name"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('college_name')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="course" class="block text-sm font-medium mb-2">Course</label>
                    <input type="text" id="course" name="course" value="{{ old('course', $user->course) }}"
                        placeholder="e.g., MCA, B.Tech, B.Sc"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('course')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="batch" class="block text-sm font-medium mb-2">Batch</label>
                        <input type="text" id="batch" name="batch" value="{{ old('batch', $user->batch) }}"
                            placeholder="e.g., 2025"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('batch')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="semester" class="block text-sm font-medium mb-2">Semester</label>
                        <input type="number" id="semester" name="semester" value="{{ old('semester', $user->semester) }}"
                            placeholder="e.g., 2" min="1" max="10"
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('semester')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="semester_start_date" class="block text-sm font-medium mb-2">Semester Start Date</label>
                    <input type="date" id="semester_start_date" name="semester_start_date" 
                        value="{{ old('semester_start_date', optional($user->semester_start_date)->format('Y-m-d') ?? '') }}"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Set this once. It will be used for all timetable uploads.</p>
                    @error('semester_start_date')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="division" class="block text-sm font-medium mb-2">Division <span class="text-red-400">*</span></label>
                    <select id="division" name="division" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Division</option>
                        <option value="A" {{ $user->division === 'A' ? 'selected' : '' }}>Division A</option>
                        <option value="B" {{ $user->division === 'B' ? 'selected' : '' }}>Division B</option>
                        <option value="C" {{ $user->division === 'C' ? 'selected' : '' }}>Division C</option>
                        <option value="D" {{ $user->division === 'D' ? 'selected' : '' }}>Division D</option>
                        <option value="E" {{ $user->division === 'E' ? 'selected' : '' }}>Division E</option>
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Required for timetable extraction</p>
                    @error('division')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update Profile
                </button>
            </form>
        </div>

        <!-- Change Password Form -->
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700 lg:col-span-2">
            <h2 class="text-xl font-bold mb-4">Change Password</h2>

            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium mb-2">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('current_password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium mb-2">New Password</label>
                        <input type="password" id="password" name="password" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('password')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium mb-2">Confirm New Password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                            class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <button type="submit" class="mt-4 bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Change Password
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
