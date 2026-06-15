'use client'

import { Download, FileText, Search } from 'lucide-react'
import { AppShell } from '@/components/app-shell'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { EmptyState } from '@/components/ui/empty-state'
import { Input } from '@/components/ui/input'
import type { AnalysisStatus } from '@/lib/constants'
import { cn } from '@/lib/utils'

const reports = [
  { id: 1, title: 'Cardiovascular Study Q1', status: 'completed' as AnalysisStatus, date: 'Jun 10, 2026', jobId: 'AN-2024-0247' },
  { id: 2, title: 'Biomarker Correlation Study', status: 'completed' as AnalysisStatus, date: 'Jun 8, 2026', jobId: 'AN-2024-0245' },
  { id: 3, title: 'Clinical Trial Data Review', status: 'failed' as AnalysisStatus, date: 'Jun 5, 2026', jobId: 'AN-2024-0241' },
]

const FILTERS: (AnalysisStatus | 'all')[] = ['all', 'completed', 'processing', 'pending', 'failed']

export default function ReportsList({ variant = 'default' }: { variant?: 'default' | 'empty' }) {
  return (
    <AppShell activeNav="Reports">
      <div className="mb-8">
        <h1 className="text-3xl font-bold text-foreground mb-2">Reports</h1>
        <p className="text-muted-foreground">Browse and download your analysis reports</p>
      </div>

      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <CardTitle>All Reports</CardTitle>
            <div className="relative w-full sm:w-64">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" aria-hidden="true" />
              <Input className="pl-9" placeholder="Search reports..." aria-label="Search reports" />
            </div>
          </div>
          <div className="flex flex-wrap gap-2 mt-4" role="group" aria-label="Filter by status">
            {FILTERS.map((filter) => (
              <Button
                key={filter}
                variant={filter === 'all' ? 'default' : 'outline'}
                size="sm"
              >
                {filter === 'all' ? 'All' : filter.charAt(0).toUpperCase() + filter.slice(1)}
              </Button>
            ))}
          </div>
        </CardHeader>
        <CardContent>
          {variant === 'empty' ? (
            <EmptyState
              icon={FileText}
              title="No reports yet"
              description="Complete your first analysis to generate a report."
              actionLabel="New Analysis"
              onAction={() => {}}
            />
          ) : (
            <div className="space-y-3">
              {reports.map((report) => (
                <div
                  key={report.id}
                  className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 rounded-lg border border-border hover:bg-muted transition-colors group"
                >
                  <div className="flex items-start gap-4 min-w-0">
                    <FileText className="w-5 h-5 text-primary flex-shrink-0 mt-0.5" aria-hidden="true" />
                    <div className="min-w-0">
                      <p className="font-medium text-foreground truncate">{report.title}</p>
                      <p className="text-xs text-muted-foreground">
                        {report.jobId} • {report.date}
                      </p>
                    </div>
                  </div>
                  <div className="flex items-center gap-3 sm:flex-shrink-0">
                    <Badge variant={report.status}>
                      {report.status.charAt(0).toUpperCase() + report.status.slice(1)}
                    </Badge>
                    <Button variant="ghost" size="sm">View</Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      className={cn(report.status !== 'completed' && 'opacity-50 pointer-events-none')}
                      disabled={report.status !== 'completed'}
                    >
                      <Download className="w-4 h-4 mr-1" aria-hidden="true" />
                      HTML
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          )}
        </CardContent>
      </Card>
    </AppShell>
  )
}
