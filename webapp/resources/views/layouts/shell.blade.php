@extends('layouts.app')

@section('body')
    <div class="min-h-screen bg-background" x-data="appShell" x-init="init()">
        <a href="#main-content" class="skip-link">Skip to main content</a>

        @include('partials.shell-header', ['activeNav' => $activeNav ?? 'Dashboard'])

        <main id="main-content" class="mx-auto max-w-7xl px-4 py-8">
            @yield('content')
        </main>
    </div>
@endsection
