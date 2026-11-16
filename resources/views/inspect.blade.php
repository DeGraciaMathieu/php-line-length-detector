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
            <td>{{ $lengthBag->getCount() }}</td>
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
        @foreach($lengthBag->getThresholdCounts() as $threshold => $value)
        <tr>
            <td>> {{ $threshold }}</td>
            <td>{{ $value }}</td>
            <td>{{ App\Helper::numberFormat(
                $value * 100 / $lengthBag->getCount()
            ) }} %</td>
        </tr>
        @endforeach()
    </table>
    @if($exceededLineBag && ! $exceededLineBag->isEmpty())
    <table>
        <thead>
            <tr>
                <th>file</th>
                <th>line</th>
                <th>length</th>
            </tr>
        </thead>
        @foreach($exceededLineBag->getLongest($top) as $line)
        <tr>
            <td>{{ $line->file }}</td>
            <td>{{ $line->number }}</td>
            <td>{{ $line->length }}</td>
        </tr>
        @endforeach()
    </table>
    @endif
</div>