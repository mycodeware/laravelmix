<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Channel Fight</title>

        <link href="{!! asset('css/app.css') !!}" media="all" rel="stylesheet" type="text/css" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
	<link href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.css" media="all" rel="stylesheet"  type="text/css"/>

    </head>
    <body>
        @section('header')
            <header>
                @include('partials.header')
            </header>
        @show

        <div id="main" class="containerFull py-5">

            @yield('content')

        </div>

        <footer>
            @include('partials.footer')
        </footer>

        <script type="text/javascript" src="{!! asset('js/app.js') !!}"></script>
    </body>
</html>
