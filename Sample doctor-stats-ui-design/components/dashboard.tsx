'use client'

import {
  AlertCircle,
  BarChart3,
  CheckCircle2,
  Clock,
  FileText,
  RefreshCw,
  TrendingUp,
  Users,
  XCircle,
} from 'lucide-react'
import { AppShell } from '@/components/app-shell'
import { Alert } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { EmptyState } from '@/components/ui/empty-state'
import { ProgressBar } from '@/components/ui/progress'
import { SkeletonCard, SkeletonRow } from '@/components/ui/skeleton'
import type { AnalysisStatus } from '@/lib/constants'
import { useShowcaseNavigation } from '@/lib/showcase-navigation'
import { cn } from '@/lib/utils'

const STATUS_ICONS: Record<AnalysisStatus, React.ReactNode> = {
  completed: <CheckCircle2 className="w-5 h-5 text-accent" aria-hidden="true" />,
  processing: <Clock className="w-5 h-5 text-secondary animate-spin" aria-hidden="true" />,
  pending: <Clock className="w-5 h-5 text-secondary" aria-hidden="true" />,
  failed: <XCircle className="w-5 h-5 text-destructive" aria-hidden="true" />,
}

const recentAnalyses = [
  { id: 1, name: 'Cardiovascular Study Q1', status: 'completed' as const, date: '2 hours ago' },
  { id: 2, name: 'Diabetes Risk Factors', status: 'processing' as const, date: '30 mins ago' },
  { id: 3, name: 'Medication Efficacy Analysis', status: 'pending' as const, date: '15 mins ago' },
  { id: 4, name: 'Clinical Trial Data Review', status: 'failed' as const, date: 'Yesterday' },
  { id: 5, name: 'Biomarker Correlation Study', status: 'completed' as const, date: '2 days ago' },
]

const stats = [
  { label: 'Total Analyses', value: '24', icon: BarChart3, color: 'text-primary' },
  { label: 'Active Subscription', value: 'Professional', icon: TrendingUp, color: 'text-accent' },
  { label: 'Organization Members', value: '5', icon: Users, color: 'text-secondary' },
  { label: 'Reports Generated', value: '18', icon: FileText, color: 'text-primary' },
]

type DashboardVariant = 'default' | 'empty' | 'loading' | 'error'

export default function Dashboard({ variant = 'default' }: { variant?: DashboardVariant }) {
  const showcaseNav = useShowcaseNavigation()

  return (
    <AppShell activeNav="Dashboard">
      {variant === 'error' && (
        <Alert variant="error" title="Unable to load dashboard" className="mb-6">
          Something went wrong fetching your data. Please try again.
          <Button variant="outline" size="sm" className="mt-3 gap-2">
            <RefreshCw className="w-4 h-4" /> Retry
          </Button>
        </Alert>
      )}

      <div className="mb-8">
        <h1 className="text-3xl font-bold text-foreground mb-2">Welcome back, Dr. Sarah</h1>
        <p className="text-muted-foreground">Here&apos;s what&apos;s happening with your analyses today</p>
      </div>

      {variant === 'loading' ? (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8" aria-busy="true" aria-label="Loading stats">
          {Array.from({ length: 4 }).map((_, i) => (
            <SkeletonCard key={i} />
          ))}
        </div>
      ) : variant !== 'error' && (
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          {stats.map((stat, i) => {
            const Icon = stat.icon
            return (
              <Card key={i} variant="stat">
                <div className="flex items-center justify-between mb-4">
                  <p className="text-sm font-medium text-muted-foreground">{stat.label}</p>
                  <Icon className={cn('w-5 h-5', stat.color)} aria-hidden="true" />
                </div>
                <p className="text-3xl font-bold text-foreground">{stat.value}</p>
              </Card>
            )
          })}
        </div>
      )}

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between mb-0">
              <CardTitle>Recent Analyses</CardTitle>
              <Button variant="outline" size="sm">View All</Button>
            </CardHeader>
            <CardContent className="mt-6">
              {variant === 'loading' && (
                <div className="space-y-4" aria-busy="true">
                  {Array.from({ length: 4 }).map((_, i) => (
                    <SkeletonRow key={i} />
                  ))}
                </div>
              )}
              {variant === 'empty' && (
                <EmptyState
                  icon={BarChart3}
                  title="No analyses yet"
                  description="Upload your first dataset to get AI-powered statistical insights in minutes."
                  actionLabel="New Analysis"
                  onAction={() => {}}
                />
              )}
              {(variant === 'default' || variant === 'error') && (
                <div className="space-y-4">
                  {recentAnalyses.map((analysis) => (
                    <div
                      key={analysis.id}
                      className="flex items-center justify-between p-4 rounded-lg border border-border hover:bg-muted transition-colors group cursor-pointer"
                    >
                      <div className="flex items-center gap-4 flex-1 min-w-0">
                        {STATUS_ICONS[analysis.status]}
                        <div className="flex-1 min-w-0">
                          <p className="font-medium text-foreground truncate">{analysis.name}</p>
                          <p className="text-xs text-muted-foreground">{analysis.date}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-3">
                        <Badge variant={analysis.status}>
                          {analysis.status.charAt(0).toUpperCase() + analysis.status.slice(1)}
                        </Badge>
                        <Button
                          variant="ghost"
                          size="sm"
                          className="opacity-0 group-hover:opacity-100 transition-opacity"
                        >
                          View
                        </Button>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardTitle className="mb-4">Quick Actions</CardTitle>
            <div className="space-y-3">
              <Button
                className="w-full justify-start"
                size="sm"
                onClick={() => showcaseNav?.navigate('NewAnalysis')}
              >
                <BarChart3 className="w-4 h-4 mr-2" aria-hidden="true" />
                New Analysis
              </Button>
              <Button
                variant="outline"
                className="w-full justify-start"
                size="sm"
                onClick={() => showcaseNav?.navigate('Reports')}
              >
                <FileText className="w-4 h-4 mr-2" aria-hidden="true" />
                View Reports
              </Button>
              <Button
                variant="outline"
                className="w-full justify-start"
                size="sm"
                onClick={() => showcaseNav?.navigate('Org')}
              >
                <Users className="w-4 h-4 mr-2" aria-hidden="true" />
                Team Management
              </Button>
            </div>
          </Card>

          <div className="rounded-lg border border-primary-200 bg-primary-50 p-6">
            <div className="flex items-center justify-between mb-4">
              <h3 className="font-semibold text-primary-900">Your Plan</h3>
              <Badge>Active</Badge>
            </div>
            <div className="space-y-3">
              <div>
                <p className="text-sm text-primary-800">Professional Plan</p>
                <p className="text-xs text-primary-700">$99/month</p>
              </div>
              <ProgressBar value={75} label="Analyses used this month" />
              <p className="text-xs text-primary-700">18 of 24 analyses used</p>
            </div>
          </div>

          <Card>
            <div className="flex gap-3">
              <AlertCircle className="w-5 h-5 text-secondary flex-shrink-0 mt-0.5" aria-hidden="true" />
              <div>
                <h4 className="font-semibold text-foreground mb-1 text-sm">Tip</h4>
                <p className="text-xs text-muted-foreground">
                  Column descriptions improve AI interpretation accuracy. Add them in your next analysis.
                </p>
              </div>
            </div>
          </Card>
        </div>
      </div>
    </AppShell>
  )
}
