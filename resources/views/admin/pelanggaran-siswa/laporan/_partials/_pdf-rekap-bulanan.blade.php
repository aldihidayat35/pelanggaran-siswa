<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Periode</th>
            <th class="text-end">Jumlah Pelanggaran</th>
        </tr>
    </thead>
    <tbody>
        @php $grandTotal = 0; @endphp
        @foreach($bulanan as $row)
        <tr>
            <td>{{ $row->periode }}</td>
            <td class="text-end">{{ number_format($row->total, 0, ',', '.') }}</td>
        </tr>
        @php $grandTotal += $row->total; @endphp
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
