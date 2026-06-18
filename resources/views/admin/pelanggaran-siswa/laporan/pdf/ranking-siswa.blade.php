<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $tipeLabel }} - PDF</title>
    <style>
        @page { margin: 1cm 0.8cm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #000; }
        .header-wrap { text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 12px; }
        .header-wrap h2 { margin: 0 0 2px 0; font-size: 14px; }
        .header-wrap h3 { margin: 0; font-size: 13px; font-weight: normal; }
        .meta { margin-bottom: 10px; font-size: 10px; }
        .meta table { width: 100%; }
        .meta td { padding: 1px 0; }
        table.data { border-collapse: collapse; width: 100%; }
        table.data th, table.data td { border: 1px solid #444; padding: 4px 6px; font-size: 10px; vertical-align: top; }
        table.data th { background: #e8e8e8; font-weight: bold; text-align: center; }
        .center { text-align: center; }
        .right { text-align: right; }
        .empty { text-align: center; padding: 20px; font-style: italic; color: #666; }
        .rank-1 { background: #fff3cd; font-weight: bold; }
        .rank-2 { background: #e2e3e5; font-weight: bold; }
        .rank-3 { background: #f8d7da; font-weight: bold; }
        .footer { margin-top: 15px; font-size: 9px; text-align: right; color: #555; }
    </style>
</head>
<body>
    <div class="header-wrap">
        <h2>{{ $appName ?? config('app.name') }}</h2>
        <h3>{{ $tipeLabel }}</h3>
    </div>

    <div class="meta">
        <table>
            <tr>
                <td style="width:50%;">
                    <strong>Periode:</strong>
                    @if($tanggalMulai && $tanggalSelesai)
                        {{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} s.d. {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }}
                    @else
                        Semua data
                    @endif
                </td>
                <td style="width:50%; text-align:right;">
                    <strong>Dicetak:</strong> {{ \Carbon\Carbon::parse($generatedAt)->format('d/m/Y H:i') }}
                </td>
            </tr>
        </table>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:4%;">Peringkat</th>
                @foreach($headers as $h)
                    <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                @php $rank = $i + 1; @endphp
                <tr class="{{ $rank === 1 ? 'rank-1' : ($rank === 2 ? 'rank-2' : ($rank === 3 ? 'rank-3' : '')) }}">
                    <td class="center">{{ $rank }}</td>
                    @foreach($row as $cell)
                        <td>{{ $cell }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headers) + 1 }}" class="empty">Tidak ada data ranking siswa pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Halaman dicetak oleh sistem pada {{ \Carbon\Carbon::parse($generatedAt)->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
