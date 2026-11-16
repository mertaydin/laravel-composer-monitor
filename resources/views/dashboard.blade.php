@php($routeName = config('composer-monitor.route.name', 'composer-monitor.'))
<header class="topbar">
    <div class="brand"><div class="mark">C</div><div><h1>{{ __('composer-monitor::messages.title') }}</h1><p class="sub">{{ __('composer-monitor::messages.subtitle') }}</p></div></div>
    <div class="actions">
        <span class="last">{{ __('composer-monitor::messages.last_checked') }} · {{ !empty($latest['checked_at']) ? \Illuminate\Support\Carbon::parse($latest['checked_at'])->diffForHumans() : __('composer-monitor::messages.never') }}</span>
        <form method="POST" action="{{ route($routeName.'check') }}">@csrf<button class="btn" type="submit" onclick="this.disabled=true;this.textContent='…';this.form.submit()">{{ __('composer-monitor::messages.check_now') }}</button></form>
    </div>
</header>
@if(session('status'))<div class="notice">{{ session('status') }}</div>@endif
@if(session('error'))<div class="notice error">{{ session('error') }}</div>@endif
@if(!empty($latest['error']))<div class="notice error">{{ __('composer-monitor::messages.error_title') }}: {{ $latest['error'] }}</div>@endif
<section class="stats">
    <article class="stat"><div class="stat-label"><span class="dot"></span>{{ __('composer-monitor::messages.packages') }}</div><div class="stat-value">{{ count($packages) }}</div><div class="stat-foot">{{ __('composer-monitor::messages.all_packages') }}</div></article>
    <article class="stat"><div class="stat-label"><span class="dot" style="background:var(--amber)"></span>{{ __('composer-monitor::messages.outdated') }}</div><div class="stat-value">{{ count($outdated) }}</div><div class="stat-foot">{{ __('composer-monitor::messages.updates_title') }}</div></article>
    <article class="stat"><div class="stat-label"><span class="dot" style="background:var(--red)"></span>{{ __('composer-monitor::messages.vulnerable') }}</div><div class="stat-value">{{ array_sum(array_map(fn ($item) => count($item['advisories'] ?? []), $advisories)) }}</div><div class="stat-foot">{{ __('composer-monitor::messages.security_title') }}</div></article>
    <article class="stat"><div class="stat-label"><span class="dot" style="background:var(--muted)"></span>{{ __('composer-monitor::messages.abandoned') }}</div><div class="stat-value">{{ count($abandoned) }}</div><div class="stat-foot">{{ __('composer-monitor::messages.abandoned_title') }}</div></article>
