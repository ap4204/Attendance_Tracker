@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto mt-8">
    <div class="bg-gray-800 rounded-lg shadow-lg p-8">
        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-4">
                <label for="email" class="block text-sm font-medium mb-2">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium mb-2">Password</label>
                <input id="password" type="password" name="password" required
                    class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4 flex items-center">
                <input id="remember" type="checkbox" name="remember" class="rounded border-gray-600 text-blue-600 focus:ring-blue-500">
                <label for="remember" class="ml-2 text-sm">Remember me</label>
            </div>

            <div class="mb-4">
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Login
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-blue-400 hover:text-blue-300">Forgot password?</a>
            </div>

            <div class="mt-4 text-center">
                <a href="{{ route('register') }}" class="text-sm text-gray-400 hover:text-gray-300">Don't have an account? Register</a>
            </div>
        </form>
    </div>
</div>
@endsection

