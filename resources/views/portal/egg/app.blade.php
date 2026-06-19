<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="{{ asset('files/img/sys/logoinogesticon.png') }}" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" type="text/css" href="{{ asset('templatelogin/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('templatelogin/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('templatelogin/css/iofrm-style.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('templatelogin/css/iofrm-theme3.css') }}">
    <link href="{{ asset('template/css/app.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('toastr.min.css') }}" />
    <title>Portal de Pedidos — M+D InoGest</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div id="app">
        <router-view></router-view>
    </div>

    <script src="{{ asset('templatelogin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('templatelogin/js/popper.min.js') }}"></script>
    <script src="{{ asset('templatelogin/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('templatelogin/js/main.js') }}"></script>
    <script src="{{ asset('template/js/app.js') }}"></script>
</body>
</html>
