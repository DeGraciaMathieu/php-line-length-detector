@inject('helper', App\Helper::class)
<div>
    <table>
        <thead>
            <tr>
                <th>total lines</th>
                <th>largest line</th>
                <th>average line</th>
            </tr>
        </thead>
        <tr>
            <td>{{ $lengthBag->count }}</td>
            <td>{{ $lengthBag->getMax() }}</td>
            <td>{{ $lengthBag->getAverage() }}</td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th>length</th>
                <th>occurrence</th>
                <th>percent</th>
            </tr>
        </thead>
        @foreach($lengthBag->thresholds->values as $threshold => $value)
        <tr>
            <td>> {{ $threshold }}</td>
            <td>{{ $value }}</td>
            <td>{{ $helper::numberFormat(
                $value * 100 / $lengthBag->count
            ) }} %</td>
        </tr>
        @endforeach()
    </table>
</div>