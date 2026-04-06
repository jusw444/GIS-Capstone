@extends('layouts.auth')

@section('content')
<div class="login-card">

    {{-- Heading --}}
    <h1 class="login-heading">Welcome back</h1>
    <p class="login-subheading">Sign in to access your GIS dashboard</p>
    <div class="login-accent"></div>

    <form method="POST" action="{{ route('login') }}" novalidate>
        @csrf

        {{-- Email --}}
        <div class="mb-1">
            <label class="form-label-custom">Email Address</label>
        </div>
        <div class="input-group-custom">
            <input type="email"
                   name="email"
                   class="form-control-custom @error('email') is-invalid @enderror"
                   value="{{ old('email') }}"
                   placeholder="you@example.com"
                   required autofocus>
            <i class="fa-regular fa-envelope input-icon"></i>
        </div>
        @error('email')
            <div class="invalid-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
            </div>
        @enderror

        {{-- Password --}}
        <div class="mb-1 mt-1">
            <label class="form-label-custom">Password</label>
        </div>
        <div class="input-group-custom" id="pw-group">
            <input type="password"
                   name="password"
                   id="password-field"
                   class="form-control-custom @error('password') is-invalid @enderror"
                   placeholder="••••••••"
                   required>
            <i class="fa-solid fa-lock input-icon"></i>
            <button type="button" class="pw-toggle" onclick="togglePassword()" id="pw-btn" title="Toggle password">
                <i class="fa-regular fa-eye" id="pw-icon"></i>
            </button>
        </div>
        @error('password')
            <div class="invalid-msg">
                <i class="fa-solid fa-circle-exclamation"></i>
                {{ $message }}
            </div>
        @enderror

        {{-- Remember + Forgot --}}
        <div class="form-row-meta mt-3">
            <label class="form-check-custom">
                <input type="checkbox"
                       name="remember"
                       id="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">Remember me</label>
            </label>

            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link">
                    Forgot password?
                </a>
            @endif
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-login">
            Sign In &nbsp;<i class="fa-solid fa-arrow-right-to-bracket"></i>
        </button>

    </form>
</div>
@endsection

@push('scripts')
<script>
    function togglePassword() {
        const field = document.getElementById('password-field');
        const icon  = document.getElementById('pw-icon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }
</script>
@endpush