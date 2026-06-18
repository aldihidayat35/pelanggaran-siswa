<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th style="width:50px">#</th>
            <th>Jenis Pelanggaran</th>
            <th style="width:100px">Tingkat</th>
            <th class="text-end">Jumlah</th>
            <th class="text-end">Total Poin</th>
        </tr>
    </thead>
    <tbody>
        @php $totalPelanggaran = 0; $totalPoin = 0; @endphp
        @foreach($rekapJenis as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->nama_pelanggaran }}</td>
            <td>{{ ucfirst($item->tingkat) }}</td>
            <td class="text-end">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($item->total_poin, 0, ',', '.') }}</td>
        </tr>
        @php $totalPelanggaran += $item->jumlah; $totalPoin += $item->total_poin; @endphp
        @endforeach
        <tr style="font-weight:bold">
            <td colspan="3">Total</td>
            <td class="text-end">{{ number_format($totalPelanggaran, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($totalPoin, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
        @endempty
    </tbody>
</table>
