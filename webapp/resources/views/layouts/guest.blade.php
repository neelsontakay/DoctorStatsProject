@extends('layouts.app')

@section('body')
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
                <a href="{{ url('/') }}" class="text-xl font-bold text-primary-800">DoctorStats</a>
                <div class="flex items-center gap-3 text-sm">
                    @if (Route::currentRouteName() !== 'login')
                        <a href="{{ route('login') }}" class="font-medium text-slate-600 hover:text-primary-800">Sign in</a>
                    @endif
                    @if (Route::currentRouteName() !== 'register')
                        <a href="{{ route('register') }}" class="rounded-lg bg-primary-800 px-4 py-2 font-medium text-white hover:bg-primary-700">Register</a>
                    @endif
                </div>
            </div>
        </header>

        <main class="flex flex-1 items-center justify-center px-4 py-10">
            @yield('content')
        </main>
    </div>
@endsection
