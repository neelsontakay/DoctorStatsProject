@extends('layouts.app')

@section('body')
    <div class="min-h-screen bg-muted flex flex-col">
        <a href="#auth-content" class="skip-link">Skip to main content</a>
        <header class="p-6">
            <a href="{{ route('home') }}">
                <x-logo />
            </a>
        </header>
        <main id="auth-content" class="flex-1 flex items-center justify-center px-4 pb-12">
            @yield('content')
        </main>
    </div>
@endsection
