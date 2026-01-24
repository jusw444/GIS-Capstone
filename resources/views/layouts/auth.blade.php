<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Authentication</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">


    {{-- Google Font --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Custom Auth Styles --}}
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .auth-container {
            min-height: 100vh;
            background:
                linear-gradient(
                    rgba(0, 0, 0, 0.55),
                    rgba(0, 0, 0, 0.55)
                ),
                url('{{ asset('images/laguna_picture.jpeg') }}');
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(14px);
            border-radius: 18px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.25);
            overflow: hidden;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(12px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .auth-header {
            padding: 28px;
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            color: #b30000;
            border-bottom: 1px solid rgba(0,0,0,0.08);
        }

        .auth-body {
            padding: 30px 32px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 14px;
        }

        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(0, 60, 255, 0.15);
            border-color: #0037ff;
        }

        .btn-primary-auth {
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            background: linear-gradient(135deg, #b30000, #e53935);
            transition: all 0.25s ease;
        }

        .btn-primary-auth:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 20px rgba(179, 0, 0, 0.35);
        }

        .auth-links a {
            font-size: 13px;
            color: #b30000;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="auth-container">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>
