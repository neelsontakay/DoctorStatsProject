@extends('layouts.app')

@section('title', 'New Analysis — DoctorStats')

@section('body')
    <div class="min-h-screen bg-background" x-data="analysisWorkflowPage" x-init="init">
        <a href="#wizard-content" class="skip-link">Skip to main content</a>

        <header class="border-b border-border bg-card sticky top-0 z-50">
            <div class="mx-auto max-w-5xl px-4 py-4 flex items-center justify-between">
                <x-logo subtitle="Create New Analysis" />
                <a href="{{ route('analyses.index') }}" class="btn btn-ghost btn-icon" aria-label="Close wizard">✕</a>
            </div>
        </header>

        <main id="wizard-content" class="mx-auto max-w-5xl px-4 py-12">
            <div class="flex items-center justify-between mb-8 overflow-x-auto">
                <template x-for="n in totalSteps" :key="n">
                    <div class="flex items-center flex-1 min-w-0">
                        <div class="flex flex-col items-center">
                            <div class="size-10 rounded-full flex items-center justify-center text-sm font-bold"
                                :class="displayStep >= n ? 'bg-primary text-primary-foreground' : 'bg-muted text-muted-foreground'"
                                x-text="n"></div>
                        </div>
                        <div x-show="n < totalSteps" class="flex-1 h-0.5 mx-2"
                            :class="displayStep > n ? 'bg-primary' : 'bg-border'"></div>
                    </div>
                </template>
            </div>

            <div class="card card-pad">
                <p x-show="error" x-text="error" class="alert-error mb-6"></p>

                {{-- Step 1: Upload --}}
                <div x-show="step === 1" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground mb-2">Upload your data</h2>
                        <p class="text-muted-foreground">Supported formats: Excel (.xlsx, .xls) or CSV. Maximum 50 MB per file.</p>
                    </div>

                    <template x-if="uploadState === 'empty'">
                        <div class="border-2 border-dashed border-border rounded-lg p-12 text-center hover:border-primary transition-colors"
                            @dragover.prevent @drop="onDrop($event)">
                            <p class="font-semibold text-foreground mb-2">Drag and drop your file here</p>
                            <p class="text-sm text-muted-foreground mb-4">or click to browse</p>
                            <label class="btn btn-primary cursor-pointer">
                                Select file
                                <input type="file" class="hidden" accept=".csv,.xlsx,.xls" @change="onFileSelect($event)">
                            </label>
                        </div>
                    </template>

                    <template x-if="uploadState === 'uploading'">
                        <div class="border border-border rounded-lg p-6 space-y-4">
                            <p class="font-semibold" x-text="fileName"></p>
                            <p class="text-sm text-muted-foreground">Uploading...</p>
                            <div class="h-2 bg-muted rounded-full overflow-hidden">
                                <div class="h-full bg-primary transition-all" :style="`width: ${uploadProgress}%`"></div>
                            </div>
                        </div>
                    </template>

                    <template x-if="uploadState === 'scanning'">
                        <div class="alert-info">Scanning file and validating structure...</div>
                    </template>

                    <template x-if="uploadState === 'ready'">
                        <div class="border border-border rounded-lg p-6 bg-muted space-y-4">
                            <div>
                                <p class="font-semibold text-foreground" x-text="fileName"></p>
                                <p class="text-sm text-accent mt-1">File ready for analysis</p>
                            </div>
                            <template x-if="sheets.length > 1">
                                <div>
                                    <label class="label">Sheet</label>
                                    <select x-model="selectedSheet" @change="changeSheet" class="input max-w-xs">
                                        <template x-for="sheet in sheets" :key="sheet">
                                            <option :value="sheet" x-text="sheet"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                            <template x-if="statsLoading">
                                <p class="text-sm text-muted-foreground">Computing dataset summary...</p>
                            </template>
                            <template x-if="!statsLoading && previewStats">
                                <details class="border border-border rounded-lg bg-card p-4" open>
                                    <summary class="cursor-pointer font-semibold text-foreground">Dataset summary</summary>
                                    <p class="text-sm text-muted-foreground mt-2" x-text="`${previewStats.row_count ?? 0} rows analyzed`"></p>
                                    <template x-for="column in previewStats.columns ?? []" :key="column.name">
                                        <div class="mt-4">
                                            <h4 class="text-sm font-semibold text-foreground" x-text="`${column.name} (${column.inferred_type})`"></h4>
                                            <template x-if="column.inferred_type === 'numerical'">
                                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2 text-sm">
                                                    <template x-for="(value, key) in column.descriptive_statistics ?? {}" :key="key">
                                                        <div class="rounded border border-border px-2 py-1">
                                                            <span class="text-muted-foreground capitalize" x-text="key.replaceAll('_', ' ')"></span>:
                                                            <span class="font-medium" x-text="value ?? '—'"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </template>
                                            <template x-if="column.inferred_type === 'categorical' && (column.frequency_table?.length ?? 0) > 0">
                                                <div class="overflow-x-auto mt-2">
                                                    <table class="w-full text-sm">
                                                        <thead>
                                                            <tr class="border-b border-border text-left text-muted-foreground">
                                                                <th class="py-1 pr-3">Value</th>
                                                                <th class="py-1 pr-3">Count</th>
                                                                <th class="py-1 pr-3">%</th>
                                                                <th class="py-1">Cumulative %</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <template x-for="row in column.frequency_table" :key="row.value">
                                                                <tr class="border-b border-border">
                                                                    <td class="py-1 pr-3" x-text="row.value"></td>
                                                                    <td class="py-1 pr-3" x-text="row.count"></td>
                                                                    <td class="py-1 pr-3" x-text="row.percent"></td>
                                                                    <td class="py-1" x-text="row.cumulative_percent"></td>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </details>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Step 2: Objectives --}}
                <div x-show="step === 2" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground mb-2">Analysis objectives</h2>
                        <p class="text-muted-foreground">Describe what you want to learn from this data (minimum 50 characters).</p>
                    </div>
                    <div>
                        <label class="label">Objectives</label>
                        <textarea x-model="objectives" rows="5" class="input" placeholder="e.g. Compare treatment outcomes between groups..."></textarea>
                        <p class="text-xs text-muted-foreground mt-1" x-text="`${objectives.length} / 50 minimum`"></p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="template in objectiveTemplates" :key="template">
                            <button type="button" class="btn btn-outline btn-sm" @click="applyTemplate(template)" x-text="template"></button>
                        </template>
                    </div>
                </div>

                {{-- Step 3: Column mapping --}}
                <div x-show="step === 3" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground mb-2">Column mapping</h2>
                        <p class="text-muted-foreground">Assign data types and variable roles for each column.</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-border text-left text-muted-foreground">
                                    <th class="py-2 pr-3">Column</th>
                                    <th class="py-2 pr-3">Data type</th>
                                    <th class="py-2 pr-3">Role</th>
                                    <th class="py-2">Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(col, index) in columns" :key="col.column_name">
                                    <tr class="border-b border-border">
                                        <td class="py-3 pr-3 font-medium" x-text="col.column_name"></td>
                                        <td class="py-3 pr-3">
                                            <select class="input" :value="col.data_type" @change="updateColumn(index, 'data_type', $event.target.value)">
                                                <template x-for="type in dataTypes" :key="type">
                                                    <option :value="type" x-text="type" :selected="col.data_type === type"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-3 pr-3">
                                            <select class="input" :value="col.variable_type" @change="updateColumn(index, 'variable_type', $event.target.value)">
                                                <template x-for="role in variableRoles" :key="role">
                                                    <option :value="role" x-text="role" :selected="col.variable_type === role"></option>
                                                </template>
                                            </select>
                                        </td>
                                        <td class="py-3">
                                            <input type="text" class="input" :value="col.description" @input="updateColumn(index, 'description', $event.target.value)" placeholder="Optional">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Step 4: Access (org only) --}}
                <div x-show="step === 4 && isOrgAccount" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground mb-2">Access control</h2>
                        <p class="text-muted-foreground">Choose who can view this analysis within your organisation.</p>
                    </div>
                    <div class="space-y-3">
                        <template x-for="option in accessOptions" :key="option.value">
                            <label class="flex items-start gap-3 p-4 rounded-lg border border-border cursor-pointer hover:bg-muted"
                                :class="accessScope === option.value ? 'border-primary bg-primary-50' : ''">
                                <input type="radio" name="access" :value="option.value" x-model="accessScope" class="mt-1">
                                <div>
                                    <p class="font-medium text-foreground" x-text="option.label"></p>
                                    <p class="text-sm text-muted-foreground" x-text="option.description"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Review step --}}
                <div x-show="step === reviewStep" class="space-y-6">
                    <div>
                        <h2 class="text-2xl font-bold text-foreground mb-2">Review and submit</h2>
                        <p class="text-muted-foreground">Confirm your analysis configuration before submitting.</p>
                    </div>
                    <dl class="grid gap-4 text-sm">
                        <div><dt class="font-medium text-muted-foreground">File</dt><dd x-text="fileName"></dd></div>
                        <div><dt class="font-medium text-muted-foreground">Objectives</dt><dd x-text="objectives"></dd></div>
                        <div><dt class="font-medium text-muted-foreground">Columns</dt><dd x-text="`${columns.length} mapped`"></dd></div>
                        <div x-show="isOrgAccount"><dt class="font-medium text-muted-foreground">Access</dt><dd class="capitalize" x-text="accessScope.replace('_', ' ')"></dd></div>
                    </dl>
                </div>

                <div class="flex justify-between mt-8 pt-6 border-t border-border">
                    <button type="button" class="btn btn-outline" @click="prevStep" x-show="step > 1">Back</button>
                    <div class="ml-auto flex gap-3">
                        <button type="button" class="btn btn-primary" @click="nextStep" x-show="step < reviewStep" :disabled="!canProceed()">Continue</button>
                        <button type="button" class="btn btn-primary" @click="submit" x-show="step === reviewStep" :disabled="submitting">
                            <span x-show="!submitting">Submit analysis</span>
                            <span x-show="submitting">Submitting...</span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>
@endsection
