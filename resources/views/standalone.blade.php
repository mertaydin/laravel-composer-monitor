<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('composer-monitor::messages.title') }}</title>
</head><body><div class="shell">
@include('composer-monitor::dashboard')
<div class="footer">Composer Monitor · {{ count($packages) }} dependencies scanned</div>
</div>
</body></html>
