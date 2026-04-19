@extends('layouts.app')

@section('page_title', $page['pageTitle'] ?? 'Create Admin/User Account')

@section('content')

<style>
    .create-page {
        min-height: calc(100vh - 60px);
        font-family: 'Nunito', system-ui, sans-serif;
    }

    .create-form {
        max-width: 600px;           /* MADE WIDER as requested */
        margin: 0 auto;
                 /* balanced padding for wider look */ /* very subtle shadow */
    }

    .create-heading {
        font-size: 30px;
        font-weight: 700;
        color: #1C1C1C;
        text-align: center;
        margin-bottom: 6px;
    }

    .create-subheading {
        text-align: center;
        color: #6C757D;
        font-size: 15.5px;
        margin-bottom: 30px;
    }

    .accent-bar {
        width: 70px;
        height: 4px;
        background: #C0282B;
        margin: 0 auto 40px;
        border-radius: 4px;
    }

    .form-label {
        font-weight: 600;
        font-size: 14px;
        color: #1C1C1C;
        margin-bottom: 9px;
    }

    .form-control {
        padding: 15px 18px;
        border: 2px solid #E9ECEF;
        border-radius: 10px;
        font-size: 15.5px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #C0282B;
        box-shadow: 0 0 0 4px rgba(192, 40, 43, 0.15);
        outline: none;
    }

    .position-relative .form-control {
        padding-right: 50px; /* space for eye icon */
    }

    .pw-toggle {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        font-size: 19px;
        color: #6C757D;
        cursor: pointer;
        z-index: 10;
    }

    .btn-create {
        background: #C0282B;
        color: white;
        border: none;
        padding: 15px;
        font-size: 16.5px;
        font-weight: 600;
        border-radius: 10px;
        width: 100%;
        margin-top: 15px;
        transition: all 0.3s ease;
    }

    .btn-create:hover {
        background: #8B0000;
        transform: translateY(-2px);
    }

    .password-match {
        font-size: 14px;
        margin-top: 8px;
        display: block;
    }

    /* Make category section clean */
    .input-group .form-control {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
</style>

<div class="create-page">
    <div class="create-form">

        <h1 class="create-heading">{{ $page['pageName'] ?? 'Create Admin/User Account' }}</h1>
        <p class="create-subheading">Add a new user to the GIS dashboard</p>
        <div class="accent-bar"></div>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger mb-4">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Success Message -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('superadmin.admins.store') }}" method="POST">
            @csrf

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name"
                    class="form-control @error('name') is-invalid @enderror"
                    placeholder="Enter full name" value="{{ old('name') }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    placeholder="you@example.com" value="{{ old('email') }}" required>
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role"
                    class="form-control @error('role') is-invalid @enderror" required>
                    <option value="">-- Select Role --</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Category -->
            <div class="mb-4" id="categoryWrapper" style="display: none;">
                <label for="category" class="form-label">Category</label>
                <div class="input-group">
                    <select id="category" name="category"
                        class="form-control @error('category') is-invalid @enderror">
                        <option value="">-- Select Category --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" 
                                {{ old('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="addCategoryBtn">+</button>
                </div>

                <input type="text" id="newCategoryInput" class="form-control mt-2" 
                       placeholder="New category name" style="display:none;">
                <button type="button" class="btn btn-success mt-2" id="saveNewCategory" 
                        style="display:none;">Add Category</button>

                @error('category')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-4 position-relative">
                <label for="password" class="form-label">Password</label>
                <input type="password" id="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="Enter password" required>
                <button type="button" class="pw-toggle" id="togglePassword">👁</button>
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4 position-relative">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="form-control" placeholder="Confirm password" required>
                <button type="button" class="pw-toggle" id="toggleConfirmPassword">👁</button>
                <small id="passwordMatchMessage" class="password-match"></small>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-create" id="submit-btn">
                Create Account
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Password toggle
    document.getElementById('togglePassword').addEventListener('click', function() {
        const pw = document.getElementById('password');
        pw.type = pw.type === 'password' ? 'text' : 'password';
    });

    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const cpw = document.getElementById('password_confirmation');
        cpw.type = cpw.type === 'password' ? 'text' : 'password';
    });

    // Live password match
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('password_confirmation');
    const message = document.getElementById('passwordMatchMessage');
    const submitButton = document.getElementById('submit-btn');

    function checkPasswordMatch() {
        if (!confirmPassword.value) {
            message.textContent = '';
            submitButton.disabled = false;
            return;
        }
        if (password.value === confirmPassword.value) {
            message.innerHTML = 'Passwords match <span style="color:green">✓</span>';
            message.style.color = 'green';
            submitButton.disabled = false;
        } else {
            message.innerHTML = 'Passwords do not match <span style="color:red">✕</span>';
            message.style.color = 'red';
            submitButton.disabled = true;
        }
    }

    password.addEventListener('keyup', checkPasswordMatch);
    confirmPassword.addEventListener('keyup', checkPasswordMatch);

    // Role → Category
    const roleSelect = document.getElementById('role');
    const categoryWrapper = document.getElementById('categoryWrapper');

    function toggleCategory() {
        categoryWrapper.style.display = (roleSelect.value === 'admin') ? 'block' : 'none';
    }
    roleSelect.addEventListener('change', toggleCategory);
    toggleCategory();

    // Dynamic category
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const newCategoryInput = document.getElementById('newCategoryInput');
    const saveNewCategoryBtn = document.getElementById('saveNewCategory');
    const categorySelect = document.getElementById('category');

    addCategoryBtn.addEventListener('click', () => {
        newCategoryInput.style.display = 'block';
        saveNewCategoryBtn.style.display = 'inline-block';
        newCategoryInput.focus();
    });

    saveNewCategoryBtn.addEventListener('click', () => {
        const name = newCategoryInput.value.trim();
        if (!name) return alert('Enter a category name');

        const exists = Array.from(categorySelect.options).some(opt =>
            opt.text.toLowerCase() === name.toLowerCase()
        );

        if (exists) {
            alert('Category already exists');
            return;
        }

        const option = document.createElement('option');
        option.value = 'new:' + name;
        option.text = name;
        option.selected = true;
        categorySelect.add(option);

        newCategoryInput.value = '';
        newCategoryInput.style.display = 'none';
        saveNewCategoryBtn.style.display = 'none';
    });
</script>
@endpush
@endsection