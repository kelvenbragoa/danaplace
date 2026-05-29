<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="shortcut icon" href="/files/img/sys/logoinogesticon.png" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <title>Inogest</title>
    <link rel="stylesheet" type="text/css" href="{{asset('templatelogin/css/bootstrap.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('templatelogin/css/fontawesome-all.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('templatelogin/css/iofrm-style.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('templatelogin/css/iofrm-theme3.css')}}">
</head>
<body id="app">
        <router-view>
            <Login/>
        </router-view>
<script src="{{asset('templatelogin/js/jquery.min.js')}}"></script>
<script src="{{asset('templatelogin/js/popper.min.js')}}"></script>
<script src="{{asset('templatelogin/js/bootstrap.min.js')}}"></script>
<script src="{{asset('templatelogin/js/main.js')}}"></script>
</body>
</html>

{{-- 
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="Inogest MassMeters, sistema de gest���o">
	<meta name="author" content="M+D">
	<meta name="keywords" content="M+D">

	<link rel="shortcut icon" href="/files/img/sys/logoinogesticon.png" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])

	<title>Inogest</title>

	<link href="{{asset('template/css/app.css')}}" rel="stylesheet">
</head>


<body style="background-image: url('/files/img/sys/ie.jpg'); height: 100%; 
background-position: center;
background-repeat: no-repeat;
background-size: cover;">
	 <div id="app">
        <router-view>
            <Login/>
        </router-view>
    </div>

	<script src="{{asset('template/js/app.js')}}"></script>

</body>

</html>

 --}}