</section>
<section class="panel">
    <div class="panel-head"><h2>{{ __('composer-monitor::messages.updates_title') }} <span class="count">{{ count($outdated) }}</span></h2></div>
    <div class="table-wrap"><table><thead><tr><th>{{ __('composer-monitor::messages.package') }}</th><th>{{ __('composer-monitor::messages.installed') }}</th><th>{{ __('composer-monitor::messages.latest') }}</th><th>{{ __('composer-monitor::messages.type') }}</th><th>{{ __('composer-monitor::messages.release_date') }}</th></tr></thead><tbody>
    @forelse($outdated as $item)
        @php($package = $packages[$item['name']] ?? [])
        <tr><td><div class="pkg">{{ $item['name'] }}</div><div class="desc">{{ $package['description'] ?? $item['description'] ?? '' }}</div></td><td class="version">{{ $item['version'] }}</td><td><span class="pill amber">{{ $item['latest'] ?? '—' }}</span></td><td><span class="pill">{{ !empty($package['direct']) ? __('composer-monitor::messages.direct') : __('composer-monitor::messages.transitive') }}</span> @if(!empty($package['dev'])) <span class="pill">{{ __('composer-monitor::messages.development') }}</span> @endif</td><td class="muted">{{ !empty($item['latest_release_date']) ? \Illuminate\Support\Carbon::parse($item['latest_release_date'])->format('d.m.Y') : '—' }}</td></tr>
    @empty<tr><td colspan="5" class="empty">{{ __('composer-monitor::messages.no_updates') }}</td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="panel">
    <div class="panel-head"><h2>{{ __('composer-monitor::messages.security_title') }} <span class="count">{{ array_sum(array_map(fn ($item) => count($item['advisories'] ?? []), $advisories)) }}</span></h2></div>
    <div class="table-wrap"><table><thead><tr><th>{{ __('composer-monitor::messages.package') }}</th><th>{{ __('composer-monitor::messages.description') }}</th><th>{{ __('composer-monitor::messages.severity') }}</th><th>{{ __('composer-monitor::messages.affected') }}</th></tr></thead><tbody>
    @php($hasAdvisory = false)
    @foreach($advisories as $package)
        @foreach($package['advisories'] ?? [] as $advisory)
            @php($hasAdvisory = true)
            <tr><td class="pkg">{{ $package['name'] }}</td><td>@if(!empty($advisory['link']))<a class="link" href="{{ $advisory['link'] }}" target="_blank" rel="noopener">{{ $advisory['title'] }}</a>@else{{ $advisory['title'] }}@endif @if(!empty($advisory['cve']))<div class="muted">{{ $advisory['cve'] }}</div>@endif</td><td><span class="pill red">{{ strtoupper($advisory['severity'] ?? 'unknown') }}</span></td><td class="version">{{ $advisory['affected_versions'] ?? '—' }}</td></tr>
        @endforeach
    @endforeach
    @unless($hasAdvisory)<tr><td colspan="4" class="empty">{{ __('composer-monitor::messages.no_advisories') }}</td></tr>@endunless
    </tbody></table></div>
</section>
<section class="panel">
    <div class="panel-head"><h2>{{ __('composer-monitor::messages.abandoned_title') }} <span class="count">{{ count($abandoned) }}</span></h2></div>
    <div class="table-wrap"><table><thead><tr><th>{{ __('composer-monitor::messages.package') }}</th><th>{{ __('composer-monitor::messages.replacement') }}</th></tr></thead><tbody>
    @forelse($abandoned as $item)<tr><td class="pkg">{{ $item['name'] }}</td><td class="muted">{{ $item['replacement'] ?? '—' }}</td></tr>@empty<tr><td colspan="2" class="empty">{{ __('composer-monitor::messages.no_abandoned') }}</td></tr>@endforelse
    </tbody></table></div>
</section>
<section class="panel">
    <div class="panel-head"><div><h2>{{ __('composer-monitor::messages.all_packages') }} <span class="count">{{ count($packages) }}</span></h2></div><input id="package-search" class="search" type="search" placeholder="{{ __('composer-monitor::messages.search') }}" autocomplete="off"></div>
    <div class="table-wrap"><table id="packages-table"><thead><tr><th>{{ __('composer-monitor::messages.package') }}</th><th>{{ __('composer-monitor::messages.installed') }}</th><th>{{ __('composer-monitor::messages.type') }}</th><th>{{ __('composer-monitor::messages.description') }}</th></tr></thead><tbody>
    @forelse($packages as $package)<tr><td><div class="pkg">@if($package['url'])<a class="link" href="{{ $package['url'] }}" target="_blank" rel="noopener">{{ $package['name'] }}</a>@else{{ $package['name'] }}@endif</div></td><td class="version">{{ $package['version'] }}</td><td><span class="pill">{{ $package['direct'] ? __('composer-monitor::messages.direct') : __('composer-monitor::messages.transitive') }}</span> <span class="muted">{{ $package['dev'] ? __('composer-monitor::messages.development') : __('composer-monitor::messages.production') }}</span></td><td class="desc">{{ $package['description'] }}</td></tr>@empty<tr><td colspan="4" class="empty">{{ __('composer-monitor::messages.no_packages') }}</td></tr>@endforelse
    </tbody></table></div>
</section>
<script>
document.getElementById('package-search')?.addEventListener('input', function () {
    const query = this.value.toLocaleLowerCase();
    document.querySelectorAll('#packages-table tbody tr').forEach(row => {
        row.hidden = !row.textContent.toLocaleLowerCase().includes(query);
    });
});
</script>
