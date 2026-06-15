'use client'

import {
  BarChart3,
  BookOpen,
  ChevronDown,
  ClipboardList,
  Download,
  FileText,
  FlaskConical,
  Lightbulb,
  LineChart,
  ScrollText,
} from 'lucide-react'
import { useState } from 'react'
import { AppShell } from '@/components/app-shell'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import type { LucideIcon } from 'lucide-react'

type ReportSection = {
  id: string
  title: string
  icon: LucideIcon
  content: React.ReactNode
  defaultOpen?: boolean
}

const stats = [
  { label: 'Correlation Coefficient', value: '0.542', unit: 'p < 0.001' },
  { label: 'Effect Size', value: '29.4%', unit: 'of variance' },
  { label: 'Sample Size', value: '1,250', unit: 'observations' },
  { label: 'Confidence Level', value: '95%', unit: 'CI' },
]

function GraphPlaceholder({ title, caption }: { title: string; caption: string }) {
  return (
    <Card>
      <h3 className="font-semibold text-foreground mb-4 flex items-center gap-2">
        <BarChart3 className="w-5 h-5 text-primary" aria-hidden="true" />
        {title}
      </h3>
      <div className="h-64 bg-muted rounded-lg flex items-center justify-center border border-dashed border-border" role="img" aria-label={`Chart: ${title}`}>
        <div className="text-center">
          <LineChart className="w-12 h-12 text-muted-foreground mx-auto mb-2" aria-hidden="true" />
          <p className="text-sm text-muted-foreground">Server-generated graph</p>
          <Skeleton className="h-32 w-full max-w-xs mx-auto mt-4" />
        </div>
      </div>
      <p className="text-xs text-muted-foreground mt-4">{caption}</p>
    </Card>
  )
}

