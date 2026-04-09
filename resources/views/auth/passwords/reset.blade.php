@extends('layouts.auth')

@section('content')
<div class="login-card">

    {{-- Heading --}}
    <h1 class="login-heading">Reset Password</h1>
    <p class="login-subheading">Enter your new password to access your GIS dashboard</p>
    <div class="login-accent"></div>

    <form method="POST" action="{{ route('password.update') }}" novalidate>
        @csrf

        {{-- Hidden Token --}}
        <input type="hidden" name="token" value="{{ $token }}">

        {{-- Email --}}
        <div class="mb-1">
            <label class="form-label-custom">Email Address</label>
        </div>
        <div class="input-group-custom">
            <input type="email"
                   name="email"
                   class="form-control-custom @error('email') is-invalid @enderror"
                   value="{{ $email ?? old('email') }}"
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
        <div class="mb-1 mt-2">
            <label class="form-label-custom">New Password</label>
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

        {{-- Confirm Password --}}
        <div class="mb-1 mt-2">
            <label class="form-label-custom">Confirm Password</label>
        </div>
        <div class="input-group-custom">
            <input type="password"
                   name="password_confirmation"
                   class="form-control-custom"
                   placeholder="••••••••"
                   required>
            <i class="fa-solid fa-lock input-icon"></i>
        </div>

        {{-- Submit --}}
        <button type="submit" class="btn-login mt-3">
            Reset Password &nbsp;<i class="fa-solid fa-arrow-right-to-bracket"></i>
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

// ✅ Show success toast
@if(session('success'))
    const toast = document.createElement('div');
    toast.className = 'toast-success';
    toast.innerText = "{{ session('success') }}";
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 4000);
@endif
</script>

<style>
/* Modern GIS style toast */
.toast-success {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #4CAF50;
    color: white;
    padding: 15px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    font-weight: 600;
    z-index: 9999;
    animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(50px);}
    to { opacity: 1; transform: translateX(0);}
}
</style>
@endpush