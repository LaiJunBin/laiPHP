<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield(title)</title>
    <link rel="stylesheet" href="{{ url('./public/assets/css/main.css') }}">
</head>
<body>
    @yield(main)
    <div>
        <a href="https://github.com/LaiJunBin/laiPHP" class="btn">View Source</a>
    </div>
</body>
</html>