export default function ReportViewer() {
  const [expandedSection, setExpandedSection] = useState<string | null>('executive')

  const sections: ReportSection[] = [
    {
      id: 'executive',
      title: 'Executive Summary',
      icon: FileText,
      defaultOpen: true,
      content: (
        <div className="bg-primary-50 border border-primary-200 rounded-lg p-4">
          <p className="text-foreground leading-relaxed">
            Our analysis of 1,250 patients reveals a significant positive correlation between age and
            systolic blood pressure (r = 0.542, p &lt; 0.001). This finding aligns with established
            cardiovascular research and suggests age-related vascular stiffening is a primary driver
            of increased blood pressure in this cohort.
          </p>
        </div>
      ),
    },
    {
      id: 'data',
      title: 'Data Overview',
      icon: ClipboardList,
      content: (
        <div className="space-y-4">
          <p className="text-foreground leading-relaxed">
            Dataset contains 1,250 observations with 12 variables. Mean age was 54.3 years (SD = 18.2).
            Mean systolic blood pressure was 128.4 mmHg (SD = 17.3).
          </p>
          <table className="w-full text-sm border border-border rounded-lg overflow-hidden">
            <thead className="bg-muted">
              <tr>
                <th className="px-4 py-2 text-left">Column</th>
                <th className="px-4 py-2 text-left">Type</th>
                <th className="px-4 py-2 text-left">Missing</th>
              </tr>
            </thead>
            <tbody>
              <tr className="border-t border-border"><td className="px-4 py-2">Age</td><td className="px-4 py-2">Numerical</td><td className="px-4 py-2">0%</td></tr>
              <tr className="border-t border-border"><td className="px-4 py-2">Blood_Pressure</td><td className="px-4 py-2">Numerical</td><td className="px-4 py-2">0.16%</td></tr>
            </tbody>
          </table>
        </div>
      ),
    },
    {
      id: 'methodology',
      title: 'Methodology',
      icon: FlaskConical,
      content: (
        <div className="space-y-3">
          <ul className="list-disc pl-5 space-y-2 text-foreground">
            <li>Pearson correlation coefficient — linear relationship between age and blood pressure</li>
            <li>Normality: Shapiro-Wilk test (p &gt; 0.05) — <span className="text-accent font-medium">Passed</span></li>
            <li>Linearity: visual inspection — <span className="text-accent font-medium">Passed</span></li>
            <li>Homoscedasticity: residual analysis — <span className="text-accent font-medium">Passed</span></li>
          </ul>
        </div>
      ),
    },
    {
      id: 'results',
      title: 'Results',
      icon: BarChart3,
      content: (
        <div className="space-y-4">
          <p className="text-foreground leading-relaxed">
            Pearson&apos;s r = 0.542, 95% CI [0.505, 0.577], t(1248) = 18.94, p &lt; 0.001.
            Age explains approximately 29.4% of the variance in blood pressure (R² = 0.294).
          </p>
          <div className="p-4 rounded-lg bg-primary-50 border border-primary-200">
            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
              <div><p className="text-xs text-primary-700 mb-1">Test</p><p className="font-semibold text-primary-900">Pearson r</p></div>
              <div><p className="text-xs text-primary-700 mb-1">Statistic</p><p className="font-semibold text-primary-900">t = 18.94</p></div>
              <div><p className="text-xs text-primary-700 mb-1">df</p><p className="font-semibold text-primary-900">1248</p></div>
              <div><p className="text-xs text-primary-700 mb-1">P-value</p><p className="font-semibold text-primary-900">p &lt; 0.001</p></div>
            </div>
          </div>
        </div>
      ),
    },
    {
      id: 'visualisations',
      title: 'Visualisations',
      icon: LineChart,
      content: (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
          <GraphPlaceholder title="Scatter Plot: Age vs Blood Pressure" caption="Trend line: r = 0.542, p &lt; 0.001" />
          <GraphPlaceholder title="Distribution: Blood Pressure" caption="Mean = 128.4 mmHg, SD = 17.3" />
        </div>
      ),
    },
    {
      id: 'interpretation',
      title: 'Interpretation',
      icon: Lightbulb,
      content: (
        <p className="text-foreground leading-relaxed">
          This positive correlation demonstrates a robust relationship between advancing age and increased
          blood pressure levels. A 10-year increase in age is associated with approximately 2.8 mmHg increase
          in systolic BP. These findings support current clinical guidelines recommending increased
          cardiovascular monitoring in older populations.
        </p>
      ),
    },
    {
      id: 'appendices',
      title: 'Appendices',
      icon: ScrollText,
      content: (
        <pre className="bg-muted p-4 rounded-lg overflow-x-auto text-xs font-mono text-foreground border border-border">
{`Pearson's product-moment correlation
data: age and blood_pressure
t = 18.942, df = 1248, p-value < 2.2e-16
alternative hypothesis: true correlation != 0
95 percent confidence interval: 0.5054 0.5766
sample estimates: cor = 0.5418`}
        </pre>
      ),
    },
  ]

  return (
    <AppShell activeNav="Reports" maxWidth="6xl">
      <div className="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-8 -mt-2">
        <div>
          <h1 className="text-2xl font-bold text-foreground">Cardiovascular Study Q1</h1>
          <p className="text-sm text-muted-foreground">Generated 2 hours ago • ID: AN-2024-0247</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" size="sm" disabled title="Coming in Phase 7">
            Share
          </Button>
          <Button size="sm" className="gap-2">
            <Download className="w-4 h-4" aria-hidden="true" />
            Download HTML
          </Button>
        </div>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        {stats.map((stat, i) => (
          <Card key={i} variant="stat">
            <p className="text-sm text-muted-foreground mb-2">{stat.label}</p>
            <div className="flex items-baseline gap-2">
              <p className="text-3xl font-bold text-primary">{stat.value}</p>
              <p className="text-sm text-muted-foreground">{stat.unit}</p>
            </div>
          </Card>
        ))}
      </div>

      <div className="space-y-4" role="region" aria-label="Report sections">
        {sections.map((section) => {
          const isOpen = expandedSection === section.id
          const Icon = section.icon

          return (
            <div key={section.id} className="border border-border rounded-lg overflow-hidden">
              <button
                type="button"
                onClick={() => setExpandedSection(isOpen ? null : section.id)}
                className="w-full flex items-center justify-between p-6 hover:bg-muted transition-colors bg-card text-left min-h-[44px]"
                aria-expanded={isOpen}
                aria-controls={`section-${section.id}`}
              >
                <div className="flex items-center gap-4">
                  <Icon className="w-5 h-5 text-primary" aria-hidden="true" />
                  <h2 className="text-lg font-semibold text-foreground">{section.title}</h2>
                </div>
                <ChevronDown
                  className={`w-5 h-5 text-muted-foreground transition-transform ${isOpen ? 'rotate-180' : ''}`}
                  aria-hidden="true"
                />
              </button>

              {isOpen && (
                <div
                  id={`section-${section.id}`}
                  className="px-6 py-4 border-t border-border bg-background"
                  role="region"
                  aria-labelledby={`heading-${section.id}`}
                >
                  {section.content}
                </div>
              )}
            </div>
          )
        })}
      </div>

      <Card className="mt-12">
        <h3 className="font-semibold text-foreground mb-4">Export options</h3>
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <Button variant="outline" className="justify-start" size="sm">
            <Download className="w-4 h-4 mr-2" aria-hidden="true" />
            Download HTML
          </Button>
          <Button variant="outline" className="justify-start" size="sm" disabled title="Coming in Phase 5">
            <BookOpen className="w-4 h-4 mr-2" aria-hidden="true" />
            Download Excel (coming soon)
          </Button>
        </div>
      </Card>
    </AppShell>
  )
}
