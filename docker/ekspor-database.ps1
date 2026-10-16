[CmdletBinding()]
param(
    [string]$EnvPath = '.env'
)

$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$envFile = Join-Path $projectRoot $EnvPath
if (-not (Test-Path -LiteralPath $envFile)) { throw "File konfigurasi tidak ditemukan: $envFile. Salin .env.example menjadi .env dan isi konfigurasi database." }

$config = @{}
foreach ($line in Get-Content -LiteralPath $envFile) {
    if ($line -match '^\s*([A-Za-z_][A-Za-z0-9_]*)\s*=\s*(.*)\s*$') {
        $value = $Matches[2].Trim()
        if ($value.Length -ge 2 -and (($value.StartsWith('"') -and $value.EndsWith('"')) -or ($value.StartsWith("'") -and $value.EndsWith("'")))) {
            $value = $value.Substring(1, $value.Length - 2)
        }
        $config[$Matches[1]] = $value
    }
}
foreach ($key in @('DB_DATABASE', 'DB_USERNAME')) {
    if (-not $config.ContainsKey($key) -or [string]::IsNullOrWhiteSpace($config[$key])) { throw "Nilai $key tidak ditemukan di $EnvPath." }
}

$mysqlHost = if ($config['DB_HOST']) { $config['DB_HOST'] } else { '127.0.0.1' }
$mysqlPort = if ($config['DB_PORT']) { $config['DB_PORT'] } else { '3306' }
$databaseName = $config['DB_DATABASE']
$mysqlDump = Get-Command mysqldump.exe -ErrorAction SilentlyContinue
if (-not $mysqlDump) { $mysqlDump = Get-Command mysqldump -ErrorAction SilentlyContinue }
if (-not $mysqlDump) {
    $mysqlRoots = @()
    if ($env:LARAGON_ROOT) { $mysqlRoots += Join-Path $env:LARAGON_ROOT 'bin\mysql' }
    $mysqlRoots += 'D:\laragon\bin\mysql'
    foreach ($root in $mysqlRoots) {
        if (Test-Path -LiteralPath $root) {
            $candidate = Get-ChildItem -LiteralPath $root -Filter mysqldump.exe -Recurse -File -ErrorAction SilentlyContinue | Select-Object -First 1
            if ($candidate) { $mysqlDump = $candidate; break }
        }
    }
}
if (-not $mysqlDump) { throw 'mysqldump tidak ditemukan. Tambahkan folder MySQL ke PATH atau atur LARAGON_ROOT.' }
$mysqlDumpPath = if ($mysqlDump -is [System.IO.FileInfo]) { $mysqlDump.FullName } else { $mysqlDump.Path }
if ([string]::IsNullOrWhiteSpace($mysqlDumpPath)) { $mysqlDumpPath = $mysqlDump.Source }
if ([string]::IsNullOrWhiteSpace($mysqlDumpPath)) { throw 'Path executable mysqldump tidak dapat ditentukan.' }

$backupPath = Join-Path $projectRoot 'backup.sql'
$tempBackupPath = Join-Path $projectRoot 'backup.sql.tmp'
$optionPath = Join-Path $env:TEMP "marketplace-mysql-$([guid]::NewGuid().ToString('N')).cnf"
$password = if ($config.ContainsKey('DB_PASSWORD')) { $config['DB_PASSWORD'] } else { '' }
$escapedPassword = $password.Replace('\', '\\').Replace('"', '\"')
$optionText = "[client]`r`nhost=$mysqlHost`r`nport=$mysqlPort`r`nuser=$($config['DB_USERNAME'])`r`npassword=`"$escapedPassword`"`r`ndefault-character-set=utf8mb4`r`n"
[IO.File]::WriteAllText($optionPath, $optionText, [Text.UTF8Encoding]::new($false))

try {
    Write-Host "Mengekspor database '$databaseName' dari $mysqlHost`:$mysqlPort ..."
    & $mysqlDumpPath "--defaults-extra-file=$optionPath" --single-transaction --quick --add-drop-table --no-tablespaces --column-statistics=0 $databaseName "--result-file=$tempBackupPath"
    if ($LASTEXITCODE -ne 0) { throw 'Ekspor gagal. Periksa status MySQL dan kredensial database di .env.' }
    if (-not (Test-Path -LiteralPath $tempBackupPath) -or (Get-Item -LiteralPath $tempBackupPath).Length -eq 0) { throw 'File hasil ekspor kosong; backup.sql yang lama tidak diubah.' }
    Move-Item -LiteralPath $tempBackupPath -Destination $backupPath -Force
    Write-Host "Ekspor berhasil: $backupPath" -ForegroundColor Green
    Write-Host 'Pindahkan backup.sql ke folder proyek pada VM tujuan.'
}
finally {
    Remove-Item -LiteralPath $optionPath -Force -ErrorAction SilentlyContinue
    Remove-Item -LiteralPath $tempBackupPath -Force -ErrorAction SilentlyContinue
}
