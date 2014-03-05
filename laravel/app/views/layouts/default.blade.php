<!DOCTYPE html>
<html>
    <head>
        @include('includes.head')
    </head>
    <body>

        @include('includes.header')

        <div class="container">

            @if(Session::has('message'))
                <p class="bg-primary">{{ Session::get('message') }}</p>
            @endif

            @yield('content')

            @include('includes.footer')

        </div>

        <script src="/js/bootstrap.js"></script>

    </body>
</html>
