# Instala hooks Git e template de commit neste repositório
$ErrorActionPreference = "Stop"
$root = Split-Path $PSScriptRoot -Parent

$hooksDir = Join-Path $root ".git\hooks"
$srcHook = Join-Path $root ".githooks\commit-msg"
$dstHook = Join-Path $hooksDir "commit-msg"
$gitmessage = Join-Path $root ".gitmessage"

if (-not (Test-Path $srcHook)) {
    Write-Error "Arquivo não encontrado: $srcHook"
}

New-Item -ItemType Directory -Force -Path $hooksDir | Out-Null
Copy-Item -Force $srcHook $dstHook

Push-Location $root
git config --local commit.template .gitmessage
Pop-Location

Write-Host "Hooks instalados com sucesso."
Write-Host "  - commit-msg: validação Conventional Commits"
Write-Host "  - commit.template: .gitmessage"
