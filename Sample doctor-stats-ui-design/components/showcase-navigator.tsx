'use client'

import { LayoutGrid, Search, X } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { cn } from '@/lib/utils'

export type ShowcaseView = {
  id: string
  name: string
  group: 'Public' | 'Auth' | 'App'
}

const GROUP_META: Record<ShowcaseView['group'], { label: string; description: string }> = {
  Public: { label: 'Public', description: 'Marketing & unauthenticated' },
  Auth: { label: 'Auth', description: 'Sign in, register, invitations' },
  App: { label: 'App', description: 'Authenticated product screens' },
}

const COMPONENTS = [
  'Button', 'Card', 'Input', 'Textarea', 'Select', 'Badge',
  'Alert', 'Table', 'Stepper', 'Dialog', 'Avatar', 'ProgressBar',
  'EmptyState', 'Skeleton', 'Checkbox', 'Logo', 'AppShell',
]

type ShowcaseNavigatorProps = {
  views: readonly ShowcaseView[]
  activeView: string
  onViewChange: (id: string) => void
}

export function ShowcaseNavigator({ views, activeView, onViewChange }: ShowcaseNavigatorProps) {
  const [open, setOpen] = useState(false)
  const [tab, setTab] = useState<'screens' | 'components'>('screens')
  const [query, setQuery] = useState('')

  const active = views.find((v) => v.id === activeView)
  const groups = ['Public', 'Auth', 'App'] as const

  const filteredViews = useMemo(() => {
    const q = query.trim().toLowerCase()
    if (!q) return views
    return views.filter(
      (v) => v.name.toLowerCase().includes(q) || v.group.toLowerCase().includes(q),
    )
  }, [views, query])

  useEffect(() => {
    if (!open) return
    const onKey = (e: KeyboardEvent) => {
      if (e.key === 'Escape') setOpen(false)
    }
    window.addEventListener('keydown', onKey)
    return () => window.removeEventListener('keydown', onKey)
  }, [open])

  const selectView = (id: string) => {
    onViewChange(id)
    setOpen(false)
    setQuery('')
  }

  return (
    <>
      {open && (
        <div
          className="fixed inset-0 z-[60] bg-black/20 backdrop-blur-[1px]"
          onClick={() => setOpen(false)}
          aria-hidden="true"
        />
      )}

      <div className="fixed bottom-0 inset-x-0 z-[70] pointer-events-none flex justify-center p-4 sm:p-6">
        <div className="pointer-events-auto w-full max-w-lg">
          {open && (
            <div
              role="dialog"
              aria-modal="true"
              aria-label="Design system navigator"
              className="mb-3 rounded-xl border border-border bg-card shadow-2xl overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-200"
            >
              <div className="flex items-center justify-between px-4 py-3 border-b border-border bg-muted/50">
                <div className="flex items-center gap-2">
                  <LayoutGrid className="w-4 h-4 text-primary" aria-hidden="true" />
                  <span className="text-sm font-semibold text-foreground">Design Navigator</span>
                </div>
                <button
                  type="button"
                  onClick={() => setOpen(false)}
                  className="p-1.5 rounded-md text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                  aria-label="Close navigator"
                >
                  <X className="w-4 h-4" />
                </button>
              </div>

              <div className="flex border-b border-border">
                {(['screens', 'components'] as const).map((t) => (
                  <button
                    key={t}
                    type="button"
                    onClick={() => setTab(t)}
                    className={cn(
                      'flex-1 px-4 py-2.5 text-xs font-medium transition-colors capitalize',
                      tab === t
                        ? 'text-primary border-b-2 border-primary bg-primary-50/50'
                        : 'text-muted-foreground hover:text-foreground hover:bg-muted/50',
                    )}
                  >
                    {t}
                  </button>
                ))}
              </div>

              {tab === 'screens' ? (
                <div className="max-h-[min(50vh,360px)] overflow-y-auto">
                  <div className="p-3 border-b border-border">
                    <div className="relative">
                      <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-muted-foreground" aria-hidden="true" />
                      <input
                        type="search"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        placeholder="Search screens..."
                        className="w-full h-9 pl-9 pr-3 text-sm rounded-lg border border-border bg-input focus:outline-none focus:ring-2 focus:ring-primary"
                        aria-label="Search screens"
                      />
                    </div>
                  </div>

                  {groups.map((group) => {
                    const items = filteredViews.filter((v) => v.group === group)
                    if (items.length === 0) return null

                    return (
                      <div key={group} className="py-2">
                        <div className="px-4 py-1.5">
                          <p className="text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                            {GROUP_META[group].label}
                          </p>
                          <p className="text-[10px] text-muted-foreground/80">{GROUP_META[group].description}</p>
                        </div>
                        <ul role="listbox" aria-label={`${group} screens`}>
                          {items.map((view) => (
                            <li key={view.id}>
                              <button
                                type="button"
                                role="option"
                                aria-selected={activeView === view.id}
                                onClick={() => selectView(view.id)}
                                className={cn(
                                  'w-full text-left px-4 py-2.5 text-sm transition-colors flex items-center justify-between gap-3 min-h-[44px]',
                                  activeView === view.id
                                    ? 'bg-primary-50 text-primary font-medium border-l-2 border-l-primary'
                                    : 'text-foreground hover:bg-muted border-l-2 border-l-transparent',
                                )}
                              >
                                <span>{view.name}</span>
                                {activeView === view.id && (
                                  <span className="text-[10px] font-medium uppercase tracking-wide text-primary/70">
                                    Active
                                  </span>
                                )}
                              </button>
                            </li>
                          ))}
                        </ul>
                      </div>
                    )
                  })}

                  {filteredViews.length === 0 && (
                    <p className="px-4 py-8 text-sm text-muted-foreground text-center">No screens match your search.</p>
                  )}
                </div>
              ) : (
                <div className="p-4 max-h-[min(50vh,360px)] overflow-y-auto">
                  <p className="text-xs text-muted-foreground mb-3">17 reusable primitives in <code className="text-foreground">components/ui/</code></p>
                  <div className="grid grid-cols-2 gap-2">
                    {COMPONENTS.map((c) => (
                      <div
                        key={c}
                        className="px-3 py-2 rounded-lg border border-border bg-muted/30 text-foreground text-xs font-medium text-center"
                      >
                        {c}
                      </div>
                    ))}
                  </div>
                </div>
              )}

              <div className="px-4 py-2.5 border-t border-border bg-muted/30 flex items-center justify-between text-[10px] text-muted-foreground">
                <span>Inter · Lucide · Tailwind v4</span>
                <span>{views.length} screens</span>
              </div>
            </div>
          )}

          <button
            type="button"
            onClick={() => setOpen((v) => !v)}
            aria-expanded={open}
            aria-haspopup="dialog"
            className={cn(
              'w-full flex items-center gap-3 rounded-full border border-border bg-card/95 backdrop-blur-md shadow-lg px-4 py-2.5 sm:px-5 sm:py-3 transition-all hover:shadow-xl hover:border-primary/30',
              open && 'ring-2 ring-primary/20 border-primary/40',
            )}
          >
            <div className="flex items-center justify-center size-8 rounded-full bg-primary text-primary-foreground flex-shrink-0">
              <LayoutGrid className="w-4 h-4" aria-hidden="true" />
            </div>
            <div className="flex-1 min-w-0 text-left">
              <p className="text-[10px] font-medium uppercase tracking-wider text-muted-foreground leading-none mb-0.5">
                Design preview
              </p>
              <p className="text-sm font-semibold text-foreground truncate">
                {active?.name ?? 'Select screen'}
              </p>
            </div>
            <span className="text-xs font-medium text-primary flex-shrink-0 hidden sm:inline">
              {open ? 'Close' : 'Browse'}
            </span>
          </button>
        </div>
      </div>
    </>
  )
}
