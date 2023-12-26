<x-dynamic-component :component="$getEntryWrapperView()" :entry="$entry">
    <div>
        <pre>
        {{ print_r($getState()) }}
        </pre>
    </div>
</x-dynamic-component>
