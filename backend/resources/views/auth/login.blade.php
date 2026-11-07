@extends('layouts.app')

@section('content')
<div class="min-h-[75vh] flex items-center justify-center">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 text-3xl mb-4 shadow-inner">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Admin Portal Login</h1>
            <p class="text-sm text-slate-400 mt-1">Authenticate with MongoDB backend credentials</p>
        </div>

        <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800 rounded-2xl p-8 shadow-2xl shadow-black/50">
            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-envelope"></i>
                        </span>
                        <input type="email" id="email" name="email" value="{{ old('email', 'admin@example.com') }}" required autofocus
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-500 pointer-events-none">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" id="password" name="password" required
                            class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-slate-950 border border-slate-700/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm transition"
                            placeholder="Enter password (default: Admin123!)">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-rose-400 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 text-slate-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-indigo-600 focus:ring-indigo-500">
                        <span>Keep me logged in</span>
                    </label>
                    <a href="/api/documentation" class="text-indigo-400 hover:text-indigo-300 transition font-medium">API Documentation &rarr;</a>
                </div>

                <button type="submit"
                    class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-500 hover:to-indigo-600 text-white font-semibold text-sm shadow-lg shadow-indigo-600/30 focus:outline-none focus:ring-2 focus:ring-indigo-500 transition duration-150">
                    Sign In to Dashboard
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800/80 text-center">
                <div class="p-3 rounded-lg bg-slate-950/80 border border-slate-800 text-left text-xs space-y-1 text-slate-400">
                    <div class="flex items-center space-x-1.5 text-slate-300 font-semibold mb-1">
                        <i class="fa-solid fa-key text-amber-400"></i>
                        <span>Default Demo Credentials:</span>
                    </div>
                    <p><span class="text-slate-500">Email:</span> <code class="text-indigo-300 font-mono">admin@example.com</code></p>
                    <p><span class="text-slate-500">Password:</span> <code class="text-indigo-300 font-mono">Admin123!</code></p>
                    <p><span class="text-slate-500">Role:</span> <code class="text-emerald-300 font-mono">admin</code> (MongoDB record)</p>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection

