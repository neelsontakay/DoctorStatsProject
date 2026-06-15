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
        pre { background: #f1f5f9; padding: 1rem; overflow-x: auto; border-radius: 0.5rem; }
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
            <pre>{{ json_encode($profile, JSON_PRETTY_PRINT) }}</pre>
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
        <h2>Results</h2>
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
            @forelse ($tests as $test)
                <tr>
                    <td>{{ $test->test_name }}</td>
                    <td>{{ $test->test_category }}</td>
                    <td>{{ $test->test_statistic ?? '—' }}</td>
                    <td>{{ $test->p_value ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">No statistical tests were recorded.</td>
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
