@extends('layouts.auth')

@section('content')
<div style="min-height: 100vh; display:flex; justify-content:center; align-items:center;">

    <div class="card shadow-lg"
         style="width: 550px; border:none; border-radius:22px; transform:scale(1.05);">

        <!-- Header -->
        <div class="card-header text-center"
             style="background:#b30000; color:white; font-size:26px; padding:18px; font-weight:600;
                    border-top-left-radius:22px; border-top-right-radius:22px;">
            {{ __('Register') }}
        </div>

        <div class="card-body" style="padding: 40px 45px;">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Name -->
                <div class="mb-3">
                    <label for="name" class="form-label" style="font-weight:500; font-size:15px;">
                        Name
                    </label>
                    <input id="name" type="text"
                           class="form-control @error('name') is-invalid @enderror"
                           name="name" value="{{ old('name') }}" required autofocus
                           style="border-radius:10px; padding:10px; font-size:15px;">

                    @error('name')
                        <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label" style="font-weight:500; font-size:15px;">
                        Email Address
                    </label>
                    <input id="email" type="email"
                           class="form-control @error('email') is-invalid @enderror"
                           name="email" value="{{ old('email') }}" required
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

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label for="password-confirm" class="form-label" style="font-weight:500; font-size:15px;">
                        Confirm Password
                    </label>
                    <input id="password-confirm" type="password" class="form-control"
                           name="password_confirmation" required
                           style="border-radius:10px; padding:10px; font-size:15px;">
                </div>

                <!-- Submit -->
                <div class="d-flex justify-content-end mt-4">
                    <button type="submit"
                            class="btn"
                            style="background:#b30000; color:#fff; padding:10px 32px; border-radius:10px;
                                   font-weight:600; font-size:16px;">
                        Register
                    </button>
                </div>

                <!-- Login link -->
                @if (Route::has('login'))
                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}" style="font-size:14px; font-weight:500; color:#b30000;">
                            Already have an account? Login
                        </a>
                    </div>
                @endif

            </form>
        </div>
    </div>

</div>
@endsection
