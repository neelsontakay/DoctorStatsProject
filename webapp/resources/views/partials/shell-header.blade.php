@php
    $navItems = [
        ['label' => 'Dashboard', 'route' => 'dashboard'],
        ['label' => 'Analyses', 'route' => 'analyses.index'],
        ['label' => 'Reports', 'route' => 'reports.index'],
        ['label' => 'Org', 'route' => 'organization'],
    ];
@endphp

<header class="border-b border-border bg-card sticky top-0 z-50">
    <div class="mx-auto max-w-7xl px-4 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-6">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="size-10 text-base rounded-lg bg-primary flex items-center justify-center text-primary-foreground font-bold" aria-hidden="true">DS</div>
                    <div>
                        <div class="font-semibold text-foreground leading-tight">DoctorStats</div>
                        <div class="text-xs text-muted-foreground leading-tight" x-text="orgName"></div>
                    </div>
                </a>
                <nav class="hidden md:flex items-center gap-1" aria-label="Main navigation">
                    @foreach ($navItems as $item)
                        <a href="{{ route($item['route']) }}"
                            class="{{ ($activeNav ?? '') === $item['label'] ? 'nav-link-active' : 'nav-link' }}"
                            @if (($activeNav ?? '') === $item['label']) aria-current="page" @endif>
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <div class="relative">
                    <button type="button" class="btn btn-ghost gap-2 pl-2 pr-1" @click="menuOpen = !menuOpen"
                        :aria-expanded="menuOpen" aria-haspopup="menu" aria-label="User menu">
                        <div class="size-8 rounded-full bg-primary-100 text-primary flex items-center justify-center text-xs font-semibold"
                            x-text="(profile?.name ?? 'U').split(' ').map(p => p[0]).join('').slice(0,2)"></div>
                        <span class="hidden sm:inline text-sm font-medium" x-text="profile?.name ?? 'Account'"></span>
                        <svg class="w-4 h-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <template x-if="menuOpen">
                        <div>
                            <div class="fixed inset-0 z-40" @click="menuOpen = false" aria-hidden="true"></div>
                            <div role="menu" class="absolute right-0 mt-2 w-48 rounded-lg border border-border bg-card shadow-lg z-50 py-1">
                                <a href="{{ route('profile') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted">Profile</a>
                                <a href="{{ route('organization') }}" role="menuitem" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted">Org settings</a>
                                <hr class="my-1 border-border">
                                <button type="button" role="menuitem" @click="logout" class="flex w-full items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-muted">Sign out</button>
                            </div>
                        </div>
                    </template>
                </div>

                <button type="button" class="btn btn-ghost btn-icon md:hidden" @click="mobileOpen = true"
                    aria-label="Open menu" :aria-expanded="mobileOpen">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>
</header>

<template x-if="mobileOpen">
    <div class="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Navigation menu">
        <div class="fixed inset-0 bg-black/50" @click="mobileOpen = false" aria-hidden="true"></div>
        <div class="fixed inset-y-0 right-0 w-72 bg-card border-l border-border shadow-xl flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-border">
                <x-logo size="sm" />
                <button type="button" class="btn btn-ghost btn-icon" @click="mobileOpen = false" aria-label="Close menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 p-4 space-y-1" aria-label="Mobile navigation">
                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                        class="block w-full text-left px-4 py-3 rounded-lg text-sm font-medium min-h-[44px] {{ ($activeNav ?? '') === $item['label'] ? 'bg-primary-50 text-primary' : 'text-foreground hover:bg-muted' }}"
                        @if (($activeNav ?? '') === $item['label']) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    </div>
</template>
