Add-Type -Assembly "System.IO.Compression"
Add-Type -Assembly "System.IO.Compression.FileSystem"

$src        = Split-Path -Parent $PSScriptRoot
$dst        = "$HOME\Downloads\bookit-for-calcom.zip"
$pluginSlug = "bookit-for-calcom"

$excludeDirs  = @('.git', 'node_modules', '.claude', '.vscode', 'bin')
$excludeFiles = @('.gitignore', 'package.json', 'package-lock.json', 'CLAUDE.md', 'README.md')

if (Test-Path $dst) { Remove-Item $dst }

$stream = [System.IO.File]::Open($dst, [System.IO.FileMode]::Create)
$zip    = New-Object System.IO.Compression.ZipArchive($stream, [System.IO.Compression.ZipArchiveMode]::Create)

Get-ChildItem -Path $src -Recurse -File | Where-Object {
    $rel   = $_.FullName.Substring($src.Length + 1)
    $parts = $rel -split [regex]::Escape('\')
    $excluded = $false
    foreach ($part in $parts) {
        if ($excludeDirs -contains $part) { $excluded = $true; break }
    }
    if ($excludeFiles -contains $_.Name) { $excluded = $true }
    -not $excluded
} | ForEach-Object {
    $rel       = $_.FullName.Substring($src.Length + 1)
    $entryName = $pluginSlug + '/' + ($rel -replace '\\', '/')
    $entry     = $zip.CreateEntry($entryName, [System.IO.Compression.CompressionLevel]::Optimal)
    $entryStream = $entry.Open()
    $fileStream  = [System.IO.File]::OpenRead($_.FullName)
    $fileStream.CopyTo($entryStream)
    $fileStream.Dispose()
    $entryStream.Dispose()
}

$zip.Dispose()
$stream.Dispose()

$size = [math]::Round((Get-Item $dst).Length / 1KB, 1)
Write-Host "Done: $dst ($size KB)"
