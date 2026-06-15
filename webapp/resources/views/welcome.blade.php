@extends('layouts.app')

@section('title', 'DoctorStats — Clinical Statistical Analysis')

@section('body')
    <div class="min-h-screen bg-background">
        <a href="#main-content" class="skip-link">Skip to main content</a>

        <header class="border-b border-border bg-card sticky top-0 z-50">
            <div class="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
                <x-logo />
                <nav class="hidden md:flex items-center gap-6 text-sm" aria-label="Landing navigation">
                    <a href="#features" class="text-muted-foreground hover:text-foreground transition-colors">Features</a>
                    <a href="#how-it-works" class="text-muted-foreground hover:text-foreground transition-colors">How it works</a>
                    <a href="#pricing" class="text-muted-foreground hover:text-foreground transition-colors">Pricing</a>
                </nav>
                <div class="flex items-center gap-3">
                    <a href="{{ route('login') }}" class="btn btn-ghost hidden sm:inline-flex">Sign in</a>
                    <a href="{{ route('register') }}" class="btn btn-primary">Get started</a>
                </div>
            </div>
        </header>

        <main id="main-content">
            <section class="py-20 md:py-28">
                <div class="mx-auto max-w-7xl px-4">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                        <div>
                            <p class="text-sm font-semibold text-accent uppercase tracking-wide mb-4">Analysis as a Service</p>
                            <h1 class="text-5xl font-bold text-foreground mb-6 leading-tight">
                                Statistical analysis for clinical research
                            </h1>
                            <p class="text-lg text-muted-foreground mb-8 max-w-lg">
                                Upload datasets, define objectives, and receive automated statistical tests with AI-powered interpretation and publication-ready reports.
                            </p>
                            <div class="flex flex-wrap gap-4">
                                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Start free analysis</a>
                                <a href="{{ route('login') }}" class="btn btn-outline btn-lg">Sign in</a>
                            </div>
                        </div>
                        <div class="relative">
                            <div class="rounded-2xl border border-border bg-card p-8 shadow-xl">
                                <div class="flex items-end gap-2 h-32 mb-6">
                                    @foreach ([48, 64, 40, 56] as $h)
                                        <div class="flex-1 bg-primary rounded-md" style="height: {{ $h }}%"></div>
                                    @endforeach
                                </div>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div><p class="text-lg font-bold text-primary">95%</p><p class="text-xs text-muted-foreground">Success</p></div>
                                    <div><p class="text-lg font-bold text-accent">4.2m</p><p class="text-xs text-muted-foreground">Data Points</p></div>
                                    <div><p class="text-lg font-bold text-primary">&lt;5m</p><p class="text-xs text-muted-foreground">Avg Time</p></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="features" class="py-20 border-t border-border">
                <div class="mx-auto max-w-7xl px-4">
                    <h2 class="text-4xl font-bold text-foreground text-center mb-12">Key Features</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        @foreach ([
                            ['title' => 'Automated Analysis', 'desc' => 'AI selects optimal statistical tests for your data'],
                            ['title' => 'AI Interpretation', 'desc' => 'Plain-language explanations of clinical significance'],
                            ['title' => 'Enterprise Security', 'desc' => 'HIPAA-ready data protection and org isolation'],
                        ] as $feature)
                            <div class="p-6 rounded-lg border border-border hover:border-primary hover:shadow-lg transition-all bg-card">
                                <div class="w-8 h-8 text-primary mb-4 font-bold">◆</div>
                                <h3 class="text-lg font-semibold text-foreground mb-2">{{ $feature['title'] }}</h3>
                                <p class="text-muted-foreground text-sm">{{ $feature['desc'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="how-it-works" class="py-20 bg-muted">
                <div class="mx-auto max-w-7xl px-4">
                    <h2 class="text-4xl font-bold text-foreground text-center mb-12">How It Works</h2>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        @foreach (['Upload', 'Describe', 'Analyze', 'Report'] as $i => $step)
                            <div class="text-center">
                                <div class="w-12 h-12 rounded-full bg-primary text-primary-foreground font-bold text-lg flex items-center justify-center mx-auto mb-4">{{ $i + 1 }}</div>
                                <h3 class="font-semibold text-foreground mb-2">{{ $step }}</h3>
                                <p class="text-sm text-muted-foreground">{{ ['Upload your dataset', 'Set objectives and map columns', 'Automated statistical engine', 'AI-powered report'][$i] }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="pricing" class="py-20">
                <div class="mx-auto max-w-7xl px-4">
                    <h2 class="text-4xl font-bold text-foreground text-center mb-12">Pricing</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
                        @foreach ([
                            ['name' => 'Starter', 'price' => '$0', 'features' => ['3 analyses/month', 'Basic reports', 'Email support'], 'highlighted' => false],
                            ['name' => 'Professional', 'price' => '$99', 'features' => ['24 analyses/month', 'AI interpretation', 'Priority processing', 'Team collaboration'], 'highlighted' => true],
                        ] as $plan)
                            <div class="rounded-xl p-8 transition-all {{ $plan['highlighted'] ? 'border-2 border-primary bg-primary-50 shadow-lg' : 'border border-border bg-card' }}">
                                <h3 class="text-2xl font-bold text-foreground">{{ $plan['name'] }}</h3>
                                <p class="text-4xl font-bold text-primary mt-4">{{ $plan['price'] }}<span class="text-base font-normal text-muted-foreground">/mo</span></p>
                                <ul class="mt-6 space-y-3">
                                    @foreach ($plan['features'] as $f)
                                        <li class="flex items-center gap-2 text-sm text-foreground">✓ {{ $f }}</li>
                                    @endforeach
                                </ul>
                                <a href="{{ route('register') }}" class="btn {{ $plan['highlighted'] ? 'btn-primary' : 'btn-outline' }} w-full mt-6">Get Started</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="py-20 bg-primary">
                <div class="mx-auto max-w-7xl px-4 text-center">
                    <h2 class="text-3xl font-bold text-primary-foreground mb-4">Ready to analyze your data?</h2>
                    <p class="text-primary-foreground/80 mb-8 max-w-xl mx-auto">
                        Join researchers using DoctorStats for clinical-grade statistical analysis.
                    </p>
                    <a href="{{ route('register') }}" class="btn btn-secondary btn-lg">Start Free Analysis →</a>
                </div>
            </section>
        </main>

        <footer class="border-t border-border py-12 bg-card">
            <div class="mx-auto max-w-7xl px-4 flex flex-col md:flex-row justify-between items-center gap-4">
                <x-logo />
                <p class="text-sm text-muted-foreground">© {{ date('Y') }} DoctorStats. All rights reserved.</p>
            </div>
        </footer>
    </div>
@endsection
