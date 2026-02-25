@extends('layouts.app')

@section('page_title', $page['pageTitle'])

@section('content')
<div class="d-flex justify-content-center align-items-center"
    style="min-height: 100vh; font-family: 'Nunito', sans-serif; background-color: #f8f9fa;">
    <div class="card shadow-sm p-5" style="width: 100%; max-width: 600px; border-radius: 15px;">

        <h2 class="mb-4 text-center text-danger">{{ $page['pageName'] }}</h2>

        <!-- Validation Errors -->
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
        <form action="{{ route('superadmin.admins.store') }}" method="POST">
            @csrf

            <!-- Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name"
                    class="form-control form-control-lg @error('name') is-invalid @enderror"
                    placeholder="Enter full name" value="{{ old('name') }}">
                @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Email -->
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email"
                    class="form-control form-control-lg @error('email') is-invalid @enderror"
                    placeholder="Enter email address" value="{{ old('email') }}">
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Role -->
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role"
                    class="form-control form-control-lg @error('role') is-invalid @enderror">
                    <option value="">--Select Role--</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
                </select>
                @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Category (show only if admin) -->
            <div class="mb-3" id="categoryWrapper"
                style="{{ old('role') === 'admin' ? 'display:block;' : 'display:none;' }}">
                <label for="category" class="form-label">Category</label>

                <!-- Dropdown + Add New Button -->
                <div class="input-group mb-2">
                    <select id="category" name="category"
                        class="form-control form-control-lg @error('category') is-invalid @enderror">
                        <option value="">--Select Category--</option>
                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="addCategoryBtn">+</button>
                </div>

                <!-- Input for new category -->
                <input type="text" id="newCategoryInput" class="form-control mb-2" placeholder="New category"
                    style="display:none;">
                <button type="button" class="btn btn-success mb-2" id="saveNewCategory" style="display:none;">Add
                    Category</button>

                @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password"
                        class="form-control form-control-lg @error('password') is-invalid @enderror"
                        placeholder="Enter password">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">👁</button>
                </div>
                @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <div class="input-group">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="form-control form-control-lg" placeholder="Confirm password">
                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">👁</button>
                </div>
                <small id="passwordMatchMessage" class="mt-2"></small>
            </div>

            <!-- Submit -->
            <div class="d-grid mb-3">
                <button type="submit" class="btn btn-danger btn-lg rounded-pill">
                    Create Account
                </button>
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
    const roleSelect = document.getElementById('role');
    const categoryWrapper = document.getElementById('categoryWrapper');
    const addCategoryBtn = document.getElementById('addCategoryBtn');
    const newCategoryInput = document.getElementById('newCategoryInput');
    const saveNewCategoryBtn = document.getElementById('saveNewCategory');
    const categorySelect = document.getElementById('category');

    // Show/hide password
    document.getElementById('togglePassword').addEventListener('click', () => {
        password.type = password.type === 'password' ? 'text' : 'password';
    });
    document.getElementById('toggleConfirmPassword').addEventListener('click', () => {
        confirmPassword.type = confirmPassword.type === 'password' ? 'text' : 'password';
    });

    // Live password match
    function checkPasswordMatch() {
        if (!confirmPassword.value) {
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

    // Show/hide category based on role
    function toggleCategory() {
        categoryWrapper.style.display = roleSelect.value === 'admin' ? 'block' : 'none';
    }
    roleSelect.addEventListener('change', toggleCategory);
    toggleCategory();

    // Show input to add new category
    addCategoryBtn.addEventListener('click', () => {
        newCategoryInput.style.display = 'block';
        saveNewCategoryBtn.style.display = 'inline-block';
        newCategoryInput.focus();
    });

    // Save new category via AJAX
    saveNewCategoryBtn.addEventListener('click', () => {
        const name = newCategoryInput.value.trim();
        if (!name) return alert('Enter a category name');

        fetch('{{ route('superadmin.categories.store.ajax') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const option = document.createElement('option');
                option.value = data.id; // save ID for backend
                option.text = data.category;
                option.selected = true;
                categorySelect.add(option);

                // Reset input
                newCategoryInput.value = '';
                newCategoryInput.style.display = 'none';
                saveNewCategoryBtn.style.display = 'none';
            } else {
                alert('Failed to add category');
            }
        })
        .catch(err => alert('Error: ' + err));
    });
</script>
@endpush
@endsection