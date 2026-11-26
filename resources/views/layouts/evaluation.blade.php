<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Service Evaluation')</title>
    <link rel="stylesheet" href="{{ asset('css/booking.css') }}">
    <style nonce="{{ request()->attributes->get('csp_nonce') }}">
        body.evaluation-page { margin: 0; min-width: 320px; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; }
    </style>
    @stack('styles')
    @include('partials.student-navigation-loader-head')
</head>
<body class="evaluation-page">
    @yield('content')

    <script src="{{ asset('js/student-csp-controls.js') }}?v={{ filemtime(public_path('js/student-csp-controls.js')) }}"></script>
    @stack('scripts')
    @include('partials.student-navigation-loader')
</body>
</html>
