'use client'

import { useState } from 'react'
import { ArrowRight, BarChart3, CheckCircle, Menu, Shield, TrendingUp, X, Zap } from 'lucide-react'
import { Button } from '@/components/ui/button'
import { Logo } from '@/components/ui/logo'

type LandingPageProps = {
  onSignIn?: () => void
  onGetStarted?: () => void
}

export default function LandingPage({ onSignIn, onGetStarted }: LandingPageProps) {
  const [mobileOpen, setMobileOpen] = useState(false)

  return (
    <div className="min-h-screen bg-background">
      <a href="#landing-content" className="skip-link">Skip to main content</a>

      <header className="border-b border-border bg-card sticky top-0 z-50">
        <div className="mx-auto max-w-7xl px-4 py-4 flex items-center justify-between">
          <Logo />
          <nav className="hidden md:flex gap-8" aria-label="Marketing navigation">
            <a href="#features" className="text-muted-foreground hover:text-foreground transition">Features</a>
            <a href="#how-it-works" className="text-muted-foreground hover:text-foreground transition">How It Works</a>
            <a href="#pricing" className="text-muted-foreground hover:text-foreground transition">Pricing</a>
          </nav>
          <div className="flex items-center gap-2">
            <Button variant="outline" className="hidden sm:inline-flex" onClick={onSignIn}>Sign In</Button>
            <Button className="hidden sm:inline-flex" onClick={onGetStarted}>Get Started</Button>
            <Button
              variant="ghost"
              size="icon"
              className="md:hidden"
              onClick={() => setMobileOpen(true)}
              aria-label="Open menu"
              aria-expanded={mobileOpen}
            >
              <Menu className="w-5 h-5" />
            </Button>
          </div>
        </div>
      </header>

      {mobileOpen && (
        <div className="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Navigation menu">
          <div className="fixed inset-0 bg-black/50" onClick={() => setMobileOpen(false)} aria-hidden="true" />
          <div className="fixed inset-y-0 right-0 w-72 bg-card border-l border-border shadow-xl flex flex-col">
            <div className="flex items-center justify-between p-4 border-b border-border">
              <Logo size="sm" />
              <Button variant="ghost" size="icon" onClick={() => setMobileOpen(false)} aria-label="Close menu">
                <X className="w-5 h-5" />
              </Button>
            </div>
            <nav className="flex-1 p-4 space-y-1" aria-label="Mobile navigation">
              {['Features', 'How It Works', 'Pricing'].map((item) => (
                <a
                  key={item}
                  href={`#${item.toLowerCase().replace(/ /g, '-')}`}
                  onClick={() => setMobileOpen(false)}
                  className="block px-4 py-3 rounded-lg text-sm font-medium min-h-[44px] hover:bg-muted"
                >
                  {item}
                </a>
              ))}
            </nav>
            <div className="p-4 border-t border-border space-y-2">
              <Button variant="outline" className="w-full" onClick={() => { setMobileOpen(false); onSignIn?.() }}>Sign In</Button>
              <Button className="w-full" onClick={() => { setMobileOpen(false); onGetStarted?.() }}>Get Started</Button>
            </div>
          </div>
        </div>
      )}

      <main id="landing-content">
        <section className="relative overflow-hidden bg-gradient-to-br from-primary-50 to-background">
          <div className="mx-auto max-w-7xl px-4 py-24 sm:py-32">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
              <div className="space-y-6">
                <div className="inline-flex items-center gap-2 rounded-full bg-primary-100 px-4 py-2">
                  <Zap className="w-4 h-4 text-primary" aria-hidden="true" />
                  <span className="text-sm font-medium text-primary-700">AI-Powered Insights</span>
                </div>
                <h1 className="text-5xl sm:text-6xl font-bold text-foreground leading-tight">
                  Statistical Analysis, <span className="text-primary">Simplified</span>
                </h1>
                <p className="text-xl text-muted-foreground max-w-xl">
                  Upload clinical data, run rigorous statistical tests, and get publication-ready reports in minutes.
                </p>
                <div className="flex flex-col sm:flex-row gap-4 pt-4">
                  <Button size="lg" className="gap-2 min-h-[44px]" onClick={onGetStarted}>
                    Start Free Analysis <ArrowRight className="w-4 h-4" aria-hidden="true" />
                  </Button>
                  <Button size="lg" variant="outline" className="min-h-[44px]">Watch Demo</Button>
                </div>
              </div>
              <div className="relative">
                <div className="relative bg-card border border-border rounded-2xl p-6 shadow-xl">
                  <div className="h-24 bg-muted rounded-lg flex items-end gap-2 p-4 mb-4">
                    {[48, 64, 40, 56].map((h, i) => (
                      <div key={i} className="flex-1 bg-primary rounded-md" style={{ height: `${h}%` }} />
                    ))}
                  </div>
                  <div className="grid grid-cols-3 gap-2 text-center">
                    <div><p className="text-lg font-bold text-primary">95%</p><p className="text-xs text-muted-foreground">Success</p></div>
                    <div><p className="text-lg font-bold text-accent">4.2m</p><p className="text-xs text-muted-foreground">Data Points</p></div>
                    <div><p className="text-lg font-bold text-primary">&lt;5m</p><p className="text-xs text-muted-foreground">Avg Time</p></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <section id="features" className="py-20 border-t border-border">
          <div className="mx-auto max-w-7xl px-4">
            <h2 className="text-4xl font-bold text-foreground text-center mb-12">Key Features</h2>
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
              {[
                { icon: BarChart3, title: 'Automated Analysis', desc: 'AI selects optimal statistical tests for your data' },
                { icon: TrendingUp, title: 'AI Interpretation', desc: 'Plain-language explanations of clinical significance' },
                { icon: Shield, title: 'Enterprise Security', desc: 'HIPAA-ready data protection and org isolation' },
              ].map(({ icon: Icon, title, desc }) => (
                <div key={title} className="p-6 rounded-lg border border-border hover:border-primary hover:shadow-lg transition-all bg-card">
                  <Icon className="w-8 h-8 text-primary mb-4" aria-hidden="true" />
                  <h3 className="text-lg font-semibold text-foreground mb-2">{title}</h3>
                  <p className="text-muted-foreground text-sm">{desc}</p>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section id="how-it-works" className="py-20 bg-muted">
          <div className="mx-auto max-w-7xl px-4">
            <h2 className="text-4xl font-bold text-foreground text-center mb-12">How It Works</h2>
            <div className="grid grid-cols-1 md:grid-cols-4 gap-8">
              {['Upload', 'Describe', 'Analyze', 'Report'].map((step, i) => (
                <div key={step} className="text-center">
                  <div className="w-12 h-12 rounded-full bg-primary text-primary-foreground font-bold text-lg flex items-center justify-center mx-auto mb-4">
                    {i + 1}
                  </div>
                  <h3 className="font-semibold text-foreground mb-2">{step}</h3>
                  <p className="text-sm text-muted-foreground">
                    {['Upload your dataset', 'Set objectives and map columns', 'Automated statistical engine', 'AI-powered report'][i]}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section id="pricing" className="py-20">
          <div className="mx-auto max-w-7xl px-4">
            <h2 className="text-4xl font-bold text-foreground text-center mb-12">Pricing</h2>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-3xl mx-auto">
              {[
                { name: 'Starter', price: '$0', features: ['3 analyses/month', 'Basic reports', 'Email support'], highlighted: false },
                { name: 'Professional', price: '$99', features: ['24 analyses/month', 'AI interpretation', 'Priority processing', 'Team collaboration'], highlighted: true },
              ].map((plan) => (
                <div
                  key={plan.name}
                  className={`rounded-xl p-8 transition-all ${
                    plan.highlighted
                      ? 'border-2 border-primary bg-primary-50 shadow-lg'
                      : 'border border-border bg-card'
                  }`}
                >
                  <h3 className="text-2xl font-bold text-foreground">{plan.name}</h3>
                  <p className="text-4xl font-bold text-primary mt-4">{plan.price}<span className="text-base font-normal text-muted-foreground">/mo</span></p>
                  <ul className="mt-6 space-y-3">
                    {plan.features.map((f) => (
                      <li key={f} className="flex items-center gap-2 text-sm text-foreground">
                        <CheckCircle className="w-4 h-4 text-accent flex-shrink-0" aria-hidden="true" />
                        {f}
                      </li>
                    ))}
                  </ul>
                  <Button className="w-full mt-6" variant={plan.highlighted ? 'default' : 'outline'} onClick={onGetStarted}>
                    Get Started
                  </Button>
                </div>
              ))}
            </div>
          </div>
        </section>

        <section className="py-20 bg-primary">
          <div className="mx-auto max-w-7xl px-4 text-center">
            <h2 className="text-3xl font-bold text-primary-foreground mb-4">Ready to analyze your data?</h2>
            <p className="text-primary-foreground/80 mb-8 max-w-xl mx-auto">
              Join researchers using DoctorStats for clinical-grade statistical analysis.
            </p>
            <Button size="lg" variant="secondary" className="gap-2" onClick={onGetStarted}>
              Start Free Analysis <ArrowRight className="w-4 h-4" aria-hidden="true" />
            </Button>
          </div>
        </section>
      </main>

      <footer className="border-t border-border py-12 bg-card">
        <div className="mx-auto max-w-7xl px-4 flex flex-col md:flex-row justify-between items-center gap-4">
          <Logo />
          <p className="text-sm text-muted-foreground">© 2026 DoctorStats. All rights reserved.</p>
        </div>
      </footer>
    </div>
  )
}
