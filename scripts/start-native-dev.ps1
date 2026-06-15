# Starts Laravel, queue worker, and stats-service for local analysis on Windows.
# Usage: .\scripts\start-native-dev.ps1
#        .\scripts\start-native-dev.ps1 -Port 8080

param(
    [int]$Port = 8080,
    [int]$StatsPort = 8001,
    [string]$ServiceToken = "local-dev-token"
)

$ErrorActionPreference = "Stop"

$RepoRoot = (Resolve-Path (Join-Path $PSScriptRoot "..")).Path
$Webapp = Join-Path $RepoRoot "webapp"
$StatsService = Join-Path $RepoRoot "stats-service"
$VenvPython = Join-Path $StatsService ".venv\Scripts\python.exe"
$VenvPip = Join-Path $StatsService ".venv\Scripts\pip.exe"
$VenvUvicorn = Join-Path $StatsService ".venv\Scripts\uvicorn.exe"

function Require-Command {
    param([string]$Name)
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        Write-Error "Required command not found: $Name. Install it and run this script again."
    }
}

function Test-PortListening {
    param([int]$ListenPort)
    $conn = Get-NetTCPConnection -LocalPort $ListenPort -State Listen -ErrorAction SilentlyContinue
    return $null -ne $conn
}

Write-Host "DoctorStats native dev startup" -ForegroundColor Cyan
Write-Host "Repository: $RepoRoot"
Write-Host ""

Require-Command "php"
Require-Command "python"

if (-not (Test-Path (Join-Path $Webapp "artisan"))) {
    Write-Error "Laravel webapp not found at $Webapp"
}

# Python virtual environment for stats-service
if (-not (Test-Path $VenvPython)) {
    Write-Host "Creating Python virtual environment..." -ForegroundColor Yellow
    Push-Location $StatsService
    python -m venv .venv
    Pop-Location
}

Write-Host "Installing stats-service dependencies..." -ForegroundColor Yellow
& $VenvPip install -q -r (Join-Path $StatsService "requirements.txt")

$StatsEnvPath = Join-Path $StatsService ".env"
if (-not (Test-Path $StatsEnvPath)) {
    Write-Host "Creating stats-service/.env for native dev..." -ForegroundColor Yellow
    @"
STATS_DEBUG=true
STATS_SERVICE_TOKEN=$ServiceToken
STATS_CALLBACK_TIMEOUT_SECONDS=300
"@ | Set-Content -Path $StatsEnvPath -Encoding UTF8
}

# Database + demo user
Push-Location $Webapp
if (-not (Test-Path "database\database.sqlite")) {
    New-Item -ItemType File -Path "database\database.sqlite" -Force | Out-Null
}
Write-Host "Running migrations and seed (demo user)..." -ForegroundColor Yellow
php artisan config:clear 2>&1 | Out-Null
php artisan migrate --force --no-interaction 2>&1 | Out-Null
php artisan db:seed --force --no-interaction 2>&1 | Out-Null
if (-not (Test-Path "public\build\manifest.json")) {
    if (Get-Command "npm" -ErrorAction SilentlyContinue) {
        Write-Host "Building frontend assets..." -ForegroundColor Yellow
        npm run build 2>&1 | Out-Null
    }
}
Pop-Location

if ((Test-PortListening -ListenPort $Port) -or (Test-PortListening -ListenPort $StatsPort)) {
    Write-Host "Stopping existing services on ports $Port and $StatsPort..." -ForegroundColor Yellow
    & (Join-Path $PSScriptRoot "stop-native-dev.ps1") -Port $Port -StatsPort $StatsPort
    Start-Sleep -Seconds 2
}

$appUrl = "http://127.0.0.1:$Port"

Write-Host ""
Write-Host "Starting services in new terminal windows..." -ForegroundColor Green

