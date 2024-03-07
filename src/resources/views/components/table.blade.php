@once
    @sectionMissing('styles')
        <link href="{{ asset('/vendor/gridview/assets/css/bootstrap.min.css') }}" rel="stylesheet">
        <link rel="stylesheet" href="{{ asset('/vendor/gridview/assets/css/bootstrap-icons.min.css') }}">
    @endif

    @push('scripts')
        <script src="{{ asset('vendor/gridview/assets/js/bootstrap.bundle.min.js') }}"></script>
        <script src="{{ asset('vendor/gridview/assets/js/gridview.js') }}"></script>
        <script>
            gridView.init("#{{ $options['id'] }} [id*='filter_']", "{{ $filterUrl }}");
            gridView.apply();
        </script>
    @endpush
@endonce

<div @php echo \neoacevedo\gridview\Support\Html::renderTagAttributes($options) @endphp>
    {!! $section['{summary}'] !!}
    <table {!! $attributes !!}>
        {!! $section['{items}'] !!}
    </table>
    {!! $section['{pager}'] !!}
</div>
