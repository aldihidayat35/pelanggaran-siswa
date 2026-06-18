<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th style="width:50px">#</th>
            <th>Kategori</th>
            <th class="text-end">Jumlah Pelanggaran</th>
            <th class="text-end">Total Poin</th>
        </tr>
    </thead>
    <tbody>
        @php $totalPelanggaran = 0; $totalPoin = 0; @endphp
        @foreach($rekapKategori as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->kategori_nama }}</td>
            <td class="text-end">{{ number_format($item->jumlah_pelanggaran, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($item->total_poin, 0, ',', '.') }}</td>
        </tr>
        @php $totalPelanggaran += $item->jumlah_pelanggaran; $totalPoin += $item->total_poin; @endphp
        @endforeach
        <tr style="font-weight:bold">
            <td colspan="2">Total</td>
            <td class="text-end">{{ number_format($totalPelanggaran, 0, ',', '.') }}</td>
            <td class="text-end">{{ number_format($totalPoin, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="4" class="text-center">Tidak ada data</td></tr>
        @endempty
    </tbody>
</table>
