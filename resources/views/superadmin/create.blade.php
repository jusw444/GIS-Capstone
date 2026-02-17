@extends('layouts.app')

@section('content')
    <div class="d-flex" style="min-height: 100vh; font-family: 'Nunito', sans-serif;    ">

        <!-- Sidebar (already exists in your layout) -->
        <!-- Main Content -->
        <div class="flex-fill p-5">

            <h2 class="mb-4" style="color:#dc3545;">Create New Admin</h2>

            @if ($errors->any())
        <div class="alert alert-danger rounded-3 mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

            <!-- Success Message -->
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('superadmin.admins.store') }}" method="POST" class="w-50">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label" style="color:#2b2b2b;">Name</label>
                    <input type="text" id="name" name="name"
                        class="form-control form-control-lg @error('name') is-invalid @enderror"
                        placeholder="Enter full name">
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label" style="color:#2b2b2b;">Email</label>
                    <input type="email" id="email" name="email"
                        class="form-control form-control-lg @error('email') is-invalid @enderror"
                        placeholder="Enter email address">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label" style="color:#2b2b2b;">Role</label>
                    <select id="role" name="role"
                        class="form-control form-control-lg @error('role') is-invalid @enderror">
                        <option value="">--Select Role--</option>
                        <option value="admin">Admin</option>
                        <option value="user">User</option>
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label" style="color:#2b2b2b;">Password</label>
                    <div class="input-group">
                        <input type="password" id="password" name="password"
                            class="form-control form-control-lg @error('password') is-invalid @enderror"
                            placeholder="Enter password">
                        <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                            👁
                        </button>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label" style="color:#2b2b2b;">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control form-control-lg" placeholder="Confirm password">
                        <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">
                            👁
                        </button>
                    </div>
                    <small id="passwordMatchMessage" class="mt-2"></small>
                </div>


                <div class="d-grid mb-3">
                    <button type="submit" class="btn"
                        style="background-color:#dc3545; color:#fff; font-weight:bold; border-radius:50px; padding:10px;">Create
                        Account</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('password_confirmation');
            const message = document.getElementById('passwordMatchMessage');
            const submitButton = document.querySelector('button[type="submit"]');

            // Show/Hide Password
            document.getElementById('togglePassword').addEventListener('click', function() {
                password.type = password.type === 'password' ? 'text' : 'password';
            });

            document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
                confirmPassword.type = confirmPassword.type === 'password' ? 'text' : 'password';
            });

            // Live password match check
            function checkPasswordMatch() {
                if (confirmPassword.value === '') {
                    message.textContent = '';
                    submitButton.disabled = false;
                    return;
                }

                if (password.value === confirmPassword.value) {
                    message.textContent = 'Passwords match ✔';
                    message.style.color = 'green';
                    submitButton.disabled = false;
                } else {
                    message.textContent = 'Passwords do not match ✖';
                    message.style.color = 'red';
                    submitButton.disabled = true;
                }
            }

            password.addEventListener('keyup', checkPasswordMatch);
            confirmPassword.addEventListener('keyup', checkPasswordMatch);
        </script>
    @endpush
@endsection
