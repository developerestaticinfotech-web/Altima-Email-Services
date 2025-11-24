Param(
    [string]$PhpPath = "C:\xampp\php\php.exe",
    [string]$ProjectPath = "C:\xampp\htdocs\email\email-microservice",
    [string]$TaskName = "EmailRabbitMQListener",
    [string]$LogPath = "C:\xampp\htdocs\email\email-microservice\storage\logs\rabbitmq-listener.log"
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $PhpPath)) { throw "php.exe not found at '$PhpPath'" }
if (-not (Test-Path $ProjectPath)) { throw "Project path not found at '$ProjectPath'" }
$logDir = Split-Path -Path $LogPath -Parent
if (-not (Test-Path $logDir)) { New-Item -ItemType Directory -Path $logDir | Out-Null }

$arguments = "artisan rabbitmq:listen"
$action = New-ScheduledTaskAction -Execute $PhpPath -Argument $arguments -WorkingDirectory $ProjectPath

$trigger = New-ScheduledTaskTrigger -AtLogOn

$settings = New-ScheduledTaskSettingsSet -RestartCount 999 -RestartInterval (New-TimeSpan -Minutes 1) -AllowStartIfOnBatteries -DontStopIfGoingOnBatteries -ExecutionTimeLimit (New-TimeSpan -Days 7)

$principal = New-ScheduledTaskPrincipal -UserId "$env:USERNAME" -LogonType Interactive -RunLevel Highest

$description = "Consumes 'email.send' queue and sends emails. Auto-restarts on failure."

schtasks /Query /TN $TaskName /FO LIST | Out-Null
if ($LASTEXITCODE -eq 0) {
    schtasks /Delete /TN $TaskName /F | Out-Null
}

$task = New-ScheduledTask -Action $action -Trigger $trigger -Principal $principal -Settings $settings -Description $description
Register-ScheduledTask -TaskName $TaskName -InputObject $task | Out-Null

$wrapper = @"
@echo off
pushd "$ProjectPath"
"$PhpPath" artisan rabbitmq:listen >> "$LogPath" 2>&1
"@
$wrapperPath = Join-Path $ProjectPath "start-rabbitmq-listener.bat"
Set-Content -Path $wrapperPath -Value $wrapper -Encoding ASCII

Write-Host "Scheduled Task '$TaskName' registered."
Write-Host "Manual start wrapper created at: $wrapperPath"
Write-Host "Logs will be written to: $LogPath"
