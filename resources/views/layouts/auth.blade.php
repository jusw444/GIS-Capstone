<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }} - Authentication</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --red-primary:   #C0282B;
            --red-dark:      #8B0000;
            --red-light:     #FDECEA;
            --red-mid:       #E53935;
            --white:         #FFFFFF;
            --off-white:     #FAF8F8;
            --text-dark:     #1C1C1C;
            --text-muted:    #7A7A7A;
            --border:        #EDE8E8;
            --input-bg:      #FFF5F5;
            --shadow-card:   0 24px 64px rgba(192,40,43,0.10);
            --shadow-btn:    0 8px 24px rgba(192,40,43,0.35);
        }

        html, body {
            height: 100%;
            font-family: 'DM Sans', sans-serif;
            background: var(--off-white);
        }

        /* ─── WRAPPER ─────────────────────────────────── */
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            overflow: hidden;
        }

        /* ─── LEFT PANEL ──────────────────────────────── */
        .auth-left {
            flex: 0 0 50%;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 48px;
            overflow: hidden;

            /* slide-in from left */
            animation: slideInLeft 0.75s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slideInLeft {
            from { transform: translateX(-100%); opacity: 0; }
            to   { transform: translateX(0);     opacity: 1; }
        }

        /* map background */
        .auth-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url('{{ asset("images/laguna_picture.jpeg") }}') center/cover no-repeat;
            filter: brightness(0.38) saturate(0.7);
            z-index: 0;
        }

        /* red gradient overlay */
        .auth-left::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                145deg,
                rgba(139,0,0,0.72) 0%,
                rgba(192,40,43,0.55) 50%,
                rgba(229,57,53,0.30) 100%
            );
            z-index: 1;
        }

        .auth-left-content {
            position: relative;
            z-index: 2;
            text-align: center;
            color: var(--white);
        }

        /* globe icon badge */
        .brand-icon {
            width: 72px;
            height: 72px;
            background: rgba(255,255,255,0.15);
            border: 2px solid rgba(255,255,255,0.30);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 28px;
            font-size: 30px;
            backdrop-filter: blur(8px);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(255,255,255,0.25); }
            50%       { box-shadow: 0 0 0 14px rgba(255,255,255,0); }
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(22px, 3vw, 34px);
            font-weight: 700;
            line-height: 1.25;
            letter-spacing: -0.3px;
            margin-bottom: 14px;
            text-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }

        .brand-subtitle {
            font-size: 14px;
            font-weight: 300;
            opacity: 0.78;
            letter-spacing: 0.4px;
            margin-bottom: 40px;
            max-width: 300px;
            line-height: 1.7;
        }

        /* divider line */
        .brand-divider {
            width: 48px;
            height: 2px;
            background: rgba(255,255,255,0.45);
            border-radius: 2px;
            margin: 0 auto 36px;
        }

        /* homepage button */
        .btn-homepage {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 26px;
            border-radius: 100px;
            border: 1.5px solid rgba(255,255,255,0.55);
            color: var(--white);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 0.3px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(10px);
            text-decoration: none;
            transition: all 0.25s ease;
            cursor: pointer;
        }

        .btn-homepage:hover {
            background: rgba(255,255,255,0.22);
            border-color: rgba(255,255,255,0.8);
            color: var(--white);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }

        /* decorative dots */
        .deco-dots {
            position: absolute;
            bottom: 40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 2;
        }

        .deco-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: rgba(255,255,255,0.35);
        }

        .deco-dots span:nth-child(2) {
            background: rgba(255,255,255,0.75);
            transform: scale(1.3);
        }

        /* ─── RIGHT PANEL ─────────────────────────────── */
        .auth-right {
            flex: 0 0 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--white);
            padding: 60px 48px;
            position: relative;
            overflow: hidden;

            animation: slideInRight 0.75s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }

        /* subtle red tint corner */
        .auth-right::before {
            content: '';
            position: absolute;
            top: -80px;
            right: -80px;
            width: 260px;
            height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(192,40,43,0.07) 0%, transparent 70%);
            pointer-events: none;
        }

        .auth-right::after {
            content: '';
            position: absolute;
            bottom: -60px;
            left: -60px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(192,40,43,0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ─── LOGIN CARD ──────────────────────────────── */
        .login-card {
            width: 100%;
            max-width: 400px;
            position: relative;
            z-index: 1;
            animation: fadeUp 0.6s 0.3s cubic-bezier(0.22,1,0.36,1) both;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-heading {
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
            letter-spacing: -0.3px;
        }

        .login-subheading {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 36px;
            font-weight: 400;
        }

        /* red accent line */
        .login-accent {
            width: 36px;
            height: 3px;
            background: var(--red-primary);
            border-radius: 2px;
            margin-bottom: 32px;
        }

        /* ─── FORM ────────────────────────────────────── */
        .form-label-custom {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 20px;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
            pointer-events: none;
            transition: color 0.2s;
        }

        .form-control-custom {
            width: 100%;
            padding: 13px 14px 13px 40px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
            background: var(--input-bg);
            color: var(--text-dark);
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control-custom::placeholder { color: #BDBDBD; }

        .form-control-custom:focus {
            border-color: var(--red-primary);
            background: var(--white);
            box-shadow: 0 0 0 3.5px rgba(192,40,43,0.10);
        }

        .form-control-custom:focus + .input-icon,
        .input-group-custom:focus-within .input-icon {
            color: var(--red-primary);
        }

        /* password toggle */
        .pw-toggle {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            transition: color 0.2s;
        }

        .pw-toggle:hover { color: var(--red-primary); }

        /* validation */
        .form-control-custom.is-invalid {
            border-color: #E53935;
            background: #FFF5F5;
        }

        .invalid-msg {
            font-size: 12px;
            color: #E53935;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* remember + forgot row */
        .form-row-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 28px;
        }

        .form-check-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .form-check-custom input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--red-primary);
            cursor: pointer;
            border-radius: 4px;
        }

        .form-check-custom label {
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 13px;
            color: var(--red-primary);
            font-weight: 500;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .forgot-link:hover { opacity: 0.7; text-decoration: underline; }

        /* submit button */
        .btn-login {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 15px;
            font-weight: 600;
            letter-spacing: 0.3px;
            color: var(--white);
            background: linear-gradient(135deg, var(--red-dark) 0%, var(--red-mid) 100%);
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: var(--shadow-btn);
            position: relative;
            overflow: hidden;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255,255,255,0);
            transition: background 0.2s;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(192,40,43,0.42);
        }

        .btn-login:hover::after {
            background: rgba(255,255,255,0.06);
        }

        .btn-login:active { transform: translateY(0); }

        /* ─── RESPONSIVE ──────────────────────────────── */
        @media (max-width: 768px) {
            .auth-wrapper { flex-direction: column; }

            .auth-left {
                flex: 0 0 auto;
                min-height: 220px;
                padding: 36px 24px;
                animation: slideInLeft 0.6s cubic-bezier(0.22,1,0.36,1) both;
            }

            .brand-title { font-size: 22px; }
            .brand-subtitle { display: none; }
            .deco-dots { display: none; }

            .auth-right {
                flex: 1;
                padding: 36px 24px;
                animation: slideInRight 0.6s 0.15s cubic-bezier(0.22,1,0.36,1) both;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="auth-wrapper">

    {{-- ── LEFT PANEL ── --}}
    <div class="auth-left">
        <div class="auth-left-content">
            <div class="brand-icon">
                <i class="fa-solid fa-globe"></i>
            </div>

            <div class="brand-title">
                Laguna Geographic<br>Information System
            </div>

            <div class="brand-divider"></div>

            <p class="brand-subtitle">
                Mapping Laguna's landscape — explore spatial data, layers, and insights all in one place.
            </p>

            <a href="/" class="btn-homepage">
                <i class="fa-solid fa-arrow-left" style="font-size:11px;"></i>
                Back to Homepage
            </a>
        </div>
    </div>

    {{-- ── RIGHT PANEL ── --}}
    <div class="auth-right">
        @yield('content')
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')

</body>
</html>