<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Error')</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
            background-color: #f8f9fa;
            color: #333;
        }
        .error-container {
            max-width: 600px;
            margin: auto;
        }
        .error-image {
            width: 200px;
            height: auto;
            margin-bottom: 30px;
        }
        .error-code {
            font-size: 72px;
            font-weight: bold;
        }
        .error-title {
            font-size: 32px;
            margin: 10px 0;
        }
        .error-message {
            font-size: 18px;
            margin-bottom: 20px;
        }
        .home-btn {
            display: inline-block;
            padding: 10px 25px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .home-btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <img src="@yield('image', asset('images/error.png'))" alt="Error Image" class="error-image">
        <div class="error-code">@yield('code', 'Oops!')</div>
        <div class="error-title">@yield('title', 'Something went wrong')</div>
        <div class="error-message">@yield('message', 'The page you requested could not be found.')</div>
        <a href="{{ url('/') }}" class="home-btn">Go Home</a>
        @yield('content')
    </div>
</body>
</html>