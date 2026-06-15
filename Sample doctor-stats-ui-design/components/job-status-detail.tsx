'use client'

import { ArrowRight, CheckCircle2, Clock, FileText } from 'lucide-react'
import { AppShell } from '@/components/app-shell'
import { Alert } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ProgressBar } from '@/components/ui/progress'
import type { AnalysisStatus } from '@/lib/constants'

const TIMELINE: { status: AnalysisStatus; label: string; time?: string; done: boolean }[] = [
  { status: 'pending', label: 'Job submitted', time: '10:32 AM', done: true },
  { status: 'processing', label: 'Statistical analysis running', time: '10:33 AM', done: true },
  { status: 'processing', label: 'Generating visualisations', done: false },
  { status: 'completed', label: 'AI interpretation', done: false },
]

export default function JobStatusDetail({ status = 'processing' }: { status?: AnalysisStatus }) {
  return (
    <AppShell activeNav="Analyses">
      <div className="mb-8">
        <div className="flex flex-wrap items-center gap-3 mb-2">
          <h1 className="text-3xl font-bold text-foreground">Diabetes Risk Factors</h1>
          <Badge variant={status}>
            {status.charAt(0).toUpperCase() + status.slice(1)}
          </Badge>
        </div>
        <p className="text-muted-foreground">Job ID: AN-2024-0248 • Submitted 30 minutes ago</p>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div className="lg:col-span-2 space-y-6">
          {status === 'processing' && (
            <Alert variant="info" title="Analysis in progress">
              Your data is being analyzed. This page updates automatically. Typical completion time is under 5 minutes.
            </Alert>
          )}
          {status === 'failed' && (
            <Alert variant="error" title="Analysis failed">
              Insufficient data for the selected statistical tests. Please review your column mappings and try again.
            </Alert>
          )}

          <Card>
            <CardHeader>
              <CardTitle>Progress</CardTitle>
            </CardHeader>
            <CardContent>
              {status === 'processing' && (
                <ProgressBar indeterminate label="Processing..." className="mb-6" />
              )}
              <ol className="space-y-4" aria-label="Job progress timeline">
                {TIMELINE.map((step, i) => (
                  <li key={i} className="flex items-start gap-4">
                    <div className="mt-0.5">
                      {step.done ? (
                        <CheckCircle2 className="w-5 h-5 text-accent" aria-hidden="true" />
                      ) : status === 'processing' && i === 2 ? (
                        <Clock className="w-5 h-5 text-secondary animate-spin" aria-hidden="true" />
                      ) : (
                        <div className="w-5 h-5 rounded-full border-2 border-border" aria-hidden="true" />
                      )}
                    </div>
                    <div>
                      <p className="font-medium text-foreground">{step.label}</p>
                      {step.time && (
                        <p className="text-xs text-muted-foreground">{step.time}</p>
                      )}
                    </div>
                  </li>
                ))}
              </ol>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Objectives</CardTitle>
            </CardHeader>
            <CardContent>
              <p className="text-sm text-foreground leading-relaxed">
                Analyze the relationship between HbA1c levels, BMI, and age in our Type 2 diabetes cohort
                to identify significant risk factors and inform treatment protocols for high-risk patients.
              </p>
            </CardContent>
          </Card>
        </div>

        <div className="space-y-6">
          <Card>
            <CardTitle className="mb-4">Details</CardTitle>
            <dl className="space-y-3 text-sm">
              <div className="flex justify-between">
                <dt className="text-muted-foreground">File</dt>
                <dd className="font-medium">diabetes_cohort.xlsx</dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-muted-foreground">Rows</dt>
                <dd className="font-medium">842</dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-muted-foreground">Columns</dt>
                <dd className="font-medium">14</dd>
              </div>
              <div className="flex justify-between">
                <dt className="text-muted-foreground">Access</dt>
                <dd className="font-medium">All members</dd>
              </div>
            </dl>
          </Card>

          {status === 'completed' && (
            <Button className="w-full gap-2">
              <FileText className="w-4 h-4" aria-hidden="true" />
              View Report
              <ArrowRight className="w-4 h-4" aria-hidden="true" />
            </Button>
          )}
          {status === 'failed' && (
            <Button variant="outline" className="w-full">Edit and resubmit</Button>
          )}
          {status === 'processing' && (
            <p className="text-xs text-muted-foreground text-center flex items-center justify-center gap-1">
              <Clock className="w-3 h-3 animate-spin" aria-hidden="true" />
              Auto-refreshing every 5 seconds
            </p>
          )}
        </div>
      </div>
    </AppShell>
  )
}
