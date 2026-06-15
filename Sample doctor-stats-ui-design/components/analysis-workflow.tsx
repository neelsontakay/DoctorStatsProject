'use client'

import {
  CheckCircle2,
  ChevronRight,
  FileSpreadsheet,
  Lock,
  Shield,
  Upload,
  Users,
  X,
} from 'lucide-react'
import { Fragment, useState } from 'react'
import { Alert } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Logo } from '@/components/ui/logo'
import { ProgressBar } from '@/components/ui/progress'
import { Select } from '@/components/ui/select'
import { Stepper } from '@/components/ui/stepper'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Textarea } from '@/components/ui/textarea'
import {
  DATA_TYPES,
  VARIABLE_ROLES,
  formatDataType,
  formatVariableRole,
  type AccessScope,
  type DataType,
  type VariableRole,
} from '@/lib/constants'
import { cn } from '@/lib/utils'

type UploadState = 'empty' | 'uploading' | 'scanning' | 'ready' | 'error'

type ColumnRow = {
  name: string
  dataType: DataType
  variableRole: VariableRole
  description: string
  unit: string
  missingPercent?: number
}

const INITIAL_COLUMNS: ColumnRow[] = [
  { name: 'Patient_ID', dataType: 'text', variableRole: 'identifier', description: '', unit: '' },
  { name: 'Age', dataType: 'numerical', variableRole: 'independent', description: 'Patient age', unit: 'yrs' },
  { name: 'Blood_Pressure', dataType: 'numerical', variableRole: 'dependent', description: 'Systolic BP', unit: 'mmHg', missingPercent: 3 },
  { name: 'Gender', dataType: 'categorical', variableRole: 'control', description: '', unit: '' },
]

const OBJECTIVE_TEMPLATES = [
  'Compare treatment outcomes between groups',
  'Identify risk factors for disease progression',
  'Assess correlation between biomarkers',
]

const ACCESS_OPTIONS: { value: AccessScope; label: string; description: string; icon: typeof Users }[] = [
  { value: 'all_members', label: 'All members', description: 'Everyone in your organisation can view this analysis', icon: Users },
  { value: 'specific_members', label: 'Specific members', description: 'Only selected team members can access', icon: Shield },
  { value: 'private', label: 'Private', description: 'Only you can view this analysis', icon: Lock },
]

