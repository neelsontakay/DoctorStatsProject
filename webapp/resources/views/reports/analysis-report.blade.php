<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $job->job_id }} — DoctorStats Report</title>
    <style>
        body { font-family: Georgia, 'Times New Roman', serif; color: #1f2937; margin: 0; background: #f8fafc; }
        .container { max-width: 960px; margin: 0 auto; padding: 2rem; background: #fff; }
        h1, h2, h3 { color: #0f172a; }
        h1 { border-bottom: 3px solid #2563eb; padding-bottom: 0.5rem; }
        section { margin-bottom: 2rem; }
        .meta { color: #64748b; font-size: 0.95rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border: 1px solid #e2e8f0; padding: 0.6rem 0.75rem; text-align: left; }
        th { background: #eff6ff; }
        .graph { margin: 1.5rem 0; text-align: center; }
        .graph img { max-width: 100%; border: 1px solid #e2e8f0; border-radius: 0.5rem; }
        .graph figcaption { color: #475569; margin-top: 0.5rem; font-size: 0.95rem; }
        .summary-box { background: #eff6ff; border-left: 4px solid #2563eb; padding: 1rem 1.25rem; }
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-top: 1rem; }
        .stat-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.5rem; padding: 0.75rem; }
        .stat-label { color: #64748b; font-size: 0.85rem; text-transform: capitalize; }
        .stat-value { font-weight: 700; margin-top: 0.25rem; }
        .badge { display: inline-block; padding: 0.15rem 0.5rem; border-radius: 999px; font-size: 0.8rem; background: #eff6ff; color: #1d4ed8; }
        .result-block { margin-top: 1.5rem; }
    </style>
</head>
<body>
<div class="container">
    <h1>DoctorStats Analysis Report</h1>
    <p class="meta">
        Job ID: <strong>{{ $job->job_id }}</strong> |
        Generated: {{ $generatedAt->toDayDateTimeString() }} |
        Analyst: {{ $job->user->name }}
    </p>

    <section>
        <h2>Executive Summary</h2>
        <div class="summary-box">
            <p>{{ $ai['executive_summary'] }}</p>
        </div>
    </section>

    <section>
        <h2>Objectives</h2>
        <p>{{ $job->objectives }}</p>
    </section>

    <section>
        <h2>Data Overview</h2>
        @if (! empty($profile))
            <p><strong>Rows analyzed:</strong> {{ $profile['row_count'] ?? 'N/A' }}</p>

            @foreach ($profile['descriptive_statistics'] ?? [] as $column => $stats)
                <div class="result-block">
                    <h3>{{ $column }}</h3>
                    @if (! empty($stats['frequency_table']))
                        <table>
                            <thead>
                            <tr>
                                <th>Value</th>
                                <th>Count</th>
                                <th>Percent</th>
                                <th>Cumulative %</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach ($stats['frequency_table'] as $row)
                                <tr>
                                    <td>{{ $row['value'] ?? '—' }}</td>
                                    <td>{{ $row['count'] ?? '—' }}</td>
                                    <td>{{ $row['percent'] ?? '—' }}</td>
                                    <td>{{ $row['cumulative_percent'] ?? '—' }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="stat-grid">
                            @foreach ($stats as $key => $value)
                                @if ($key !== 'frequency_table')
                                    <div class="stat-card">
                                        <div class="stat-label">{{ str_replace('_', ' ', $key) }}</div>
                                        <div class="stat-value">{{ is_array($value) ? json_encode($value) : ($value ?? '—') }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <p>No profile data available.</p>
        @endif
    </section>

    <section>
        <h2>Methodology</h2>
        <table>
            <thead>
            <tr>
                <th>Column</th>
                <th>Type</th>
                <th>Variable Role</th>
                <th>Description</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($job->columns as $column)
                <tr>
                    <td>{{ $column->column_name }}</td>
                    <td>{{ $column->data_type->value }}</td>
                    <td>{{ $column->variable_type->value }}</td>
                    <td>{{ $column->description ?? '—' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Frequency Analysis</h2>
        @forelse ($frequencyTests as $test)
            <div class="result-block">
                <h3>{{ $test->test_name }}</h3>
                @if (! empty($test->raw_output['frequency_table']))
                    <table>
                        <thead>
                        <tr>
                            <th>Value</th>
                            <th>Count</th>
                            <th>Percent</th>
                            <th>Cumulative %</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($test->raw_output['frequency_table'] as $row)
                            <tr>
                                <td>{{ $row['value'] ?? '—' }}</td>
                                <td>{{ $row['count'] ?? '—' }}</td>
                                <td>{{ $row['percent'] ?? '—' }}</td>
                                <td>{{ $row['cumulative_percent'] ?? '—' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @elseif (! empty($test->raw_output['contingency_table']))
                    <table>
                        <thead>
                        <tr>
                            <th></th>
                            @foreach (array_keys(reset($test->raw_output['contingency_table'])) as $columnHeader)
                                <th>{{ $columnHeader }}</th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @foreach ($test->raw_output['contingency_table'] as $rowHeader => $values)
                            <tr>
                                <th>{{ $rowHeader }}</th>
                                @foreach ($values as $count)
                                    <td>{{ $count }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        @empty
            <p>No frequency analysis results were recorded.</p>
        @endforelse
    </section>

    <section>
        <h2>Hypothesis Tests</h2>
        <table>
            <thead>
            <tr>
                <th>Test</th>
                <th>Statistic</th>
                <th>P-value</th>
                <th>Library</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($hypothesisTests as $test)
                <tr>
                    <td>{{ $test->test_name }}</td>
                    <td>{{ $test->test_statistic ?? '—' }}</td>
                    <td>
                        @if ($test->p_value !== null && $test->p_value < 0.001)
                            &lt; 0.001
                        @else
                            {{ $test->p_value ?? '—' }}
                        @endif
                    </td>
                    <td><span class="badge">{{ $test->parameters['library'] ?? 'unknown' }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No hypothesis tests were recorded.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    <section>
        <h2>Other Results</h2>
        <table>
            <thead>
            <tr>
                <th>Test</th>
                <th>Category</th>
                <th>Statistic</th>
                <th>P-value</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($otherTests as $test)
                <tr>
                    <td>{{ $test->test_name }}</td>
                    <td>{{ $test->test_category }}</td>
                    <td>{{ $test->test_statistic ?? '—' }}</td>
                    <td>{{ $test->p_value ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No additional statistical tests were recorded.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </section>

    @if (! empty($graphs))
        <section>
            <h2>Visualizations</h2>
            @foreach ($graphs as $graph)
                <figure class="graph">
                    <img
                        src="data:{{ $graph['mime_type'] ?? 'image/png' }};base64,{{ $graph['image_base64'] ?? '' }}"
                        alt="{{ $graph['title'] ?? 'Visualization' }}"
                    >
                    <figcaption>{{ $graph['caption'] ?? $graph['title'] ?? '' }}</figcaption>
                </figure>
            @endforeach
        </section>
    @endif

    <section>
        <h2>Interpretation</h2>
        <p>{{ $ai['interpretation'] }}</p>
        @if (! empty($ai['limitations']))
            <h3>Limitations</h3>
            <p>{{ $ai['limitations'] }}</p>
        @endif
        @if (! empty($ai['recommendations']))
            <h3>Recommendations</h3>
            <p>{{ $ai['recommendations'] }}</p>
        @endif
        <p class="meta">AI provider: {{ $ai['provider'] ?? 'unknown' }}</p>
    </section>
</div>
</body>
</html>
