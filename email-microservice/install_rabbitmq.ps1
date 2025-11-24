# RabbitMQ Installation Script for Windows
# Run this script as Administrator

Write-Host "=== RabbitMQ Installation Script ===" -ForegroundColor Green
Write-Host "This script will help you install RabbitMQ and Erlang" -ForegroundColor Yellow
Write-Host ""

# Check if running as Administrator
if (-NOT ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")) {
    Write-Host "ERROR: This script must be run as Administrator!" -ForegroundColor Red
    Write-Host "Please right-click PowerShell and select 'Run as Administrator'" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✓ Running as Administrator" -ForegroundColor Green

# Check if Chocolatey is installed
Write-Host "Checking for Chocolatey..." -ForegroundColor Cyan
try {
    $chocoVersion = choco --version
    Write-Host "✓ Chocolatey found: $chocoVersion" -ForegroundColor Green
    
    Write-Host "Installing Erlang via Chocolatey..." -ForegroundColor Yellow
    choco install erlang -y
    
    Write-Host "Installing RabbitMQ via Chocolatey..." -ForegroundColor Yellow
    choco install rabbitmq -y
    
} catch {
    Write-Host "Chocolatey not found. Manual installation required." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Please follow these manual steps:" -ForegroundColor Cyan
    Write-Host "1. Download and install Erlang from: https://www.erlang.org/downloads" -ForegroundColor White
    Write-Host "2. Download and install RabbitMQ from: https://www.rabbitmq.com/download.html" -ForegroundColor White
    Write-Host "3. After installation, run these commands as Administrator:" -ForegroundColor White
    Write-Host "   rabbitmq-plugins enable rabbitmq_management" -ForegroundColor White
    Write-Host "   net start RabbitMQ" -ForegroundColor White
    Write-Host ""
}

# Check if RabbitMQ is now available
Write-Host "Checking RabbitMQ installation..." -ForegroundColor Cyan
try {
    $rabbitmqVersion = rabbitmq-server --version
    Write-Host "✓ RabbitMQ installed successfully: $rabbitmqVersion" -ForegroundColor Green
} catch {
    Write-Host "RabbitMQ not yet available. Please complete the installation." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "=== Installation Complete ===" -ForegroundColor Green
Write-Host "Next steps:" -ForegroundColor Cyan
Write-Host "1. Restart your terminal/PowerShell" -ForegroundColor White
Write-Host "2. Test RabbitMQ: rabbitmq-server --version" -ForegroundColor White
Write-Host "3. Start RabbitMQ service: net start RabbitMQ" -ForegroundColor White
Write-Host "4. Access management UI: http://localhost:15672" -ForegroundColor White
Write-Host "   Username: guest, Password: guest" -ForegroundColor White

Read-Host "Press Enter to exit"
