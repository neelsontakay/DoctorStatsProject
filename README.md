# DoctorStats

Statistical analysis platform for clinical data ("Analysis as a Service"). Doctors and researchers upload clinical datasets, specify objectives, and receive automated statistical analysis with AI-powered interpretation and publication-ready reports.

## Architecture

```
DoctorStatsProject/
├── docs/                          # Requirements, development plan, ERD, API contracts
├── webapp/                        # Laravel 12 web application (PHP 8.2+)
├── stats-service/                 # Python FastAPI statistical microservice
├── Sample doctor-stats-ui-design/ # UI design reference (Next.js prototype)
├── docker-compose.yml             # Full local development stack
└── .github/workflows/             # CI pipeline
```

| Layer | Technology |
|---|---|
| Backend | Laravel 12.x / PHP 8.2+ |
| Frontend | Tailwind CSS 3.x, Alpine.js, Vite |
| Statistical Engine | Python 3.10+ / FastAPI microservice |
| Database | MySQL 8.0+, Redis (cache, session, queues) |
| File Storage | S3-compatible (MinIO locally, AWS S3 / DO Spaces in production) |
| AI | Anthropic Claude (primary), Google Gemini (fallback) |
| Payments | Zoho Pay |
| Email | ZeptoMail (Mailpit catches mail locally) |

See [docs/Requirements.md](docs/Requirements.md), [docs/DoctorStats_Development_Plan.md](docs/DoctorStats_Development_Plan.md), [docs/DoctorStats_ERD.md](docs/DoctorStats_ERD.md), [docs/API_Reference.md](docs/API_Reference.md), and [docs/Production_Integrations.md](docs/Production_Integrations.md).

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (recommended path — runs the entire stack)
- For native development of individual services:
  - PHP 8.2+ and Composer 2.x
  - Node.js 20+ and npm
  - Python 3.10+

## Quick Start (Docker)

```bash
# 1. Copy environment file
cp webapp/.env.example webapp/.env

# 2. Build and start the full stack
docker compose up -d --build

# 3. One-time app setup
docker compose exec webapp php artisan key:generate
```

Services once running:

| Service | URL |
|---|---|
| Web app (Laravel) | http://localhost:8000 |
| Stats service (FastAPI) | http://localhost:8001 (docs at /docs) |
| MySQL | localhost:3306 |
| Redis | localhost:6379 |
| MinIO console | http://localhost:9001 |
| Mailpit UI | http://localhost:8025 |

## Native Development (Windows, no Docker)

To run analyses end-to-end (upload demo CSV → completed report) without Docker:

```powershell
.\scripts\start-native-dev.ps1
```

Then sign in at http://127.0.0.1:8080/login (`test@example.com` / `password`), upload the demo CSV from http://127.0.0.1:8080/demo/clinical-trial-demo.csv, or use **Analysis → Demo**.

Full guide: [docs/Native_Dev_Setup.md](docs/Native_Dev_Setup.md)

## Native Development (per service)

### webapp (Laravel)

```bash
cd webapp
composer install
cp .env.example .env
php artisan key:generate
npm install
npm run dev          # Vite dev server
php artisan serve    # http://localhost:8000
```

### stats-service (FastAPI)

```bash
cd stats-service
python -m venv .venv
.venv\Scripts\activate     # Windows (use source .venv/bin/activate on Unix)
pip install -r requirements.txt -r requirements-dev.txt
uvicorn app.main:app --reload --port 8001
```

## Testing & Linting

```bash
# PHP
cd webapp
vendor/bin/pint --test     # code style
php artisan test           # test suite

# Python
cd stats-service
ruff check .               # lint
pytest                     # test suite
```

## API Contract

The Laravel ↔ Python service contract is defined in [docs/api/laravel-python-contract.md](docs/api/laravel-python-contract.md). Both services must adhere to it; changes require updating the contract document first.

## Project Status

**MVP API backend implemented** — core flows are in place across Phases 1–8 of the [development plan](docs/DoctorStats_Development_Plan.md):

| Area | Status |
|---|---|
| Auth, profiles, organizations | Done |
| Data upload & analysis jobs | Done |
| Python stats service integration | Done |
| AI report generation (stub + real providers) | Done |
| Pay-per-job & subscriptions (checkout, renew, cancel) | Done |
| Report library, export, sharing | Done |
| Notifications & audit logging | Done |
| Laravel Blade/Alpine frontend UI | MVP shell (`/login`, `/register`, `/dashboard`) |
| Production hardening (Phase 9–11) | In progress |

Local development uses payment and AI **stub mode** by default. See [docs/Production_Integrations.md](docs/Production_Integrations.md) for Zoho Pay, Anthropic, and Gemini production setup.

### API highlights

```
POST /api/v1/auth/register|login
POST /api/v1/data-files
POST /api/v1/analysis-jobs
GET  /api/v1/analysis-jobs/{id}/status
GET  /api/v1/reports
POST /api/v1/subscriptions/checkout|renew|cancel
POST /api/v1/payments/checkout
```

Run the test suite: `cd webapp && php artisan test` (34 tests).

### Web UI (local)

```bash
cd webapp
npm run dev          # Vite assets
php artisan serve    # http://localhost:8000
```

Visit `/login` or `/dashboard` after signing in. The dashboard loads live data from `/api/v1/dashboard`.

### Demo clinical dataset

Download **[docs/demo-data/clinical-trial-demo.csv](docs/demo-data/clinical-trial-demo.csv)** (50-patient hypertension trial) or use the in-app flow:

1. Sign in: `test@example.com` / `password` (`php artisan migrate --seed`)
2. Open **http://localhost:8000/analysis/demo**
3. Click **Run demo analysis** — uploads the CSV, submits the job, and links to the report when complete

Requires the full Docker stack (webapp, stats-service, Redis queue worker). See [docs/demo-data/README.md](docs/demo-data/README.md).
