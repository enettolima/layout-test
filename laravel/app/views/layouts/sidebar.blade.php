<!DOCTYPE html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>

        @include('includes.header');

        @include('includes.sidebar');

        @yield('content');

        @include('includes.footer');

    </body>
</html>

