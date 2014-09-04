<!DOCTYPE html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>

        <div class="container">

            @if(Session::has('message'))
                <p class="">{{ Session::get('message') }}</p>
            @endif

            @yield('content')

            @include('includes.footer')

        </div>

        <script src="/js/bootstrap.js"></script>

        <?php
            if (isset($extraJS) && count($extraJS) > 0) {
                foreach ($extraJS as $extraJSSource) {
                    echo "<script src=\"$extraJSSource\"></script>\n";
                }
            }
        ?>

    </body>
</html>

