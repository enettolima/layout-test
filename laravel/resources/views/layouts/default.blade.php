<!doctype html>
<html>
<head>
<!-- my head section goes here -->
<!-- my css and js goes here -->
@include('layouts.header')
<script src="{{ asset('js/jquery-2.1.1.js') }}"></script>
</head>
<body>
  <div id="wrapper">

    @include('layouts.sidebar')
    @include('layouts.pagewrapper')

  </div>
<!-- custom scripts -->
<script src="{{ asset('js/passport/passport.js') }}"></script>
<!-- Mainly scripts -->
<script src="{{ asset('js/bootstrap.min.js') }}"></script>
<script src="{{ asset('js/plugins/metisMenu/jquery.metisMenu.js') }}"></script>
<script src="{{ asset('js/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>

<!-- Flot -->
<script src="{{ asset('js/plugins/flot/jquery.flot.js') }}"></script>
<script src="{{ asset('js/plugins/flot/jquery.flot.tooltip.min.js') }}"></script>
<script src="{{ asset('js/plugins/flot/jquery.flot.spline.js') }}"></script>
<script src="{{ asset('js/plugins/flot/jquery.flot.resize.js') }}"></script>
<script src="{{ asset('js/plugins/flot/jquery.flot.pie.js') }}"></script>

<!-- Peity -->
<script src="{{ asset('js/plugins/peity/jquery.peity.min.js') }}"></script>
<script src="{{ asset('js/demo/peity-demo.js') }}"></script>

<!-- Custom and plugin javascript -->
<script src="{{ asset('js/inspinia.js') }}"></script>
<script src="{{ asset('js/plugins/pace/pace.min.js') }}"></script>

<!-- Footable -->
<script src="{{ asset('js/plugins/footable/footable.all.min.js') }}"></script>

<!-- jQuery UI -->
<script src="{{ asset('js/plugins/jquery-ui/jquery-ui.min.js') }}"></script>

<!-- GITTER -->
<script src="{{ asset('js/plugins/gritter/jquery.gritter.min.js') }}"></script>

<!-- Sparkline -->
<script src="{{ asset('js/plugins/sparkline/jquery.sparkline.min.js') }}"></script>

<!-- Sparkline demo data  -->
<script src="{{ asset('js/demo/sparkline-demo.js') }}"></script>

<!-- ChartJS-->
<script src="{{ asset('js/plugins/chartJs/Chart.min.js') }}"></script>

<!-- Toastr -->
<script src="{{ asset('js/plugins/toastr/toastr.min.js') }}"></script>

<!-- Multi Step Modal -->
<script src="{{ asset('js/plugins/multistepmodal/multi-step-modal.js') }}"></script>

@if (isset($extra_js) && count($extra_js) > 0)
  @foreach ($extra_js as $js)
    <script src="{{ asset('')}}{{$js}}"></script>
  @endforeach
@endif

</body>
</html>
