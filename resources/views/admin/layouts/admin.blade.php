<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - CareerPath BN')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-blue-800 text-white min-h-screen flex-shrink-0">
            <div class="p-4">
                <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold flex items-center gap-2">
                    <i class="fas fa-compass"></i> CareerPath BN
                </a>
                <p class="text-sm text-blue-300 mt-1">Administration Panel</p>
            </div>

            <nav class="mt-8">
                <a href="{{ route('admin.dashboard') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-chart-pie w-5"></i> Dashboard
                </a>
                <a href="{{ route('admin.students.index') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.students.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-users w-5"></i> Students
                </a>
                <a href="{{ route('admin.careers.index') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.careers.*') ? 'bg-blue-700' : '' }}">
                    <i class="fas fa-briefcase w-5"></i> Careers
                </a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.users.index') }}" class="block py-2 px-4 hover:bg-blue-700 {{ request()->routeIs('admin.users.*') ? 'bg-blue-700' : '' }}">
                        <i class="fas fa-user-cog w-5"></i> Users
                    </a>
                @endif
            </nav>

            <div class="absolute bottom-0 w-64 p-4 border-t border-blue-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-600 flex items-center justify-center">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-sm">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-blue-300">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left text-sm text-blue-300 hover:text-white">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded">
                        <i class="fas fa-check-circle"></i> {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded">
                        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <style>
        .admin-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .admin-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .progress-bar-animated {
            transition: width 0.5s ease;
        }
    </style>
</body>
</html>