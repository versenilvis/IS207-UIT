Write-Host "Starting setup for Windows..." -ForegroundColor Cyan

# 1. Install Bun (via Powershell)
if (!(Get-Command bun -ErrorAction SilentlyContinue)) {
    Write-Host "Installing Bun..." -ForegroundColor Yellow
    powershell -c "irm bun.sh/install.ps1 | iex"
} else {
    Write-Host "Bun is already installed: $(bun --version)" -ForegroundColor Green
}

# 2. Install PHP (via Winget - có sẵn trên Windows 10/11)
if (!(Get-Command php -ErrorAction SilentlyContinue)) {
    Write-Host "Installing PHP via Winget..." -ForegroundColor Yellow
    winget install -e --id PHP.PHP
    Write-Host "Please restart your Terminal after this script finishes to recognize 'php' command." -ForegroundColor Red
} else {
    Write-Host "PHP is already installed." -ForegroundColor Green
}

# 3. Bun install in client folder
if (Test-Path "client") {
    Write-Host "Found 'client' folder. Running bun install..." -ForegroundColor Cyan
    Set-Location client
    & bun install
} else {
    Write-Host "Folder 'client' not found!" -ForegroundColor Red
}

Write-Host "Setup complete! Go build something awesome." -ForegroundColor Green
Pause