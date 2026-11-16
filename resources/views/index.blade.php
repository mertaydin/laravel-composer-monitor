@php
    $layout = config('composer-monitor.layout');
    $issues = [
        'outdated' => count($outdated),
        'advisories' => array_sum(array_map(fn ($item) => count($item['advisories'] ?? []), $advisories)),
        'abandoned' => count($abandoned),
    ];
@endphp
@if($layout)
    @extends($layout)
    @section('content')
        @include('composer-monitor::dashboard')
    @endsection
@else
    @include('composer-monitor::standalone')
@endif
