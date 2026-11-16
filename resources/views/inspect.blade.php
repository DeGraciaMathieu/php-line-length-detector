<div>
    <table>
        <thead>
            <tr>
                <th>total lines</th>
                <th>largest line</th>
                <th>average line</th>
                <th>median</th>
                <th>p90</th>
                <th>p95</th>
            </tr>
        </thead>
        <tr>
            <td>{{ $lengthBag->getCount() }}</td>
            <td>{{ $lengthBag->getMax() }}</td>
            <td>{{ App\Helper::numberFormat($lengthBag->getAverage()) }}</td>
            <td>{{ $lengthBag->getMedian() }}</td>
            <td>{{ $lengthBag->getPercentile(90) }}</td>
            <td>{{ $lengthBag->getPercentile(95) }}</td>
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
            <td>{{ App\Helper::numberFormat($lengthBag->getThresholdPercent($threshold)) }} %</td>
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
    @if($fileBag)
    <table>
        <thead>
            <tr>
                <th>file</th>
                <th>lines</th>
                <th>largest line</th>
                <th>average line</th>
                @if($fileBag->hasMax())
                <th>exceeded</th>
                @endif
            </tr>
        </thead>
        @foreach($fileBag->getSummaries($top) as $summary)
        <tr>
            <td>{{ $summary->file }}</td>
            <td>{{ $summary->count }}</td>
            <td>{{ $summary->max }}</td>
            <td>{{ App\Helper::numberFormat($summary->average) }}</td>
            @if($fileBag->hasMax())
            <td>{{ $summary->exceeded }}</td>
            @endif
        </tr>
        @endforeach()
    </table>
    @endif
</div>