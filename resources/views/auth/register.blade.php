@extends('layouts.auth')

@section('content')
<div class="auth-card">

    {{-- Header --}}
    <div class="auth-header">
        <i class="fa-solid fa-globe"></i>
        Create your account
    </div>

    {{-- Body --}}
    <div class="auth-body">
        <form method="POST" action="{{ route('register') }}">
            @csrf

            {{-- Name --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Full Name</label>
                <input type="text"
                       name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name') }}"
                       placeholder="Juan Dela Cruz"
                       required autofocus>

                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Email --}}
            <div class="mb-3">
                <label class="form-label fw-medium">Email Address</label>
                <input type="email"
                       name="email"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email') }}"
                       placeholder="you@example.com"
                       required>

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

            {{-- Confirm Password --}}
            <div class="mb-4">
                <label class="form-label fw-medium">Confirm Password</label>
                <input type="password"
                       name="password_confirmation"
                       class="form-control"
                       placeholder="••••••••"
                       required>
            </div>

            {{-- Register Button --}}
            <button type="submit" class="btn btn-primary-auth">
                Register
            </button>

            {{-- Login Link --}}
            @if (Route::has('login'))
                <div class="text-center mt-4 auth-links">
                    <span class="text-muted small">Already have an account?</span>
                    <a href="{{ route('login') }}">Login</a>
                </div>
            @endif

        </form>
    </div>
</div>
@endsection
