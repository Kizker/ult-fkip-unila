$ErrorActionPreference = 'Stop'

$node = 'C:\laragon\bin\nodejs\node-v22.16.0-win-x64\node.exe'
$script = Join-Path $PSScriptRoot 'create-ppt-mobile-video-02.mjs'

if (-not (Test-Path $node)) {
    throw "Node tidak ditemukan: $node"
}

& $node $script
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}
