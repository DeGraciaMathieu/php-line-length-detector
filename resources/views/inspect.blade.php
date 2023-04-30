<div>
    <table>
        <thead>
        <tr>
            <th>largest line</th>
            <th>average line</th>
        </tr>
        </thead>
        <tr>
            <td>{{ $statistic->max() }}</td>
            <td>{{ $statistic->average() }}</td>
        </tr>
    </table>
    <table>
        <thead>
        <tr>
            <th>lenght</th>
            <th>occurrence</th>
        </tr>
        </thead>
        @foreach($distribution->steps() as $step => $value)
        <tr>
            <td>{{ $step }}</td>
            <td>{{ $value }}</td>
        </tr>
        @endforeach()
    </table>
</div>