@php
    $issues = [
        'outdated' => count($outdated),
        'advisories' => array_sum(array_map(fn ($item) => count($item['advisories'] ?? []), $advisories)),
        'abandoned' => count($abandoned),
    ];
@endphp
@include('composer-monitor::standalone')