# 1) Laravel web server
Start-Process powershell -ArgumentList @(
    "-NoExit",
    "-Command",
    @"
Set-Location '$Webapp'
`$Host.UI.RawUI.WindowTitle = 'DoctorStats — Laravel ($Port)'
Write-Host 'Laravel: $appUrl' -ForegroundColor Cyan
php artisan serve --host=127.0.0.1 --port=$Port
"@
)

# 2) Queue worker (generates reports after analysis callbacks)
Start-Process powershell -ArgumentList @(
    "-NoExit",
    "-Command",
    @"
Set-Location '$Webapp'
`$Host.UI.RawUI.WindowTitle = 'DoctorStats — Queue worker'
Write-Host 'Queue worker — builds reports after analysis completes' -ForegroundColor Cyan
php artisan queue:work database --tries=3 --timeout=900
"@
)

# 3) Stats service (statistical engine)
Start-Process powershell -ArgumentList @(
    "-NoExit",
    "-Command",
    @"
Set-Location '$StatsService'
`$Host.UI.RawUI.WindowTitle = 'DoctorStats — Stats service ($StatsPort)'
Write-Host 'Stats service: http://127.0.0.1:$StatsPort/health (reads stats-service/.env)' -ForegroundColor Cyan
& '$VenvUvicorn' app.main:app --host 127.0.0.1 --port $StatsPort
"@
)

Start-Sleep -Seconds 5

$statsOk = $false
$tokenOk = $false
try {
    $health = Invoke-RestMethod -Uri "http://127.0.0.1:$StatsPort/health" -TimeoutSec 10
    if ($health.status -eq "ok") { $statsOk = $true }
} catch {
    $statsOk = $false
}

if ($statsOk) {
    try {
        $probeBody = @{
            job_id = "startup-probe"
            file_url = "$appUrl/demo/clinical-trial-demo.csv"
            file_format = "csv"
            objectives = "Startup connectivity probe for DoctorStats native development environment."
            columns = @(@{
                name = "patient_id"
                index = 0
                data_type = "text"
                variable_type = "identifier"
            })
            callback_url = "$appUrl/internal/v1/analysis-callback"
        } | ConvertTo-Json -Depth 5
        $probe = Invoke-WebRequest -Uri "http://127.0.0.1:$StatsPort/api/v1/analyze" -Method POST `
            -Headers @{ "X-Service-Token" = $ServiceToken } `
            -ContentType "application/json" -Body $probeBody -UseBasicParsing -TimeoutSec 10
        if ($probe.StatusCode -eq 202) { $tokenOk = $true }
    } catch {
        $tokenOk = $false
        Write-Warning "Stats service token check failed. Ensure STATS_SERVICE_TOKEN=$ServiceToken in webapp/.env and stats-service/.env"
    }
}

Push-Location $Webapp
php artisan queue:retry all 2>&1 | Out-Null
php artisan analysis:retry-pending 2>&1 | ForEach-Object { Write-Host $_ }
Pop-Location

Write-Host ""
Write-Host "========================================" -ForegroundColor Green
Write-Host " Native dev stack started" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Green
Write-Host ""
Write-Host "  App:        $appUrl"
Write-Host "  Login:      $appUrl/login"
Write-Host "  New analysis: $appUrl/analyses/create"
Write-Host "  Demo CSV:   $appUrl/demo/clinical-trial-demo.csv"
Write-Host "  Stats API:  http://127.0.0.1:$StatsPort/health $(if ($statsOk) { '(ok)' } else { '(NOT RUNNING — analyses will stay pending)' })"
if ($statsOk) {
    Write-Host "  Stats token: $(if ($tokenOk) { 'ok' } else { 'MISMATCH — fix STATS_SERVICE_TOKEN in .env files' })"
}
Write-Host ""
Write-Host "  Demo sign-in:  test@example.com / password"
Write-Host ""
Write-Host "  To analyze the demo CSV:"
Write-Host "    1. Sign in and open New Analysis"
Write-Host "    2. Upload clinical-trial-demo.csv (or download from Demo CSV link)"
Write-Host "    3. Paste objectives (50+ chars), map columns, submit"
Write-Host "    4. Or use $appUrl/analysis/demo for one-click demo"
Write-Host ""
Write-Host "  Keep all three terminal windows open while analyzing."
Write-Host "  (Analysis starts inline; the queue worker builds the report afterward.)"
Write-Host ""
