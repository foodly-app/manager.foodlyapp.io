<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ka']) ? 'ltr' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="locale" content="{{ app()->getLocale() }}">
    <meta name="supported-locales" content="ka,en">
    <title>{{ __('login.title') }} - FOODLY For Partners</title>

    <!-- PrimeVue Icons -->
    <link rel="stylesheet" href="https://unpkg.com/primeicons/primeicons.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    <div id="app" data-page="login"></div>
</body>

</html>
