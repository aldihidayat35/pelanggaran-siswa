<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Status Penanganan</th>
            <th class="text-end">Jumlah</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($statusPenanganan as $item)
        <tr>
            <td>{{ $item->status_penanganan }}</td>
            <td class="text-end">{{ number_format($item->jumlah, 0, ',', '.') }}</td>
        </tr>
        @php $grandTotal += $item->jumlah; @endphp
        @endforeach
        <tr style="font-weight:bold">
            <td>Total Keseluruhan</td>
            <td class="text-end">{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
        @empty
        <tr><td colspan="2" class="text-center">Tidak ada data</td></tr>
        @endempty
    </tbody>
</table>
