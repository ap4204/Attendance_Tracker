@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4">
            <h1 class="text-3xl font-bold mb-4 sm:mb-0">Subjects</h1>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" 
                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                + Add Subject
            </button>
        </div>
        
        @if($semesters->isNotEmpty())
        <div class="bg-gray-800 rounded-lg p-4 border border-gray-700">
            <form method="GET" action="{{ route('subjects.index') }}" class="flex flex-col sm:flex-row sm:items-center gap-4">
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
    </div>

    <!-- Add Modal -->
    <div id="addModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
        <div class="bg-gray-800 rounded-lg p-4 sm:p-6 max-w-md w-full mx-2 sm:mx-4 my-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-2xl font-bold mb-4">Add Subject</h2>
            <form method="POST" action="{{ route('subjects.store') }}">
                @csrf
                @if($semesters->isNotEmpty())
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Semester</label>
                    <select name="semester_id" 
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Current Semester (Auto)</option>
                        @foreach($semesters as $sem)
                            <option value="{{ $sem->id }}" {{ $currentSemester && $currentSemester->id == $sem->id ? 'selected' : '' }}>
                                {{ $sem->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Leave empty to use current semester</p>
                </div>
                @endif
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Subject Name</label>
                    <input type="text" name="name" required
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-2">Target Percentage</label>
                    <input type="number" name="target_percentage" value="75" min="0" max="100" step="0.01"
                        class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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

    @php
        $nullSemesterSubjects = $subjects->filter(function($subject) {
            return $subject->semester_id === null;
        });
    @endphp

    @if($nullSemesterSubjects->isNotEmpty() && ($selectedSemesterId || $currentSemester))
    <div class="bg-yellow-900 border border-yellow-700 rounded-lg p-4 mb-6">
        <div class="flex items-start justify-between">
            <div class="flex-1">
                <p class="text-yellow-200 font-semibold mb-1">⚠️ Unassigned Subjects</p>
                <p class="text-yellow-300 text-sm">
                    You have {{ $nullSemesterSubjects->count() }} subject(s) without a semester assignment. 
                    They are shown here but should be assigned to a semester for better organization.
                </p>
            </div>
            @if($semesters->isNotEmpty())
            <form method="POST" action="{{ route('subjects.assign-semester') }}" class="ml-4">
                @csrf
                <input type="hidden" name="semester_id" value="{{ $selectedSemesterId ?: ($currentSemester ? $currentSemester->id : '') }}">
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Assign to {{ $selectedSemesterId ? $semesters->find($selectedSemesterId)->name : ($currentSemester ? $currentSemester->name : 'Semester') }}
                </button>
            </form>
            @endif
        </div>
    </div>
    @endif

    @if($subjects->isEmpty())
    <div class="bg-gray-800 rounded-lg p-8 text-center">
        <p class="text-gray-400 text-lg">No subjects yet. Add your first subject to get started.</p>
    </div>
    @else
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($subjects as $subject)
        <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
            <div class="flex items-start justify-between mb-2">
                <h3 class="text-xl font-semibold">{{ $subject->name }}</h3>
                @if($subject->semester)
                <span class="bg-purple-600 text-white px-2 py-1 rounded text-xs">
                    {{ $subject->semester->name }}
                </span>
                @else
                <span class="bg-yellow-600 text-white px-2 py-1 rounded text-xs" title="Not assigned to any semester">
                    Unassigned
                </span>
                @endif
            </div>
            <p class="text-gray-400 text-sm mb-4">Target: {{ $subject->target_percentage }}%</p>
                <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                <button onclick="editSubject({{ $subject->id }}, '{{ $subject->name }}', {{ $subject->target_percentage }})" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                    Edit
                </button>
                <form method="POST" action="{{ route('subjects.destroy', $subject->id) }}" class="flex-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" onclick="return confirm('Are you sure?')" 
                        class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

<!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-gray-800 rounded-lg p-4 sm:p-6 max-w-md w-full mx-2 sm:mx-4 my-4 max-h-[90vh] overflow-y-auto">
        <h2 class="text-2xl font-bold mb-4">Edit Subject</h2>
        <form id="editForm" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Subject Name</label>
                <input type="text" name="name" id="edit_name" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Target Percentage</label>
                <input type="number" name="target_percentage" id="edit_target" min="0" max="100" step="0.01" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex flex-col sm:flex-row gap-2 sm:space-x-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Update
                </button>
                <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')" 
                    class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function editSubject(id, name, target) {
    document.getElementById('editForm').action = '/subjects/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_target').value = target;
    document.getElementById('editModal').classList.remove('hidden');
}
</script>
@endsection

