'use client'

import { useCallback, useState } from 'react'
import { ShowcaseNavigator } from '@/components/showcase-navigator'
import { ShowcaseNavigationProvider, type AppNavTarget } from '@/lib/showcase-navigation'
import LandingPage from '@/components/landing-page'
import Dashboard from '@/components/dashboard'
import AnalysisWorkflow from '@/components/analysis-workflow'
import ReportViewer from '@/components/report-viewer'
import JobStatusDetail from '@/components/job-status-detail'
import ReportsList from '@/components/reports-list'
import Profile from '@/components/profile'
import OrgMembers from '@/components/org-members'
import SignIn from '@/components/auth/sign-in'
import Register from '@/components/auth/register'
import ForgotPassword from '@/components/auth/forgot-password'
import ResetPassword from '@/components/auth/reset-password'
import EmailVerification from '@/components/auth/email-verification'
import AcceptInvitation from '@/components/auth/accept-invitation'

const VIEWS = [
  { id: 'landing', name: 'Landing', group: 'Public' },
  { id: 'signin', name: 'Sign In', group: 'Auth' },
  { id: 'register', name: 'Register', group: 'Auth' },
  { id: 'forgot', name: 'Forgot Password', group: 'Auth' },
  { id: 'reset', name: 'Reset Password', group: 'Auth' },
  { id: 'verify', name: 'Email Verify', group: 'Auth' },
  { id: 'invite', name: 'Accept Invite', group: 'Auth' },
  { id: 'dashboard', name: 'Dashboard', group: 'App' },
  { id: 'dashboard-empty', name: 'Dashboard (Empty)', group: 'App' },
  { id: 'dashboard-loading', name: 'Dashboard (Loading)', group: 'App' },
  { id: 'dashboard-error', name: 'Dashboard (Error)', group: 'App' },
  { id: 'workflow', name: 'Analysis Wizard', group: 'App' },
  { id: 'job-status', name: 'Job Status', group: 'App' },
  { id: 'reports', name: 'Reports List', group: 'App' },
  { id: 'report', name: 'Report Viewer', group: 'App' },
  { id: 'profile', name: 'Profile', group: 'App' },
  { id: 'org', name: 'Org Members', group: 'App' },
] as const

type ViewId = (typeof VIEWS)[number]['id']

const NAV_TO_VIEW: Record<AppNavTarget, ViewId> = {
  Dashboard: 'dashboard',
  Analyses: 'job-status',
  Reports: 'reports',
  Org: 'org',
  Profile: 'profile',
  SignIn: 'signin',
  NewAnalysis: 'workflow',
}

export default function UIShowcase() {
  const [activeView, setActiveView] = useState<ViewId>('landing')

  const handleAppNavigate = useCallback((target: AppNavTarget) => {
    setActiveView(NAV_TO_VIEW[target])
  }, [])

  const renderView = () => {
    switch (activeView) {
      case 'landing': return (
        <LandingPage
          onSignIn={() => setActiveView('signin')}
          onGetStarted={() => setActiveView('register')}
        />
      )
      case 'signin': return (
        <SignIn
          onSuccess={() => setActiveView('dashboard')}
          onNavigateRegister={() => setActiveView('register')}
          onNavigateForgot={() => setActiveView('forgot')}
        />
      )
      case 'register': return <Register />
      case 'forgot': return <ForgotPassword />
      case 'reset': return <ResetPassword />
      case 'verify': return <EmailVerification />
      case 'invite': return <AcceptInvitation />
      case 'dashboard': return <Dashboard variant="default" />
      case 'dashboard-empty': return <Dashboard variant="empty" />
      case 'dashboard-loading': return <Dashboard variant="loading" />
      case 'dashboard-error': return <Dashboard variant="error" />
      case 'workflow': return <AnalysisWorkflow isOrgAccount />
      case 'job-status': return <JobStatusDetail status="processing" />
      case 'reports': return <ReportsList />
      case 'report': return <ReportViewer />
      case 'profile': return <Profile />
      case 'org': return <OrgMembers />
      default: return <LandingPage />
    }
  }

  return (
    <ShowcaseNavigationProvider navigate={handleAppNavigate}>
      <div className="min-h-screen bg-background pb-24">
        {renderView()}
        <ShowcaseNavigator
          views={VIEWS}
          activeView={activeView}
          onViewChange={(id) => setActiveView(id as ViewId)}
        />
      </div>
    </ShowcaseNavigationProvider>
  )
}
