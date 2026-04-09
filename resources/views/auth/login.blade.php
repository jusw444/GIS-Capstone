@extends('layouts.auth')

@section('content')
    <div class="login-card">

        {{-- LOGIN FORM --}}
        <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
            @csrf

            {{-- Heading --}}
            <h1 class="login-heading">Welcome back</h1>
            <p class="login-subheading">Sign in to access your GIS dashboard</p>
            <div class="login-accent"></div>

            {{-- Status messages --}}
        <div id="status-msg">
            @if (session('status'))
                <div class="toast-success">{{ session('status') }}</div>
            @endif
            @if (session('success'))
                <div class="toast-success">{{ session('success') }}</div>
            @endif
        </div>

            {{-- Email --}}
            <div class="mb-1">
                <label class="form-label-custom">Email Address</label>
            </div>
            <div class="input-group-custom">
                <input type="email" name="email" id="login-email"
                    class="form-control-custom @error('email') is-invalid @enderror" value="{{ old('email') }}"
                    placeholder="you@example.com" required autofocus>
                <i class="fa-regular fa-envelope input-icon"></i>
            </div>
            @error('email')
                <div class="invalid-msg"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror

            {{-- Password --}}
            <div class="mb-1 mt-1">
                <label class="form-label-custom">Password</label>
            </div>
            <div class="input-group-custom" id="pw-group">
                <input type="password" name="password" id="login-password"
                    class="form-control-custom @error('password') is-invalid @enderror" placeholder="••••••••" required>
                <i class="fa-solid fa-lock input-icon"></i>
                <button type="button" class="pw-toggle" onclick="togglePassword()" id="pw-btn" title="Toggle password">
                    <i class="fa-regular fa-eye" id="pw-icon"></i>
                </button>
            </div>
            @error('password')
                <div class="invalid-msg"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror

            {{-- Remember + Forgot --}}
            <div class="form-row-meta mt-3">
                <label class="form-check-custom">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Remember me</label>
                </label>

                <a href="#" class="forgot-link" onclick="showForgotPassword(event)">
                    Forgot password?
                </a>
            </div>

            {{-- Submit --}}
            <button type="submit" class="btn-login">
                Sign In &nbsp;<i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>
        </form>

        {{-- FORGOT PASSWORD FORM --}}
        <form method="POST" action="{{ route('password.email') }}" id="forgot-form" class="d-none">
            @csrf

            {{-- Heading only for forgot password --}}
            <h2 class="login-heading">Forgot Password</h2>
            <p class="login-subheading">Enter your email to receive a reset link</p>

            {{-- Email field prefilled with old input --}}
            <div class="input-group-custom">
                <input type="email" name="email" id="forgot-email"
                    class="form-control-custom @error('email') is-invalid @enderror" placeholder="you@example.com"
                    value="{{ old('email') }}" required>
                <i class="fa-regular fa-envelope input-icon"></i>
            </div>
            @error('email')
                <div class="invalid-msg"><i class="fa-solid fa-circle-exclamation"></i>{{ $message }}</div>
            @enderror

            {{-- Submit button --}}
            <button type="submit" class="btn-login mt-3">
                Send Reset Link &nbsp;<i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>

            {{-- Back to login link --}}
            <p class="mt-2 text-center">
                <a href="#" onclick="showLogin(event)" class="forgot-link">Back to login</a>
            </p>
        </form>

        {{-- RESET PASSWORD FORM --}}
        <form method="POST" action="{{ route('password.update') }}" id="reset-form" class="d-none">
            @csrf
            <input type="hidden" name="token" id="reset-token">

            <h2 class="login-heading">Reset Password</h2>
            <p class="login-subheading">Set your new GIS dashboard password</p>

            <div class="input-group-custom">
                <input type="email" name="email" id="reset-email" class="form-control-custom"
                    placeholder="you@example.com" required>
                <i class="fa-regular fa-envelope input-icon"></i>
            </div>

            <div class="input-group-custom mt-2">
                <input type="password" name="password" id="reset-password" class="form-control-custom"
                    placeholder="••••••••" required>
                <i class="fa-solid fa-lock input-icon"></i>
            </div>

            <div class="input-group-custom mt-2">
                <input type="password" name="password_confirmation" id="reset-password-confirm"
                    class="form-control-custom" placeholder="Confirm Password" required>
                <i class="fa-solid fa-lock input-icon"></i>
            </div>

            <button type="submit" class="btn-login mt-3">
                Reset Password &nbsp;<i class="fa-solid fa-arrow-right-to-bracket"></i>
            </button>

            <p class="mt-2 text-center">
                <a href="#" onclick="showLogin(event)" class="forgot-link">Back to login</a>
            </p>
        </form>

    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword() {
            const field = document.getElementById('login-password');
            const icon = document.getElementById('pw-icon');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Show forms
        function showForgotPassword(e) {
            e.preventDefault();
            document.getElementById('login-form').classList.add('d-none');
            document.getElementById('forgot-form').classList.remove('d-none');
        }

        function showLogin(e) {
            e.preventDefault();
            document.getElementById('forgot-form').classList.add('d-none');
            document.getElementById('reset-form').classList.add('d-none');
            document.getElementById('login-form').classList.remove('d-none');
        }

        // Prefill reset form from URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const token = urlParams.get('token');
            const email = urlParams.get('email');
            if (token && email) {
                document.getElementById('login-form').classList.add('d-none');
                document.getElementById('reset-form').classList.remove('d-none');
                document.getElementById('reset-token').value = token;
                document.getElementById('reset-email').value = email;
            }
        });
    </script>
@endpush
