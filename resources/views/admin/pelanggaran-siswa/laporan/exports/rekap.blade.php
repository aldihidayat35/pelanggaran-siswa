<table>
    <thead>
        <tr>
            <th colspan="{{ count($headers) }}" style="text-align: center; font-weight: bold; font-size: 14px;">{{ $judul }}</th>
        </tr>
        <tr>
            @foreach($headers as $h)
                <th style="font-weight: bold; background-color: #f0f0f0; border: 1px solid #000; text-align: center;">{{ $h }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse($rows as $row)
            <tr>
                @foreach($row as $cell)
                    <td style="border: 1px solid #000;">{!! $cell !!}</td>
                @endforeach
            </tr>
        @empty
            <tr>
                <td colspan="{{ count($headers) }}" style="text-align: center; border: 1px solid #000;">Tidak ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>
