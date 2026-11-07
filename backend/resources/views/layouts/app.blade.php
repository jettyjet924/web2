<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Admin Dashboard - Laravel MongoDB' }}</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#eef2ff',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                        },
                        mongo: {
                            500: '#00ED64',
                            600: '#00684A',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body class="h-full flex flex-col font-sans antialiased bg-slate-950 text-slate-200">

    <!-- Top Navigation -->
    <header class="sticky top-0 z-50 bg-slate-900/80 backdrop-blur-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-emerald-500 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-indigo-500/20 font-black text-xl">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div>
                        <span class="text-lg font-bold text-white tracking-tight">Mongo<span class="text-indigo-400">Laravel</span></span>
                        <span class="hidden md:inline-block ml-2 px-2 py-0.5 text-xs rounded-full bg-emerald-950 text-emerald-400 border border-emerald-800/50">MongoDB v7.0</span>
                    </div>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/api/documentation" target="_blank" class="inline-flex items-center space-x-2 text-xs font-semibold px-3 py-1.5 rounded-lg bg-indigo-950/80 text-indigo-300 border border-indigo-700/50 hover:bg-indigo-900/50 transition">
                        <i class="fa-solid fa-book-open"></i>
                        <span>Swagger Docs</span>
                    </a>

                    @auth
                        <div class="flex items-center space-x-3 border-l border-slate-800 pl-4">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-indigo-400 uppercase tracking-wider font-semibold">{{ Auth::user()->role }}</p>
                            </div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="p-2 rounded-lg bg-slate-800 hover:bg-rose-950/50 hover:text-rose-400 border border-slate-700/60 text-slate-300 transition" title="Logout">
                                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                                </button>
                            </form>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Notification Alerts -->
        @if(session('status'))
            <div class="mb-6 p-4 rounded-xl bg-emerald-950/50 border border-emerald-800/60 text-emerald-300 flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-emerald-400"></i>
                <span class="text-sm">{{ session('status') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-xl bg-rose-950/50 border border-rose-800/60 text-rose-300 flex items-center space-x-3">
                <i class="fa-solid fa-triangle-exclamation text-rose-400"></i>
                <span class="text-sm">{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-900/40 py-6 text-center text-xs text-slate-500">
        <p>Laravel + MongoDB High-Performance MVC Backend &bull; OpenAPI 3.0 Standard &bull; Role-Based Security</p>
    </footer>

</body>
</html>

