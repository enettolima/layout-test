<!DOCTYPE html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>

        @include('includes.header')

        <div class="container">

            @if(Session::has('message'))
                <p class="">{{ Session::get('message') }}</p>
            @endif

            @yield('content')

            @include('includes.footer')

        </div>

        <!--<script src="/js/bootstrap.js"></script>-->
				<!-- <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>-->

        <?php
            if (isset($extraJS) && count($extraJS) > 0) {
                foreach ($extraJS as $extraJSSource) {
                    echo "<script src=\"$extraJSSource\"></script>\n";
                }
            }
        ?>

    </body>
</html>
