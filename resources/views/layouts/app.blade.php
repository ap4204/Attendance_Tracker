<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Attendance Tracker') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen">
    <nav class="bg-gray-800 border-b border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" class="text-lg sm:text-xl font-bold text-white">📚 Attendance Tracker</a>
                    </div>
                    @auth
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('dashboard') ? 'text-blue-400 border-b-2 border-blue-400' : 'text-gray-300 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Dashboard
                        </a>
                        <a href="{{ route('timetable.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('timetable.*') ? 'text-blue-400 border-b-2 border-blue-400' : 'text-gray-300 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Timetable
                        </a>
                        <a href="{{ route('subjects.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('subjects.*') ? 'text-blue-400 border-b-2 border-blue-400' : 'text-gray-300 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            Subjects
                        </a>
                        <a href="{{ route('semesters.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('semesters.*') ? 'text-blue-400 border-b-2 border-blue-400' : 'text-gray-300 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Semesters
                        </a>
                        <a href="{{ route('reports.index') }}" class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('reports.*') ? 'text-blue-400 border-b-2 border-blue-400' : 'text-gray-300 hover:text-white' }}">
                            <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Reports
                        </a>
                    </div>
                    @endauth
                </div>
                @auth
                <div class="hidden sm:flex items-center space-x-4">
                    <a href="{{ route('profile.edit') }}" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Profile
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Logout
                        </button>
                    </form>
                </div>
                <!-- Mobile menu button -->
                <div class="sm:hidden flex items-center">
                    <button type="button" id="mobile-menu-button" class="text-gray-300 hover:text-white p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                        <svg id="menu-icon" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="close-icon" class="h-6 w-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                @endauth
            </div>
        </div>

        <!-- Mobile menu -->
        @auth
        <div id="mobile-menu" class="hidden sm:hidden border-t border-gray-700">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('dashboard') ? 'text-blue-400 bg-gray-700' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">Dashboard</a>
                <a href="{{ route('timetable.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('timetable.*') ? 'text-blue-400 bg-gray-700' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">Timetable</a>
                <a href="{{ route('subjects.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('subjects.*') ? 'text-blue-400 bg-gray-700' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">Subjects</a>
                <a href="{{ route('semesters.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('semesters.*') ? 'text-blue-400 bg-gray-700' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">Semesters</a>
                <a href="{{ route('reports.index') }}" class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('reports.*') ? 'text-blue-400 bg-gray-700' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">Reports</a>
                <a href="{{ route('profile.edit') }}" class="block pl-3 pr-4 py-2 text-base font-medium {{ request()->routeIs('profile.*') ? 'text-blue-400 bg-gray-700' : 'text-gray-300 hover:text-white hover:bg-gray-700' }}">Profile</a>
                <form method="POST" action="{{ route('logout') }}" class="px-3 py-2">
                    @csrf
                    <button type="submit" class="w-full text-left text-gray-300 hover:text-white text-base font-medium">
                        Logout
                    </button>
                </form>
            </div>
        </div>
        @endauth
    </nav>

    <main class="py-6">
        @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
            <div class="bg-green-800 border border-green-700 text-green-100 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
            <div class="bg-red-800 border border-red-700 text-red-100 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        </div>
        @endif

        @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
            <div class="bg-red-800 border border-red-700 text-red-100 px-4 py-3 rounded relative" role="alert">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        @yield('content')
    </main>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button')?.addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            const menuIcon = document.getElementById('menu-icon');
            const closeIcon = document.getElementById('close-icon');
            
            menu.classList.toggle('hidden');
            menuIcon.classList.toggle('hidden');
            closeIcon.classList.toggle('hidden');
        });

        // Close mobile menu when clicking outside
        document.addEventListener('click', function(event) {
            const menu = document.getElementById('mobile-menu');
            const menuButton = document.getElementById('mobile-menu-button');
            
            if (menu && !menu.contains(event.target) && !menuButton?.contains(event.target)) {
                if (!menu.classList.contains('hidden')) {
                    menu.classList.add('hidden');
                    document.getElementById('menu-icon')?.classList.remove('hidden');
                    document.getElementById('close-icon')?.classList.add('hidden');
                }
            }
        });
    </script>
</body>
</html>

