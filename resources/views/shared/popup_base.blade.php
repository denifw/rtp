<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <!-- Meta, title, CSS, favicons, etc. -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="{{asset('images/pavico.ico')}}" type="image/ico">

    <title> @yield('title') | {{$app_name}}</title>


    <link href="{{asset('assets/vendors/bootstrap/dist/css/bootstrap.css')}}" rel="stylesheet">
    <script src="{{asset('assets/vendors/moment/min/moment.min.js')}}"></script>
    <link href="{{asset('assets/vendors/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendors/nprogress/nprogress.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendors/bootstrap-progressbar/css/bootstrap-progressbar-3.3.4.min.css')}}"
          rel="stylesheet">
    <link href="{{asset('assets/vendors/jqvmap/dist/jqvmap.min.css')}}" rel="stylesheet">
    <link href="{{asset('assets/vendors/bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css')}}"
          rel="stylesheet">
    <link href="{{asset('assets/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.min.css')}}"
          rel="stylesheet">

    <!-- Custom Theme Style -->
    <link href="{{mix('dist/css/custom.css')}}" rel="stylesheet">
    @yield('custom-style')
</head>

<body class="nav-hd footer_fixed" onload="App.showLoading()">
<div id="mdl-loading" class="modal fade bs-example-modal-dialog" tabindex="-1" role="dialog" aria-hidden="true"
     data-keyboard="false" data-backdrop="static" style="z-index: 100000000">
    <div class="modal-dialog modal-dialog">
        <div class="modal-content">

            <div class="modal-header" style="text-align: center;">
                <h4 class="modal-title">Processing...</h4>
            </div>
            <div class="modal-body">
                <div id='PleaseWait' style="text-align: center"><img style="height: 300px"
                                                                     src='{{asset('images/spinner.gif')}}'/></div>
            </div>
        </div>
    </div>
</div>
<script src="{{asset('assets/vendors/jquery/dist/jquery.min.js')}}"></script>
<script src="{{asset('assets/vendors/bootstrap-datetimepicker/build/js/bootstrap-datetimepicker.min.js')}}"></script>
<script src="{{asset('assets/vendors/HighChart/highcharts.js')}}"></script>
<script src="{{asset('assets/vendors/HighChart/highcharts-more.js')}}"></script>
<script src="{{asset('assets/vendors/HighChart/modules/data.js')}}"></script>
<script src="{{asset('assets/vendors/HighChart/modules/drilldown.js')}}"></script>
<script src="{{asset('assets/vendors/HighChart/modules/solid-gauge.js')}}"></script>

<!-- Custom Theme Scripts -->
<script src="{{asset('assets/vendors/bootstrap/dist/js/bootstrap.min.js')}}"></script>
<script src="{{mix('dist/js/app.js')}}"></script>
<div class="container body" id="container_body">
    <div class="main_container">
        <!-- page content -->
        <div class="right_col" role="main">
            @yield('content')
        </div>
        <!-- /page content -->

        <!-- footer content -->
    @include('shared.footer')
    <!-- /footer content -->
    </div>
</div>

@yield('custom-script')
<script src="{{asset('assets/vendors/fastclick/lib/fastclick.js')}}"></script>
<script src="{{asset('assets/vendors/nprogress/nprogress.js')}}"></script>
<script src="{{asset('assets/vendors/Chart.js/dist/Chart.min.js')}}"></script>
<script src="{{asset('assets/vendors/gauge.js/dist/gauge.min.js')}}"></script>
<script src="{{asset('assets/vendors/bootstrap-progressbar/bootstrap-progressbar.min.js')}}"></script>
<script src="{{asset('assets/vendors/iCheck/icheck.min.js')}}"></script>
<script src="{{asset('assets/vendors/skycons/skycons.js')}}"></script>
<script src="{{asset('assets/vendors/Flot/jquery.flot.js')}}"></script>
<script src="{{asset('assets/vendors/Flot/jquery.flot.pie.js')}}"></script>
<script src="{{asset('assets/vendors/Flot/jquery.flot.time.js')}}"></script>
<script src="{{asset('assets/vendors/Flot/jquery.flot.stack.js')}}"></script>
<script src="{{asset('assets/vendors/Flot/jquery.flot.resize.js')}}"></script>
<script src="{{asset('assets/vendors/flot.orderbars/js/jquery.flot.orderBars.js')}}"></script>
<script src="{{asset('assets/vendors/flot-spline/js/jquery.flot.spline.min.js')}}"></script>
<script src="{{asset('assets/vendors/flot.curvedlines/curvedLines.js')}}"></script>
<script src="{{asset('assets/vendors/DateJS/build/date.js')}}"></script>
<script src="{{asset('assets/vendors/jqvmap/dist/jquery.vmap.js')}}"></script>
<script src="{{asset('assets/vendors/jqvmap/dist/maps/jquery.vmap.world.js')}}"></script>
<script src="{{asset('assets/vendors/jqvmap/examples/js/jquery.vmap.sampledata.js')}}"></script>
<script src="{{asset('assets/vendors/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<script src="{{asset('assets/vendors/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js')}}"></script>
<script src="{{mix('dist/js/custom.js')}}"></script>
<script type="application/javascript">
    setInterval(function () {
        App.checkPopupSession();
    }, 300005);
</script>
</body>
</html>
