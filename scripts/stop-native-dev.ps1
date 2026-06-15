# Stops processes listening on Laravel and stats-service dev ports (Windows).
# Usage: .\scripts\stop-native-dev.ps1

param(
    [int]$Port = 8080,
    [int]$StatsPort = 8001
)

function Stop-ListenersOnPort {
    param([int]$ListenPort)

    $connections = Get-NetTCPConnection -LocalPort $ListenPort -State Listen -ErrorAction SilentlyContinue
    if (-not $connections) {
        Write-Host "Nothing listening on port $ListenPort"
        return
    }

    $pids = $connections | Select-Object -ExpandProperty OwningProcess -Unique
    foreach ($processId in $pids) {
        try {
            $proc = Get-Process -Id $processId -ErrorAction Stop
            Write-Host "Stopping $($proc.ProcessName) (PID $processId) on port $ListenPort"
            Stop-Process -Id $processId -Force
        } catch {
            Write-Warning "Could not stop PID $processId : $_"
        }
    }
}

Write-Host "Stopping DoctorStats native dev services..." -ForegroundColor Yellow
Stop-ListenersOnPort -ListenPort $Port
Stop-ListenersOnPort -ListenPort $StatsPort
Write-Host "Done. Close any remaining 'DoctorStats' PowerShell windows manually if needed."
