<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        {{ $getState() }}
        {!! $generateDiff() !!}
    </div>
</x-dynamic-component>
