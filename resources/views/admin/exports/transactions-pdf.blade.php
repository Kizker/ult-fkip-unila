<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi — ULT FKIP Unila</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif; font-size: 9pt; color: #1e293b; }
        .header { text-align: center; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #7c3aed; }
        .header h1 { font-size: 14pt; color: #7c3aed; margin-bottom: 4px; }
        .header p { font-size: 9pt; color: #64748b; }
        .meta { margin-bottom: 14px; font-size: 8.5pt; color: #475569; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th { background: #7c3aed; color: #fff; font-weight: 700; font-size: 8pt; text-transform: uppercase; letter-spacing: 0.03em; padding: 7px 8px; text-align: left; }
        td { padding: 6px 8px; border-bottom: 1px solid #e2e8f0; font-size: 8.5pt; }
        tr:nth-child(even) td { background: #f8fafc; }
        .footer { margin-top: 16px; text-align: center; font-size: 7.5pt; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Transaksi Permohonan</h1>
        <p>Unit Layanan Terpadu — FKIP Universitas Lampung</p>
    </div>

    <div class="meta">
        <strong>Periode:</strong> {{ $periodLabel }} &nbsp;&bull;&nbsp;
        <strong>Dicetak:</strong> {{ $generatedAt }}
    </div>

    @if($data->isEmpty())
        <p style="text-align: center; color: #94a3b8; padding: 40px 0;">Tidak ada data transaksi pada periode ini.</p>
    @else
        <table>
            <thead>
                <tr>
                    @foreach(array_keys($data->first()) as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data as $row)
                    <tr>
                        @foreach($row as $cell)
                            <td>{{ $cell }}</td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh sistem Web ULT FKIP Unila.
    </div>
</body>
</html>
