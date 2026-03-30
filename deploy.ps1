# deploy.ps1 — Deploy BookIt for Cal.com to WordPress.org SVN
# Usage: .\deploy.ps1 [-Version "1.0.0"] [-SvnDir "C:\svn\bookit-for-cal-com"]

param(
    [string]$Version = "1.0.0",
    [string]$SvnDir  = "C:\svn\bookit-for-cal-com"
)

$PluginDir = $PSScriptRoot
$SvnTrunk  = "$SvnDir\trunk"
$SvnTag    = "$SvnDir\tags\$Version"

Write-Host "=== BookIt for Cal.com — Deploy v$Version ===" -ForegroundColor Cyan

# ── 1. Build ─────────────────────────────────────────────────────────────────
Write-Host "`n[1/5] Building assets..." -ForegroundColor Yellow
Push-Location $PluginDir
npm run build
if ($LASTEXITCODE -ne 0) { Write-Error "Build failed."; exit 1 }
Pop-Location

# ── 2. Checkout SVN (only once, skipped if already checked out) ───────────────
if (-not (Test-Path "$SvnDir\.svn")) {
    Write-Host "`n[2/5] Checking out SVN repo..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Force -Path $SvnDir | Out-Null
    svn checkout https://plugins.svn.wordpress.org/bookit-for-cal-com/ $SvnDir
    if ($LASTEXITCODE -ne 0) { Write-Error "SVN checkout failed."; exit 1 }
} else {
    Write-Host "`n[2/5] Updating SVN repo..." -ForegroundColor Yellow
    svn update $SvnDir
}

# ── 3. Sync files to trunk/ ───────────────────────────────────────────────────
Write-Host "`n[3/5] Syncing files to trunk/..." -ForegroundColor Yellow

# Clean trunk first
if (Test-Path $SvnTrunk) {
    Get-ChildItem $SvnTrunk | Remove-Item -Recurse -Force
}

# Files and folders to copy (explicit whitelist — no dev artifacts)
$includes = @(
    "bookit-for-calcom.php",
    "uninstall.php",
    "readme.txt",
    "CHANGELOG.md",
    "includes",
    "blocks",
    "elementor",
    "assets",
    "languages"
)

foreach ($item in $includes) {
    $src = "$PluginDir\$item"
    if (-not (Test-Path $src)) { continue }

    if (Test-Path $src -PathType Container) {
        # Copy folder, excluding dev artifacts
        $dst = "$SvnTrunk\$item"
        New-Item -ItemType Directory -Force -Path $dst | Out-Null
        robocopy $src $dst /E /XD "node_modules" ".git" "src" /XF "*.map" "*.swp" | Out-Null
    } else {
        Copy-Item $src -Destination $SvnTrunk -Force
    }
}

# ── 4. SVN add new files, remove deleted files ───────────────────────────────
Write-Host "`n[4/5] Updating SVN file list..." -ForegroundColor Yellow

# Add new files
svn status $SvnTrunk | Where-Object { $_ -match '^\?' } | ForEach-Object {
    $path = $_.Substring(8).Trim()
    svn add $path
}

# Remove deleted files
svn status $SvnTrunk | Where-Object { $_ -match '^!' } | ForEach-Object {
    $path = $_.Substring(8).Trim()
    svn delete $path
}

# ── 5. Create tag ─────────────────────────────────────────────────────────────
Write-Host "`n[5/5] Creating tag $Version..." -ForegroundColor Yellow
if (Test-Path $SvnTag) {
    Write-Host "Tag $Version already exists locally, skipping copy." -ForegroundColor DarkYellow
} else {
    svn copy $SvnTrunk $SvnTag
}

# ── Commit ────────────────────────────────────────────────────────────────────
Write-Host "`nReady to commit. Showing diff summary:" -ForegroundColor Cyan
svn status $SvnDir

Write-Host "`nCommitting to WordPress.org SVN..." -ForegroundColor Yellow
svn commit $SvnDir -m "Release $Version"

Write-Host "`nDone! v$Version deployed to WordPress.org." -ForegroundColor Green
