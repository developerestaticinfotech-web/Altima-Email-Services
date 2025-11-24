# RabbitMQ Setup Script for Windows
# Run this script as Administrator

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "RabbitMQ Setup for Windows" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Please right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✓ Running as Administrator" -ForegroundColor Green
Write-Host ""

# Check if Erlang is installed
Write-Host "[1/5] Checking Erlang installation..." -ForegroundColor Yellow
$erlangPaths = @(
    "C:\Program Files\Erlang OTP\bin\erl.exe",
    "C:\Program Files (x86)\Erlang OTP\bin\erl.exe"
)

$erlangFound = $false
foreach ($path in $erlangPaths) {
    if (Test-Path $path) {
        Write-Host "✓ Erlang found at: $path" -ForegroundColor Green
        $erlangFound = $true
        break
    }
}

if (-not $erlangFound) {
    Write-Host "✗ Erlang not found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install Erlang first:" -ForegroundColor Yellow
    Write-Host "1. Download from: https://www.erlang.org/downloads" -ForegroundColor White
    Write-Host "2. Install the Windows installer" -ForegroundColor White
    Write-Host "3. Run this script again" -ForegroundColor White
    Read-Host "Press Enter to exit"
    exit 1
}

# Check if RabbitMQ is installed
Write-Host ""
Write-Host "[2/5] Checking RabbitMQ installation..." -ForegroundColor Yellow
$rabbitmqService = Get-Service -Name "RabbitMQ" -ErrorAction SilentlyContinue

if ($rabbitmqService) {
    Write-Host "✓ RabbitMQ service found" -ForegroundColor Green
    
    # Check if service is running
    if ($rabbitmqService.Status -eq 'Running') {
        Write-Host "✓ RabbitMQ service is running" -ForegroundColor Green
    } else {
        Write-Host "⚠ RabbitMQ service is not running" -ForegroundColor Yellow
        Write-Host "Starting RabbitMQ service..." -ForegroundColor Yellow
        Start-Service -Name "RabbitMQ"
        Start-Sleep -Seconds 3
        if ((Get-Service -Name "RabbitMQ").Status -eq 'Running') {
            Write-Host "✓ RabbitMQ service started" -ForegroundColor Green
        } else {
            Write-Host "✗ Failed to start RabbitMQ service" -ForegroundColor Red
        }
    }
} else {
    Write-Host "✗ RabbitMQ not installed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install RabbitMQ:" -ForegroundColor Yellow
    Write-Host "1. Download from: https://www.rabbitmq.com/download.html" -ForegroundColor White
    Write-Host "2. Install the Windows installer" -ForegroundColor White
    Write-Host "3. Run this script again" -ForegroundColor White
    Read-Host "Press Enter to exit"
    exit 1
}

# Find RabbitMQ installation directory
Write-Host ""
Write-Host "[3/5] Finding RabbitMQ installation..." -ForegroundColor Yellow
$rabbitmqPaths = @(
    "C:\Program Files\RabbitMQ Server",
    "C:\Program Files (x86)\RabbitMQ Server"
)

$rabbitmqSbin = $null
foreach ($basePath in $rabbitmqPaths) {
    if (Test-Path $basePath) {
        $sbinPath = Get-ChildItem -Path $basePath -Directory -Filter "rabbitmq_server-*" | Select-Object -First 1
        if ($sbinPath) {
            $rabbitmqSbin = Join-Path $sbinPath.FullName "sbin"
            if (Test-Path $rabbitmqSbin) {
                Write-Host "✓ RabbitMQ found at: $rabbitmqSbin" -ForegroundColor Green
                break
            }
        }
    }
}

if (-not $rabbitmqSbin) {
    Write-Host "✗ Could not find RabbitMQ sbin directory" -ForegroundColor Red
    Write-Host "Please check your RabbitMQ installation" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

# Enable management plugin
Write-Host ""
Write-Host "[4/5] Enabling RabbitMQ management plugin..." -ForegroundColor Yellow
Set-Location $rabbitmqSbin
$pluginResult = & .\rabbitmq-plugins.bat enable rabbitmq_management 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Management plugin enabled" -ForegroundColor Green
} else {
    Write-Host "⚠ Plugin may already be enabled or there was an issue" -ForegroundColor Yellow
    Write-Host "Output: $pluginResult" -ForegroundColor Gray
}

# Test RabbitMQ status
Write-Host ""
Write-Host "[5/5] Testing RabbitMQ connection..." -ForegroundColor Yellow
$statusResult = & .\rabbitmqctl.bat status 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ RabbitMQ is running and responding" -ForegroundColor Green
} else {
    Write-Host "⚠ Could not get RabbitMQ status" -ForegroundColor Yellow
}

# Check if management UI is accessible
Write-Host ""
Write-Host "Testing management UI..." -ForegroundColor Yellow
try {
    $response = Invoke-WebRequest -Uri "http://localhost:15672" -TimeoutSec 3 -UseBasicParsing -ErrorAction Stop
    Write-Host "✓ Management UI is accessible at http://localhost:15672" -ForegroundColor Green
} catch {
    Write-Host "⚠ Management UI may not be ready yet. Wait a few seconds and try: http://localhost:15672" -ForegroundColor Yellow
}

# Summary
Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Setup Complete!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "RabbitMQ Information:" -ForegroundColor Yellow
Write-Host "  Management UI: http://localhost:15672" -ForegroundColor White
Write-Host "  Default Username: guest" -ForegroundColor White
Write-Host "  Default Password: guest" -ForegroundColor White
Write-Host "  AMQP Port: 5672" -ForegroundColor White
Write-Host ""
Write-Host "Next Steps:" -ForegroundColor Yellow
Write-Host "1. Update .env file with RabbitMQ configuration" -ForegroundColor White
Write-Host "2. Test connection from Laravel" -ForegroundColor White
Write-Host "3. Access management UI to monitor queues" -ForegroundColor White
Write-Host ""

Read-Host "Press Enter to exit"

