$word = New-Object -ComObject Word.Application
$word.Visible = $false
$word.DisplayAlerts = 0

function Test-Docx {
    param($path)
    try {
        $doc = $word.Documents.Open($path, $false, $true, $false)
        Write-Host "SUCCESS: $path"
        $doc.Close($false)
    } catch {
        Write-Host "FAILED: $path - $_"
    }
}

Test-Docx "c:\laragon\www\ult-fkip-unila\docs\skripsi\Lampiran_Pengolahan_Data_Lengkap_V4.docx"

$word.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($word) | Out-Null
