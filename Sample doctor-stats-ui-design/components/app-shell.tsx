'use client'

import { Bell, ChevronDown, LogOut, Menu, Settings, User, X } from 'lucide-react'
import Link from 'next/link'
import { useState } from 'react'
import { Button } from '@/components/ui/button'
import { Logo } from '@/components/ui/logo'
import { Avatar } from '@/components/ui/avatar'
import { useShowcaseNavigation, type AppNavTarget } from '@/lib/showcase-navigation'
import { cn } from '@/lib/utils'

const NAV_ITEMS: { label: AppNavTarget; href: string }[] = [
  { label: 'Dashboard', href: '#dashboard' },
  { label: 'Analyses', href: '#analyses' },
  { label: 'Reports', href: '#reports' },
  { label: 'Org', href: '#org' },
]

type AppShellProps = {
  children: React.ReactNode
  activeNav?: AppNavTarget
  maxWidth?: '7xl' | '6xl' | '5xl'
  orgName?: string
  userName?: string
  showNav?: boolean
}

function AppShell({
  children,
  activeNav = 'Dashboard',
  maxWidth = '7xl',
  orgName = "St. Mary's Research Lab",
  userName = 'Dr. Sarah Chen',
  showNav = true,
}: AppShellProps) {
  const [mobileOpen, setMobileOpen] = useState(false)
  const [menuOpen, setMenuOpen] = useState(false)
  const showcaseNav = useShowcaseNavigation()

  const maxWidthClass = {
    '7xl': 'max-w-7xl',
    '6xl': 'max-w-6xl',
    '5xl': 'max-w-5xl',
  }[maxWidth]

  const goTo = (target: AppNavTarget) => {
    if (showcaseNav) {
      showcaseNav.navigate(target)
      setMobileOpen(false)
      setMenuOpen(false)
    }
  }

  const navClass = (label: AppNavTarget) =>
    cn(
      'px-3 py-2 text-sm font-medium rounded-lg transition-colors',
      activeNav === label
        ? 'text-primary border-b-2 border-primary rounded-none'
        : 'text-muted-foreground hover:text-foreground hover:bg-muted',
    )

  const mobileNavClass = (label: AppNavTarget) =>
    cn(
      'block w-full text-left px-4 py-3 rounded-lg text-sm font-medium min-h-[44px]',
      activeNav === label
        ? 'bg-primary-50 text-primary'
        : 'text-foreground hover:bg-muted',
    )

  return (
    <div className="min-h-screen bg-background">
      <a href="#main-content" className="skip-link">
        Skip to main content
      </a>

      <header className="border-b border-border bg-card sticky top-0 z-50">
        <div className={cn('mx-auto px-4 py-4', maxWidthClass)}>
          <div className="flex items-center justify-between">
            <div className="flex items-center gap-6">
              {showcaseNav ? (
                <button type="button" onClick={() => goTo('Dashboard')} className="text-left">
                  <Logo subtitle={orgName} />
                </button>
              ) : (
                <Logo subtitle={orgName} />
              )}
              {showNav && (
                <nav className="hidden md:flex items-center gap-1" aria-label="Main navigation">
                  {NAV_ITEMS.map((item) =>
                    showcaseNav ? (
                      <button
                        key={item.label}
                        type="button"
                        onClick={() => goTo(item.label)}
                        className={navClass(item.label)}
                        aria-current={activeNav === item.label ? 'page' : undefined}
                      >
                        {item.label}
                      </button>
                    ) : (
                      <a
                        key={item.label}
                        href={item.href}
                        className={navClass(item.label)}
                        aria-current={activeNav === item.label ? 'page' : undefined}
                      >
                        {item.label}
                      </a>
                    ),
                  )}
                </nav>
              )}
            </div>

            <div className="flex items-center gap-2">
              <Button variant="ghost" size="icon" className="hidden sm:flex" aria-label="Notifications">
                <Bell className="w-4 h-4" />
              </Button>

              <div className="relative">
                <Button
                  variant="ghost"
                  className="gap-2 pl-2 pr-1"
                  onClick={() => setMenuOpen(!menuOpen)}
                  aria-expanded={menuOpen}
                  aria-haspopup="menu"
                  aria-label="User menu"
                >
                  <Avatar name={userName} size="sm" />
                  <span className="hidden sm:inline text-sm font-medium">{userName}</span>
                  <ChevronDown className="w-4 h-4 text-muted-foreground" />
                </Button>
                {menuOpen && (
                  <>
                    <div className="fixed inset-0 z-40" onClick={() => setMenuOpen(false)} aria-hidden="true" />
                    <div
                      role="menu"
                      className="absolute right-0 mt-2 w-48 rounded-lg border border-border bg-card shadow-lg z-50 py-1"
                    >
                      {showcaseNav ? (
                        <>
                          <button
                            type="button"
                            role="menuitem"
                            onClick={() => goTo('Profile')}
                            className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-muted"
                          >
                            <User className="w-4 h-4" /> Profile
                          </button>
                          <button
                            type="button"
                            role="menuitem"
                            onClick={() => goTo('Org')}
                            className="flex w-full items-center gap-2 px-4 py-2 text-sm hover:bg-muted"
                          >
                            <Settings className="w-4 h-4" /> Org settings
                          </button>
                          <hr className="my-1 border-border" />
                          <button
                            type="button"
                            role="menuitem"
                            onClick={() => goTo('SignIn')}
                            className="flex w-full items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-muted"
                          >
                            <LogOut className="w-4 h-4" /> Sign out
                          </button>
                        </>
                      ) : (
                        <>
                          <a href="#profile" role="menuitem" className="flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted">
                            <User className="w-4 h-4" /> Profile
                          </a>
                          <a href="#org" role="menuitem" className="flex items-center gap-2 px-4 py-2 text-sm hover:bg-muted">
                            <Settings className="w-4 h-4" /> Org settings
                          </a>
                          <hr className="my-1 border-border" />
                          <button type="button" role="menuitem" className="flex w-full items-center gap-2 px-4 py-2 text-sm text-destructive hover:bg-muted">
                            <LogOut className="w-4 h-4" /> Sign out
                          </button>
                        </>
                      )}
                    </div>
                  </>
                )}
              </div>

              {showNav && (
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
              )}
            </div>
          </div>
        </div>
      </header>

      {mobileOpen && (
        <div className="fixed inset-0 z-50 md:hidden" role="dialog" aria-modal="true" aria-label="Navigation menu">
          <div className="fixed inset-0 bg-black/50" onClick={() => setMobileOpen(false)} aria-hidden="true" />
          <div className="fixed inset-y-0 right-0 w-72 bg-card border-l border-border shadow-xl flex flex-col">
            <div className="flex items-center justify-between p-4 border-b border-border">
              <Logo subtitle={orgName} size="sm" />
              <Button variant="ghost" size="icon" onClick={() => setMobileOpen(false)} aria-label="Close menu">
                <X className="w-5 h-5" />
              </Button>
            </div>
            <nav className="flex-1 p-4 space-y-1" aria-label="Mobile navigation">
              {NAV_ITEMS.map((item) =>
                showcaseNav ? (
                  <button
                    key={item.label}
                    type="button"
                    onClick={() => goTo(item.label)}
                    className={mobileNavClass(item.label)}
                    aria-current={activeNav === item.label ? 'page' : undefined}
                  >
                    {item.label}
                  </button>
                ) : (
                  <a
                    key={item.label}
                    href={item.href}
                    onClick={() => setMobileOpen(false)}
                    className={mobileNavClass(item.label)}
                    aria-current={activeNav === item.label ? 'page' : undefined}
                  >
                    {item.label}
                  </a>
                ),
              )}
            </nav>
          </div>
        </div>
      )}

      <main id="main-content" className={cn('mx-auto px-4 py-8', maxWidthClass)}>
        {children}
      </main>
    </div>
  )
}

function AuthLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="min-h-screen bg-muted flex flex-col">
      <a href="#auth-content" className="skip-link">
        Skip to main content
      </a>
      <header className="p-6">
        <Link href="/">
          <Logo />
        </Link>
      </header>
      <main id="auth-content" className="flex-1 flex items-center justify-center px-4 pb-12">
        {children}
      </main>
    </div>
  )
}

export { AppShell, AuthLayout }