export default function AnalysisWorkflow({ isOrgAccount = true }: { isOrgAccount?: boolean }) {
  const [step, setStep] = useState(1)
  const [uploadState, setUploadState] = useState<UploadState>('empty')
  const [uploadProgress, setUploadProgress] = useState(0)
  const [objectives, setObjectives] = useState('')
  const [columns, setColumns] = useState<ColumnRow[]>(INITIAL_COLUMNS)
  const [accessScope, setAccessScope] = useState<AccessScope>('all_members')
  const [selectedSheet, setSelectedSheet] = useState('Sheet1')

  const totalSteps = isOrgAccount ? 5 : 4
  const displayStep = isOrgAccount ? step : (step >= 4 ? step + 1 : step)

  const steps = [
    { number: 1, title: 'Upload Data', icon: Upload },
    { number: 2, title: 'Objectives', icon: FileSpreadsheet },
    { number: 3, title: 'Column Mapping', icon: FileSpreadsheet },
    ...(isOrgAccount ? [{ number: 4, title: 'Access', icon: Users }] : []),
    { number: isOrgAccount ? 5 : 4, title: 'Review', icon: CheckCircle2 },
  ]

  const handleFileSelect = () => {
    setUploadState('uploading')
    setUploadProgress(0)
    const interval = setInterval(() => {
      setUploadProgress((p) => {
        if (p >= 100) {
          clearInterval(interval)
          setUploadState('scanning')
          setTimeout(() => setUploadState('ready'), 1200)
          return 100
        }
        return p + 20
      })
    }, 300)
  }

  const updateColumn = (index: number, field: keyof ColumnRow, value: string) => {
    setColumns((prev) =>
      prev.map((col, i) => (i === index ? { ...col, [field]: value } : col)),
    )
  }

  const applyNumericalRole = () => {
    setColumns((prev) =>
      prev.map((col) =>
        col.dataType === 'numerical' && col.variableRole === 'identifier'
          ? col
          : col.dataType === 'numerical'
            ? { ...col, variableRole: 'independent' as VariableRole }
            : col,
      ),
    )
  }

  const objectivesValid = objectives.length >= 50
  const canProceed = () => {
    if (step === 1) return uploadState === 'ready'
    if (step === 2) return objectivesValid
    if (step === 3) return columns.every((c) => c.dataType && c.variableRole)
    return true
  }

  const reviewStep = isOrgAccount ? 5 : 4

  return (
    <div className="min-h-screen bg-background">
      <a href="#wizard-content" className="skip-link">Skip to main content</a>

      <header className="border-b border-border bg-card sticky top-0 z-50">
        <div className="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
          <Logo subtitle="Create New Analysis" />
          <Button variant="ghost" size="icon" aria-label="Close wizard">
            <X className="w-4 h-4" />
          </Button>
        </div>
      </header>

      <main id="wizard-content" className="mx-auto max-w-5xl px-4 py-12">
        <Stepper steps={steps} currentStep={displayStep} />

        <Card className="p-8">
          {/* Step 1: Upload */}
          {step === 1 && (
            <div className="space-y-6">
              <div>
                <h2 className="text-2xl font-bold text-foreground mb-2">Upload your data</h2>
                <p className="text-muted-foreground">
                  Supported formats: Excel (.xlsx, .xls) or CSV. Maximum 50 MB per file.
                </p>
              </div>

              {uploadState === 'empty' && (
                <div
                  className="border-2 border-dashed border-border rounded-lg p-12 text-center hover:border-primary transition-colors cursor-pointer"
                  onClick={handleFileSelect}
                  role="button"
                  tabIndex={0}
                  onKeyDown={(e) => e.key === 'Enter' && handleFileSelect()}
                >
                  <div className="flex justify-center mb-4">
                    <div className="w-16 h-16 rounded-lg bg-primary-100 flex items-center justify-center">
                      <Upload className="w-8 h-8 text-primary" aria-hidden="true" />
                    </div>
                  </div>
                  <p className="font-semibold text-foreground mb-2">Drag and drop your file here</p>
                  <p className="text-sm text-muted-foreground mb-4">or click to browse</p>
                  <Button onClick={(e) => { e.stopPropagation(); handleFileSelect() }}>
                    Select file
                  </Button>
                </div>
              )}

              {uploadState === 'uploading' && (
                <div className="border border-border rounded-lg p-6 space-y-4">
                  <div className="flex items-center gap-4">
                    <FileSpreadsheet className="w-10 h-10 text-primary" aria-hidden="true" />
                    <div className="flex-1">
                      <p className="font-semibold">patient_data.xlsx</p>
                      <p className="text-sm text-muted-foreground">Uploading...</p>
                    </div>
                  </div>
                  <ProgressBar value={uploadProgress} label="Upload progress" />
                </div>
              )}

              {uploadState === 'scanning' && (
                <Alert variant="info" title="Scanning file">
                  Running virus scan and validating file structure...
                </Alert>
              )}

              {uploadState === 'ready' && (
                <div className="space-y-4">
                  <div className="border border-border rounded-lg p-6 bg-muted">
                    <div className="flex items-start justify-between">
                      <div className="flex gap-4">
                        <FileSpreadsheet className="w-10 h-10 text-accent" aria-hidden="true" />
                        <div>
                          <p className="font-semibold text-foreground">patient_data.xlsx</p>
                          <p className="text-sm text-muted-foreground">2.4 MB • 1,250 rows • 12 columns</p>
                        </div>
                      </div>
                      <Button variant="ghost" size="icon" onClick={() => setUploadState('empty')} aria-label="Remove file">
                        <X className="w-4 h-4" />
                      </Button>
                    </div>
                  </div>

                  <div>
                    <Label htmlFor="sheet-select">Excel sheet</Label>
                    <Select id="sheet-select" value={selectedSheet} onChange={(e) => setSelectedSheet(e.target.value)}>
                      <option value="Sheet1">Sheet1 — Patient data</option>
                      <option value="Sheet2">Sheet2 — Lab results</option>
                    </Select>
                  </div>

                  <div>
                    <Label>Data preview (first 10 rows)</Label>
                    <div className="mt-2 overflow-x-auto rounded-lg border border-border">
                      <table className="w-full text-xs">
                        <thead className="bg-muted">
                          <tr>
                            {['Patient_ID', 'Age', 'Blood_Pressure', 'Gender'].map((h) => (
                              <th key={h} className="px-3 py-2 text-left font-semibold">{h}</th>
                            ))}
                          </tr>
                        </thead>
                        <tbody>
                          {[
                            ['P001', '45', '128', 'M'],
                            ['P002', '62', '142', 'F'],
                            ['P003', '38', '—', 'M'],
                          ].map((row, i) => (
                            <tr key={i} className="border-t border-border">
                              {row.map((cell, j) => (
                                <td key={j} className="px-3 py-2">{cell}</td>
                              ))}
                            </tr>
                          ))}
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              )}

              {uploadState === 'error' && (
                <Alert variant="error" title="Upload failed">
                  File exceeds 50 MB limit or format is not supported. Please upload .xlsx, .xls, or .csv.
                </Alert>
              )}
            </div>
          )}

          {/* Step 2: Objectives */}
          {step === 2 && (
            <div className="space-y-6">
              <div>
                <h2 className="text-2xl font-bold text-foreground mb-2">Describe your analysis</h2>
                <p className="text-muted-foreground">Tell us what you want to learn from this data.</p>
              </div>

              <div>
                <Label htmlFor="objectives" required>Analysis objectives</Label>
                <Textarea
                  id="objectives"
                  value={objectives}
                  onChange={(e) => setObjectives(e.target.value)}
                  placeholder="Minimum 50 characters. Example: We want to analyze the relationship between blood pressure and age in our patient cohort..."
                  error={objectives.length > 0 && !objectivesValid ? 'At least 50 characters required' : undefined}
                />
                <p className={cn('text-xs mt-1', objectivesValid ? 'text-accent' : 'text-muted-foreground')}>
                  {objectives.length}/50 characters minimum
                </p>
              </div>

              <div>
                <Label>Template suggestions</Label>
                <div className="flex flex-wrap gap-2 mt-2">
                  {OBJECTIVE_TEMPLATES.map((t) => (
                    <Button
                      key={t}
                      variant="outline"
                      size="sm"
                      onClick={() => setObjectives(t)}
                    >
                      {t}
                    </Button>
                  ))}
                </div>
              </div>
            </div>
          )}

          {/* Step 3: Column mapping */}
          {step === 3 && (
            <div className="space-y-6">
              <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <h2 className="text-2xl font-bold text-foreground mb-2">Column mapping</h2>
                  <p className="text-muted-foreground">Define data types and variable roles for each column.</p>
                </div>
                <Button variant="outline" size="sm" onClick={applyNumericalRole}>
                  Set all numerical as independent
                </Button>
              </div>

              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead className="sticky left-0 bg-muted z-10">Column</TableHead>
                    <TableHead>Data type</TableHead>
                    <TableHead>Variable role</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead>Unit</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {columns.map((col, i) => (
                    <Fragment key={col.name}>
                      <TableRow>
                        <TableCell className="font-medium sticky left-0 bg-card">{col.name}</TableCell>
                        <TableCell>
                          <Select
                            value={col.dataType}
                            onChange={(e) => updateColumn(i, 'dataType', e.target.value)}
                            aria-label={`Data type for ${col.name}`}
                          >
                            {DATA_TYPES.map((t) => (
                              <option key={t} value={t}>{formatDataType(t)}</option>
                            ))}
                          </Select>
                        </TableCell>
                        <TableCell>
                          <Select
                            value={col.variableRole}
                            onChange={(e) => updateColumn(i, 'variableRole', e.target.value)}
                            aria-label={`Variable role for ${col.name}`}
                          >
                            {VARIABLE_ROLES.map((r) => (
                              <option key={r} value={r}>{formatVariableRole(r)}</option>
                            ))}
                          </Select>
                        </TableCell>
                        <TableCell>
                          <Input
                            value={col.description}
                            onChange={(e) => updateColumn(i, 'description', e.target.value)}
                            placeholder="Optional"
                            aria-label={`Description for ${col.name}`}
                          />
                        </TableCell>
                        <TableCell>
                          <Input
                            value={col.unit}
                            onChange={(e) => updateColumn(i, 'unit', e.target.value)}
                            placeholder="e.g. mmHg"
                            aria-label={`Unit for ${col.name}`}
                          />
                        </TableCell>
                      </TableRow>
                      {col.missingPercent !== undefined && (
                        <TableRow>
                          <TableCell colSpan={5} className="py-2 bg-secondary-50">
                            <p className="text-xs text-secondary-800 flex items-center gap-1">
                              ⚠ {col.missingPercent}% missing values in {col.name} — will be handled during analysis
                            </p>
                          </TableCell>
                        </TableRow>
                      )}
                    </Fragment>
                  ))}
                </TableBody>
              </Table>

              <Alert variant="warning" title="Data quality note">
                2 missing values detected in Blood_Pressure. This is within normal range for clinical datasets.
              </Alert>
            </div>
          )}

          {/* Step 4: Access (org only) */}
          {isOrgAccount && step === 4 && (
            <div className="space-y-6">
              <div>
                <h2 className="text-2xl font-bold text-foreground mb-2">Access permissions</h2>
                <p className="text-muted-foreground">Choose who can view this analysis and its report.</p>
              </div>

              <div className="space-y-3" role="radiogroup" aria-label="Access scope">
                {ACCESS_OPTIONS.map(({ value, label, description, icon: Icon }) => (
                  <button
                    key={value}
                    type="button"
                    role="radio"
                    aria-checked={accessScope === value}
                    onClick={() => setAccessScope(value)}
                    className={cn(
                      'w-full flex items-start gap-4 p-4 rounded-lg border-2 text-left transition-all min-h-[44px]',
                      accessScope === value
                        ? 'border-primary bg-primary-50'
                        : 'border-border hover:border-primary/50',
                    )}
                  >
                    <Icon className={cn('w-5 h-5 mt-0.5', accessScope === value ? 'text-primary' : 'text-muted-foreground')} aria-hidden="true" />
                    <div>
                      <p className="font-semibold text-foreground">{label}</p>
                      <p className="text-sm text-muted-foreground">{description}</p>
                    </div>
                  </button>
                ))}
              </div>

              {accessScope === 'specific_members' && (
                <div>
                  <Label htmlFor="member-select">Select members</Label>
                  <Select id="member-select" multiple className="h-auto min-h-10">
                    <option value="1">James Okonkwo (Analyst)</option>
                    <option value="2">Maria Lopez (Viewer)</option>
                  </Select>
                  <p className="text-xs text-muted-foreground mt-1">Hold Ctrl/Cmd to select multiple members</p>
                </div>
              )}
            </div>
          )}

          {/* Step 5: Review */}
          {step === reviewStep && (
            <div className="space-y-6">
              <div>
                <h2 className="text-2xl font-bold text-foreground mb-2">Review and submit</h2>
                <p className="text-muted-foreground">Confirm your settings before starting the analysis.</p>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {[
                  { label: 'File', value: 'patient_data.xlsx' },
                  { label: 'Rows', value: '1,250' },
                  { label: 'Columns mapped', value: String(columns.length) },
                  { label: 'Access', value: isOrgAccount ? ACCESS_OPTIONS.find((o) => o.value === accessScope)?.label ?? '—' : 'Private' },
                ].map((item) => (
                  <div key={item.label} className="flex justify-between p-3 border border-border rounded-lg">
                    <span className="text-muted-foreground">{item.label}</span>
                    <span className="font-semibold text-foreground">{item.value}</span>
                  </div>
                ))}
              </div>

              <div className="p-4 rounded-lg border border-border bg-muted">
                <p className="text-xs text-muted-foreground mb-1">Objectives</p>
                <p className="text-sm text-foreground">{objectives || '—'}</p>
              </div>

              <Alert variant="success" title="Ready to submit">
                This analysis will use 1 credit from your Professional subscription (18 of 24 used).
              </Alert>
            </div>
          )}
        </Card>

        <div className="flex justify-between mt-8 gap-4">
          <Button
            variant="outline"
            onClick={() => setStep(Math.max(1, step - 1))}
            disabled={step === 1}
          >
            Back
          </Button>
          {step < reviewStep ? (
            <Button
              onClick={() => setStep(step + 1)}
              disabled={!canProceed()}
              className="gap-2"
            >
              Next <ChevronRight className="w-4 h-4" aria-hidden="true" />
            </Button>
          ) : (
            <Button className="gap-2">
              Submit analysis <ChevronRight className="w-4 h-4" aria-hidden="true" />
            </Button>
          )}
        </div>
      </main>
    </div>
  )
}
