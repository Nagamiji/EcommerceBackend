<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - {{ config('app.name', 'E-Commerce') }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> <!-- Tailwind CSS -->
    <script src="https://kit.fontawesome.com/YOUR_FA_KIT.js" crossorigin="anonymous"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-800 text-gray-200 flex-shrink-0">
        <div class="p-4 text-xl font-bold border-b border-gray-700">
            <a href="{{ route('admin.dashboard') }}">{{ config('app.name', 'E-Commerce') }}</a>
        </div>
        <nav class="mt-4">
            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
            </a>
            <a href="{{ route('admin.products') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.products') ? 'bg-gray-700' : '' }}">
                <i class="fas fa-box mr-2"></i> Products
            </a>
            <a href="{{ route('admin.categories') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.categories') ? 'bg-gray-700' : '' }}">
                <i class="fas fa-tags mr-2"></i> Categories
            </a>
            <a href="{{ route('admin.orders') }}" class="block px-4 py-2 hover:bg-gray-700 {{ request()->routeIs('admin.orders') ? 'bg-gray-700' : '' }}">
                <i class="fas fa-shopping-cart mr-2"></i> Orders
            </a>
            <button onclick="logout()" class="w-full text-left px-4 py-2 hover:bg-red-600 mt-4">
                <i class="fas fa-sign-out-alt mr-2"></i> Logout
            </button>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col">
        <!-- Top Navbar -->
        <header class="bg-white shadow p-4 flex justify-between items-center">
            <h1 class="text-2xl font-semibold">@yield('title')</h1>
        </header>

        <!-- Page Content -->
        <main class="p-6 flex-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-gray-200 p-4 text-center text-sm text-gray-600">
            &copy; {{ date('Y') }} {{ config('app.name', 'E-Commerce') }}. All rights reserved.
        </footer>
    </div>

    <script>
        function logout() {
            localStorage.removeItem('token');
            window.location.href = '{{ route('login') }}';
        }
        if (!localStorage.getItem('token')) {
            window.location.href = '{{ route('login') }}';
        }
    </script>
    @yield('scripts')
</body>
</html>
