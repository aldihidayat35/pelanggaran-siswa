<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th style="width:40px">#</th>
            <th>Nama Siswa</th>
            <th class="text-center" style="width:80px">No. Hp Siswa</th>
            <th class="text-center" style="width:80px">Total Poin</th>
            <th class="text-center" style="width:100px">Status Pembinaan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($ranking as $item)
        <tr>
            <td class="text-center">{{ $loop->iteration }}</td>
            <td>{{ $item->nama }}</td>
            <td class="text-center">{{ $item->no_hp_siswa ?? '-' }}</td>
            <td class="text-end">{{ number_format($item->total_poin, 0, ',', '.') }}</td>
            <td>
                @php
                    $badgeColors = [
                        'Aman'                => 'success',
                        'Perhatian'           => 'info',
                        'Pembinaan'           => 'warning',
                        'Panggilan Orang Tua' => 'danger',
                        'Rekomendasi Tindakan Khusus' => 'danger',
                    ];
                    $color = $badgeColors[$item->status_pembinaan] ?? 'secondary';
                @endphp
                <span class="badge badge-light-{{ $color }}">{{ $item->status_pembinaan }}</span>
            </td>
        </tr>
        @endforeach
        @empty
        <tr><td colspan="5" class="text-center">Tidak ada data</td></tr>
        @endempty
    </tbody>
</table>
