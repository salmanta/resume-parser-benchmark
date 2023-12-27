<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        {{ $getState() }}
{{--        {!! $generateDiff() !!}--}}
        <h2>OpenAI vs Affinda</h2>
        <table style="width:100%; border-collapse: collapse;">
            @foreach ($compare_arrays($getOpenAi(), $getAffinda()) as $key => $value)
                @if (is_array($value))
                    <tr>
                        <th colspan="3" style="border: 1px solid black; padding: 8px; text-align: left;">
                            {{ ucwords(str_replace('.', ' ', $key)) }}
                        </th>
                    </tr>
                    @foreach ($value as $subKey => $subValue)
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">{{ ucwords($subKey) }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $subValue['expected'] ?? 'N/A' }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $subValue['actual'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="border: 1px solid black; padding: 8px;">{{ ucwords($key) }}</td>
                        <td style="border: 1px solid black; padding: 8px;" colspan="2">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

        <h2>OpenAI vs Daxtra</h2>
        <table style="width:100%; border-collapse: collapse;">
            @foreach ($compare_arrays($getOpenAi(), $getDaxtra()) as $key => $value)
                @if (is_array($value))
                    <tr>
                        <th colspan="3" style="border: 1px solid black; padding: 8px; text-align: left;">
                            {{ ucwords(str_replace('.', ' ', $key)) }}
                        </th>
                    </tr>
                    @foreach ($value as $subKey => $subValue)
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">{{ ucwords($subKey) }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $subValue['expected'] ?? 'N/A' }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $subValue['actual'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="border: 1px solid black; padding: 8px;">{{ ucwords($key) }}</td>
                        <td style="border: 1px solid black; padding: 8px;" colspan="2">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

        <h2>Affinda vs Daxtra</h2>
        <table style="width:100%; border-collapse: collapse;">
            @foreach ($compare_arrays($getAffinda(), $getDaxtra()) as $key => $value)
                @if (is_array($value))
                    <tr>
                        <th colspan="3" style="border: 1px solid black; padding: 8px; text-align: left;">
                            {{ ucwords(str_replace('.', ' ', $key)) }}
                        </th>
                    </tr>
                    @foreach ($value as $subKey => $subValue)
                        <tr>
                            <td style="border: 1px solid black; padding: 8px;">{{ ucwords($subKey) }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $subValue['expected'] ?? 'N/A' }}</td>
                            <td style="border: 1px solid black; padding: 8px;">{{ $subValue['actual'] ?? 'N/A' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td style="border: 1px solid black; padding: 8px;">{{ ucwords($key) }}</td>
                        <td style="border: 1px solid black; padding: 8px;" colspan="2">{{ $value }}</td>
                    </tr>
                @endif
            @endforeach
        </table>

    </div>
</x-dynamic-component>
