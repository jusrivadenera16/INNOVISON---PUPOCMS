<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Service Evaluation')</title>
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
    @stack('styles')
</head>
<body class="evaluation-page" style="margin:0; min-width:320px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    @yield('content')

    @stack('scripts')
</body>
</html>
