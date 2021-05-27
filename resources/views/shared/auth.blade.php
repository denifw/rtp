<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{--<link rel="icon" href="{{asset('images/tck_logo.ico')}}" type="image/ico" />--}}

    <title>{{$app_name}}</title>
    <link rel="icon" href="{{asset('images/pavico.ico')}}" type="image/ico">
    <!-- Bootstrap -->
    <link href="{{asset('assets/vendors/bootstrap/dist/css/bootstrap.css')}}" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="{{asset('assets/vendors/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <!-- NProgress -->
    <link href="{{asset('assets/vendors/nprogress/nprogress.css')}}" rel="stylesheet">
    <!-- Animate.css -->
    <link href="{{asset('assets/vendors/animate.css/animate.min.css')}}" rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="{{mix('dist/css/custom.css')}}" rel="stylesheet">
</head>

<body class="login tile-dark-blue">
<div>
    <div class="login_wrapper">
        <div class="animate form login_form">
            <div class="login-logo">
                <img src="{{asset('images/login_form_web.png')}}" alt="Matalogix" style="width: 100px; height: auto;">
            </div>
            {{-- <div class="login-logo" style="margin-top: -15px;">
                <img src="{{asset('images/matalogix_text.png')}}" alt="Matalogix">
            </div> --}}
            <section class="login_content">

                @yield('content')
                <div class="separator">
                    <div class="clearfix"></div>
                    <br/>

                    <div>
                        <p>&copy;{{ date('Y') }} {{ $copyright }}. Privacy and Terms</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

</div>
<script type="application/javascript">
    setInterval(function () {
        window.location.reload();
    }, 60005);
</script>
</body>
</html>
