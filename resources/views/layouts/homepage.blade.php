<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('page_title') | Laguna GIS</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,600;0,700;1,600&family=DM+Sans:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --red: #C0282B;
            --red-dark: #8B0000;
            --red-mid: #E53935;
            --red-soft: #FDECEA;
            --red-pale: #FFF5F5;
            --red-tint: #FAF0F0;
            --white: #FFFFFF;
            --off-white: #FAF8F8;
            --text: #1A1A1A;
            --text-muted: #7A7A7A;
            --border: #F0E8E8;
            --shadow-nav: 0 1px 0 rgba(192, 40, 43, 0.08), 0 4px 16px rgba(192, 40, 43, 0.06);
            --shadow-btn: 0 6px 20px rgba(192, 40, 43, 0.30);
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--off-white);
            color: var(--text);
            min-height: 100vh;
        }

        /* ══════════════════════════════════════
           NAVBAR
        ══════════════════════════════════════ */
        .gis-nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            box-shadow: var(--shadow-nav);
            animation: navDrop 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes navDrop {
            from {
                transform: translateY(-100%);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .gis-nav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Brand */
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .nav-brand-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--red-dark), var(--red-mid));
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 15px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(192, 40, 43, 0.25);
        }

        .nav-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.15;
        }

        .nav-brand-name {
            font-family: 'Playfair Display', serif;
            font-size: 15px;
            font-weight: 700;
            color: var(--text);
            letter-spacing: -0.2px;
        }

        .nav-brand-sub {
            font-size: 10.5px;
            font-weight: 500;
            color: var(--red);
            letter-spacing: 0.6px;
            text-transform: uppercase;
        }

        /* Nav right */
        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-nav-login {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 22px;
            border-radius: 100px;
            background: linear-gradient(135deg, var(--red-dark) 0%, var(--red-mid) 100%);
            color: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 0.2px;
            border: none;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.22s ease;
            box-shadow: var(--shadow-btn);
        }

        .btn-nav-login:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(192, 40, 43, 0.38);
        }

        .btn-nav-login:active {
            transform: translateY(0);
        }

        /* ══════════════════════════════════════
           PAGE WRAPPER
        ══════════════════════════════════════ */
        .gis-main {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 32px;
            animation: pageIn 0.6s 0.15s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes pageIn {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ══════════════════════════════════════
           FOOTER
        ══════════════════════════════════════ */
        .gis-footer {
            margin-top: auto;
            border-top: 1px solid var(--border);
            background: var(--white);
            padding: 20px 32px;
        }

        .gis-footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 400;
        }

        .footer-brand strong {
            color: var(--red);
            font-weight: 600;
        }

        .footer-dot {
            width: 3px;
            height: 3px;
            border-radius: 50%;
            background: var(--border);
            display: inline-block;
        }

        .footer-copy {
            font-size: 12px;
            color: var(--text-muted);
        }

        /* ══════════════════════════════════════
           MOBILE
        ══════════════════════════════════════ */
        @media (max-width: 600px) {
            .gis-nav-inner {
                padding: 0 16px;
            }

            .nav-brand-sub {
                display: none;
            }

            .gis-main {
                padding: 0 16px;
            }

            .gis-footer {
                padding: 16px;
            }
        }
    </style>

    @stack('styles')
</head>

<body style="display:flex; flex-direction:column; min-height:100vh;">

    {{-- ── NAVBAR ── --}}
    <nav class="gis-nav">
        <div class="gis-nav-inner">

            {{-- Brand --}}
            <a href="{{ url('/') }}" class="nav-brand">
                <div class="nav-brand-icon">
                    <i class="fa-solid fa-globe"></i>
                </div>
                <div class="nav-brand-text">
                    <span class="nav-brand-name">Laguna GIS</span>
                    <span class="nav-brand-sub">Geographic Information System</span>
                </div>
            </a>

            {{-- Login Button --}}
            <div class="nav-actions">
                <a href="{{ route('login') }}" class="btn-nav-login">
                    <i class="fa-solid fa-right-to-bracket" style="font-size:12px;"></i>
                    Login
                </a>
            </div>

        </div>
    </nav>

    {{-- ── CONTENT ── --}}
    <main class="gis-main" style="flex:1; position:relative; overflow:hidden;">
        @yield('content')
    </main>

    {{-- ── FOOTER ── --}}
    <footer class="gis-footer">
        <div class="gis-footer-inner">
            <div class="footer-brand">
                <i class="fa-solid fa-globe" style="color: var(--red); font-size:13px;"></i>
                <strong>Laguna GIS</strong>
                <span class="footer-dot"></span>
                <span>Geographic Information System</span>
            </div>
            <span class="footer-copy">&copy; {{ date('Y') }} Province of Laguna. All rights reserved.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

</body>

</html>
