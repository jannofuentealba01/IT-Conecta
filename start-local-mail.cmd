@echo off
setlocal

powershell -NoProfile -ExecutionPolicy Bypass -Command ^
  "$root = '%~dp0'; $mailpit = Join-Path $root 'tools\mailpit\mailpit.exe'; $database = Join-Path $root 'tools\mailpit\mailpit.db'; if (-not (Test-Path -LiteralPath $mailpit)) { Write-Host 'No se encontro Mailpit en tools\mailpit.' -ForegroundColor Red; exit 1 }; $running = Get-NetTCPConnection -LocalAddress 127.0.0.1 -LocalPort 8025 -State Listen -ErrorAction SilentlyContinue; if (-not $running) { Start-Process -FilePath $mailpit -ArgumentList @('--listen','127.0.0.1:8025','--smtp','127.0.0.1:1025','--database',$database) -WindowStyle Hidden; Start-Sleep -Seconds 1 }; Start-Process 'http://127.0.0.1:8025'"

if errorlevel 1 (
    echo No fue posible iniciar el correo local.
    pause
    exit /b 1
)

echo Bandeja local disponible en http://127.0.0.1:8025
