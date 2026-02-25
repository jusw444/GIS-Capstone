@extends('layouts.auth')

@section('content')
<div class="auth-card">

{{-- Header --}}
<div class="auth-header text-center">
    <i class="fa-solid fa-globe"></i>
    Sign in to your account
</div>

    {{-- Body --}}
    <div class="auth-body">
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Email Address</label>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required autofocus>

                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Password --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Password</label>
                <input type="password"
                       name="password"
                       class="form-control @error('password') is-invalid @enderror"
                       placeholder="••••••••"
                       required>

                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Remember --}}
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input"
                           type="checkbox"
                           name="remember"
                           id="remember"
                           {{ old('remember') ? 'checked' : '' }}>
                    <label class="form-check-label small" for="remember">
                        Remember me
                    </label>
                </div>

                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="auth-links">
                        Forgot password?
                    </a>
                @endif
            </div>

            {{-- Login Button --}}
            <button type="submit" class="btn btn-primary-auth">
                Login
            </button>

            {{-- Register --}}
            {{-- @if (Route::has('register'))
                <div class="text-center mt-4 auth-links">
                    <span class="text-muted small">Don’t have an account?</span>
                    <a href="{{ route('register') }}">Register</a>
                </div>
            @endif --}}

        </form>
    </div>
</div>
@endsection
