<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield ('title', 'ICCR Alumni Portal')</title>

    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack ('styles')
</head>
<body>
    @include ('components.navbar')
    {{-- HTML lives here --}}

    <main>
        @yield ('content')
    </main>

    @include ('components.footer')
    {{-- HTML lives here --}}

    <script src="{{ asset('js/navbar.js') }}"></script>
    @stack ('scripts')
</body>
</html>
