<!DOCTYPE html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>
        @include('includes.header')
        <div class="container">
            @yield('content')

            @include('includes.footer')
        </div>
        <script src="/js/bootstrap.js"></script>
    </body>
</html>
