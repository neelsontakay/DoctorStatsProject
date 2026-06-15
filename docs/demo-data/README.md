# Demo clinical dataset

## File

**[clinical-trial-demo.csv](clinical-trial-demo.csv)** — 50-patient synthetic hypertension trial.

| Column | Type | Role in analysis |
|---|---|---|
| `patient_id` | text | Identifier |
| `age` | numerical | Independent variable |
| `sex` | categorical | Demographics |
| `treatment_group` | categorical | Independent (Treatment vs Control) |
| `baseline_systolic_bp` | numerical | Baseline measure (mmHg) |
| `post_treatment_systolic_bp` | numerical | **Dependent** outcome (mmHg) |
| `cholesterol_mg_dl` | numerical | Independent (mg/dL) |

Treatment patients show lower post-treatment blood pressure than controls, so the stats engine can run group comparisons and correlations.

**Download in browser:**

- Docker: `http://localhost:8000/demo/clinical-trial-demo.csv`
- Native dev: `http://127.0.0.1:8080/demo/clinical-trial-demo.csv` (run `.\scripts\start-native-dev.ps1` first)

## Demo sign-in

| Field | Value |
|---|---|
| Email | `test@example.com` |
| Password | `password` |

Create the user if needed:

```bash
cd webapp
php artisan migrate --seed
```

## Run a full report (Docker — recommended)

```bash
# From repo root
docker compose up -d
docker compose exec webapp php artisan migrate --seed
```

1. Open **http://localhost:8000/login** and sign in.
2. Go to **http://localhost:8000/analysis/demo**.
3. Click **Run demo analysis** (uses the bundled CSV and column mapping).
4. When status is **completed**, open the report link on that page.

Requires `stats-service`, Redis (queue), and `DOCTORSTATS_PAYMENT_STUB=true` / `DOCTORSTATS_AI_STUB=true` (defaults in `.env.example`).

## Manual API flow (curl)

After sign-in, use the session cookie from the browser or:

```bash
# CSRF + login
curl -c cookies.txt -b cookies.txt http://localhost:8000/sanctum/csrf-cookie
curl -c cookies.txt -b cookies.txt -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Upload CSV
curl -c cookies.txt -b cookies.txt -X POST http://localhost:8000/api/v1/data-files \
  -F "file=@docs/demo-data/clinical-trial-demo.csv"

# Create analysis (replace DATA_FILE_ID from upload response)
curl -c cookies.txt -b cookies.txt -X POST http://localhost:8000/api/v1/analysis-jobs \
  -H "Content-Type: application/json" \
  -d @docs/demo-data/analysis-job-payload.json
```

See [analysis-job-payload.json](analysis-job-payload.json) for the column mapping template (`data_file_id` is a placeholder).
