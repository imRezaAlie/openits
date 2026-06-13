<!DOCTYPE html>
<html lang="en">
<head>
    <title>OpenITS | Unified API Workspace</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="OpenITS">
    <meta name="description" content="Design, debug, test, and document REST, GraphQL, gRPC, WebSocket, SSE, Socket.IO, and SOAP in one workspace.">
    <meta name="robots" content="index, follow">
    <!-- MOBILE SPECIFIC -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" type="image/png" href="{{URL::asset('images/favicon.png')}}">

    <!-- Style css -->
    <link class="main-css" href="{{asset('../../css/style.css')}} " rel="stylesheet">
    @stack('head-src')

</head>
<body>


<!--*******************
    Preloader start
********************-->
<div id="preloader">
    <div>
        <img src="{{URL::asset('images/logo/logo-full.png')}}" alt="">
    </div>
</div>
<!--*******************
    Preloader end
********************-->
<!--**********************************
    Main wrapper start
***********************************-->
<div id="main-wrapper">
@include('layouts.navbar')
@include('layouts.sidebar')
    @yield('body')

@include('layouts.copyright')
</div>
@stack('footer-src')
</body>
</html>
