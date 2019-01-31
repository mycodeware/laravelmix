<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Channel Fight otp verification</title>

        <link href="{!! asset('css/app.css') !!}" media="all" rel="stylesheet" type="text/css" />
        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>
    <body>

        <div id="main" class="containerFull py-5">
           Hi {!! $data->to_name !!}, <br>
           Your verification otp is {!! $data->otp !!}.

        <footer class="containerFull">
            <br><br>
             <b>Regards</b><br>
             <p>Channel Fight</p>
        </footer>

        <script type="text/javascript" src="{!! asset('js/app.js') !!}"></script>
    </body>
</html>
