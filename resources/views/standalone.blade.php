<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('composer-monitor::messages.title') }}</title>
</head><body><div class="shell">
@include('composer-monitor::dashboard')
<div class="footer">Composer Monitor · {{ count($packages) }} dependencies scanned</div>
</div>
<script>
(function(){const root=document.documentElement;let theme=localStorage.getItem('composer-monitor-theme')||'system';function apply(){root.dataset.theme=theme==='system'?(matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light'):theme;const select=document.getElementById('theme-select');if(select)select.value=theme}window.setComposerTheme=function(value){theme=value;localStorage.setItem('composer-monitor-theme',theme);apply()};apply();})();
document.getElementById('package-search')?.addEventListener('input',function(){const q=this.value.toLocaleLowerCase();document.querySelectorAll('#packages-table tbody tr').forEach(row=>row.hidden=!row.textContent.toLocaleLowerCase().includes(q));});
</script></body></html>
