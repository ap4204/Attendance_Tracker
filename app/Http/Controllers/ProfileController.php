<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        
        // Get user statistics
        $totalSubjects = $user->subjects()->count();
        $totalEntries = $user->timetableEntries()->count();
        $totalAttendances = $user->attendances()->count();
        
        return view('profile.edit', compact('user', 'totalSubjects', 'totalEntries', 'totalAttendances'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'division' => 'sometimes|required|in:A,B,C,D,E',
            'course' => 'nullable|string|max:255',
            'college_name' => 'nullable|string|max:255',
            'batch' => 'nullable|string|max:50',
            'semester' => 'nullable|integer|min:1|max:10',
            'semester_start_date' => 'nullable|date',
        ]);

        $updateData = [];
        
        if ($request->filled('name')) {
            $updateData['name'] = $request->name;
        }
        
        if ($request->filled('email')) {
            $updateData['email'] = $request->email;
        }
        
        if ($request->filled('division')) {
            $updateData['division'] = $request->division;
        }
        
        if ($request->filled('course')) {
            $updateData['course'] = $request->course;
        }
        
        if ($request->filled('college_name')) {
            $updateData['college_name'] = $request->college_name;
        }
        
        if ($request->filled('batch')) {
            $updateData['batch'] = $request->batch;
        }
        
        if ($request->filled('semester')) {
            $updateData['semester'] = $request->semester;
        }
        
        if ($request->filled('semester_start_date')) {
            $updateData['semester_start_date'] = $request->semester_start_date;
        }

        if (!empty($updateData)) {
            $user->update($updateData);
        }

        return redirect()->route('profile.edit')
            ->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile.edit')
            ->with('success', 'Password updated successfully.');
    }
}

