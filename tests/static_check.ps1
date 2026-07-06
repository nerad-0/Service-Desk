$ErrorActionPreference = "Stop"

$requiredFiles = @(
    "README.md",
    "SECURITY.md",
    "config/config.example.php",
    "database/schema.sql",
    "database/seed.sql",
    "public/index.html",
    "public/api/index.php",
    "public/assets/js/api.js",
    "public/assets/js/app.js",
    "public/assets/css/styles.css",
    "src/bootstrap.php",
    "src/Core/Database.php",
    "src/Core/Auth.php",
    "src/Core/Csrf.php",
    "src/Controllers/AuthController.php",
    "src/Controllers/TicketController.php",
    "src/Controllers/AdminController.php",
    "docs/API.md",
    "docs/TESTING.md"
)

foreach ($file in $requiredFiles) {
    if (-not (Test-Path -LiteralPath $file)) {
        throw "Missing required file: $file"
    }
}

if (Test-Path -LiteralPath "config/config.php") {
    throw "Local config/config.php must not be committed or distributed."
}

$phpFiles = Get-ChildItem -Recurse -Filter "*.php" | Where-Object { $_.FullName -notmatch "\\config\\config\.php$" }
$phpText = ($phpFiles | ForEach-Object { Get-Content -LiteralPath $_.FullName -Raw }) -join "`n"

if ($phpText -notmatch "new PDO") {
    throw "PDO connection was not found."
}

if ($phpText -notmatch "->prepare\(") {
    throw "Prepared statements were not found."
}

if ($phpText -notmatch "password_hash" -or $phpText -notmatch "password_verify") {
    throw "Password hashing or verification was not found."
}

if ($phpText -notmatch "X-CSRF-Token" -and (Get-Content -LiteralPath "src/Core/Csrf.php" -Raw) -notmatch "csrf_token") {
    throw "CSRF handling was not found."
}

if (Get-Command node -ErrorAction SilentlyContinue) {
    node --check "public/assets/js/api.js"
    node --check "public/assets/js/app.js"
}

Write-Host "Static project checks passed."

