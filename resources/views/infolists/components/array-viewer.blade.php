<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <table style="width:100%; border-collapse: collapse;">
        <tr>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Name</th>
            <td style="border: 1px solid black; padding: 8px;">{{ $getState()['name'] }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Email</th>
            <td style="border: 1px solid black; padding: 8px;">{{ $getState()['email'] }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Phone</th>
            <td style="border: 1px solid black; padding: 8px;">{{ $getState()['phone'] }}</td>
        </tr>
        <tr>
            <th style="border: 1px solid black; padding: 8px; text-align: left;">Work Experience</th>
            <td style="border: 1px solid black; padding: 8px;">
                <table style="width:100%; border-collapse: collapse;">
                    <tr>
                        <th style="border: 1px solid black; padding: 8px; text-align: left;">Company</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: left;">Position</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: left;">Summary</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: left;">Start Date</th>
                        <th style="border: 1px solid black; padding: 8px; text-align: left;">End Date</th>
                    </tr>
                    @foreach ($getState()['worksExperience'] as $work)
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">{{ $work['company'] }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $work['summary'] }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $work['position'] }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $work['startDate'] }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $work['endDate'] }}</td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    </table>
</x-dynamic-component>
