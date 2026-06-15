# Native development setup (Windows, no Docker)

Use this when running Laravel with `php artisan serve` on port **8080** and analyzing the demo CSV locally.

## One-command start

From the repository root in PowerShell:

```powershell
.\scripts\start-native-dev.ps1
```

This opens **three terminal windows**:

| Window | Role |
|--------|------|
| Laravel | Web app at http://127.0.0.1:8080 |
| Queue worker | Builds reports after analysis completes (~1–2 min) |
| Stats service | Python statistical engine on port 8001 |

Native dev uses `QUEUE_CONNECTION=sync` so analysis **starts immediately** when you submit a job. Report generation is queued on the `database` connection and processed by the queue worker.

To stop services on those ports:

```powershell
.\scripts\stop-native-dev.ps1
```

## Prerequisites

- PHP 8.2+ with extensions used by Laravel
- Composer dependencies installed (`cd webapp && php composer.phar install` or `composer install`)
- Python 3.10+
- Node.js (optional; script builds assets if `public/build` is missing)

## Environment

`webapp/.env` must include (the start script does not overwrite your file; ensure these exist):

```env
APP_URL=http://127.0.0.1:8080
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=local

STATS_SERVICE_URL=http://127.0.0.1:8001
STATS_SERVICE_TOKEN=local-dev-token

DOCTORSTATS_PAYMENT_STUB=true
DOCTORSTATS_AI_STUB=true
```

`stats-service/.env` (created automatically by the start script if missing):

```env
STATS_DEBUG=true
STATS_SERVICE_TOKEN=local-dev-token
STATS_CALLBACK_TIMEOUT_SECONDS=300
```

## Analyze the demo CSV

### Demo account

| Field | Value |
|-------|--------|
| Email | `test@example.com` |
| Password | `password` |

Created by `php artisan migrate --seed` (run automatically by the start script).

### Demo file

Download: **http://127.0.0.1:8080/demo/clinical-trial-demo.csv**

Or copy from `docs/demo-data/clinical-trial-demo.csv`.

### Manual upload workflow

1. Open http://127.0.0.1:8080/login → **Demo sign in**
2. Go to **Analyses → New Analysis** (http://127.0.0.1:8080/analyses/create)
3. Upload `clinical-trial-demo.csv`
4. Objectives (minimum 50 characters), for example:

   > Compare post-treatment systolic blood pressure between Treatment and Control arms in this hypertension trial, and explore associations with age and cholesterol.

5. Column mapping:

   | Column | Role |
   |--------|------|
   | `patient_id` | identifier |
   | `age` | independent |
   | `sex` | control |
   | `treatment_group` | independent |
   | `baseline_systolic_bp` | control |
   | `post_treatment_systolic_bp` | **dependent** |
   | `cholesterol_mg_dl` | independent |

6. Submit → open the job page → wait for **completed** → **View Report**

### One-click demo

http://127.0.0.1:8080/analysis/demo — uploads the same CSV and mapping automatically.

## Verify services

| Check | URL |
|-------|-----|
| Web app | http://127.0.0.1:8080 |
| Stats health | http://127.0.0.1:8001/health |

Queue worker terminal should log lines like `Processing App\Jobs\StartAnalysisJob` after you submit an analysis.

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| Job stays **pending** | Queue worker or stats-service not running. Run `.\scripts\start-native-dev.ps1`, then `php artisan analysis:retry-pending` |
| Job shows **failed** after retries | Stats service was down or token mismatch — restart stack with start script, fix `.env` tokens, retry pending jobs |
| Job **failed** | Stats service not running; check http://127.0.0.1:8001/health |
| Login works but dashboard empty / 401 | Use http://127.0.0.1:8080 (not a different host/port) |
| Stats cannot download file | `APP_URL` must be `http://127.0.0.1:8080` so callbacks and file URLs match |
| No report after **completed** | Restart queue worker; check `webapp/storage/logs/laravel.log` |

## Manual start (three terminals)

If you prefer not to use the script:

```powershell
# Terminal 1
cd webapp
php artisan serve --host=127.0.0.1 --port=8080

# Terminal 2
cd webapp
php artisan queue:work --tries=3 --timeout=900

# Terminal 3
cd stats-service
.\.venv\Scripts\activate
$env:STATS_SERVICE_TOKEN="local-dev-token"
uvicorn app.main:app --host 127.0.0.1 --port 8001
```
