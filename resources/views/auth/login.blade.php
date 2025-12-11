@extends('layouts.auth')

@section('content')
<div style="min-height: 100vh; display:flex; justify-content:center; align-items:center;">

    <div class="card shadow-lg"
         style="width: 550px; border:none; border-radius:22px; transform:scale(1.05);">

        <!-- Header -->
        <div class="card-header text-center"
             style="background:#b30000; color:white; font-size:26px; padding:18px; font-weight:600;
                    border-top-left-radius:22px; border-top-right-radius:22px;">
            {{ __('Login') }}
        </div>

        <div class="card-body" style="padding: 40px 45px;">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label" style="font-weight:500; font-size:15px;">
                        Email Address
                    </label>
                    <input id="email" type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required autofocus
                           style="border-radius:10px; padding:10px; font-size:15px;">

                    @error('email')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label" style="font-weight:500; font-size:15px;">
                        Password
                    </label>
                    <input id="password" type="password"
                           class="form-control @error('password') is-invalid @enderror"
                           name="password" required
                           style="border-radius:10px; padding:10px; font-size:15px;">

                    @error('password')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-3 form-check" style="margin-top:10px;">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember"
                           {{ old('remember') ? 'checked' : '' }}
                           style="transform:scale(1.1); margin-right:6px;">
                    <label class="form-check-label" for="remember" style="font-size:14px; font-weight:400;">
                        Remember Me
                    </label>
                </div>

                <!-- Submit + Register + Forgot -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <button type="submit"
                        class="btn"
                        style="background:#b30000; color:#fff; padding:10px 32px; border-radius:10px;
                               font-weight:600; font-size:16px;">
                        Login
                    </button>

                    <div class="d-flex gap-3">
                        <!-- Register Text -->
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-danger" style="font-size:14px; font-weight:500;">
                                Register
                            </a>
                        @endif

                        <!-- Forgot Password -->
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-danger" style="font-size:14px; font-weight:500;">
                                Forgot?
                            </a>
                        @endif
                    </div>
                </div>

            </form>
        </div>
    </div>

</div>
@endsection
