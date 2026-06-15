'use client'

import { createContext, useContext } from 'react'

export type AppNavTarget =
  | 'Dashboard'
  | 'Analyses'
  | 'Reports'
  | 'Org'
  | 'Profile'
  | 'SignIn'
  | 'NewAnalysis'

type ShowcaseNavigationContextValue = {
  navigate: (target: AppNavTarget) => void
}

const ShowcaseNavigationContext = createContext<ShowcaseNavigationContextValue | null>(null)

export function ShowcaseNavigationProvider({
  children,
  navigate,
}: {
  children: React.ReactNode
  navigate: (target: AppNavTarget) => void
}) {
  return (
    <ShowcaseNavigationContext.Provider value={{ navigate }}>
      {children}
    </ShowcaseNavigationContext.Provider>
  )
}

export function useShowcaseNavigation() {
  return useContext(ShowcaseNavigationContext)
}
