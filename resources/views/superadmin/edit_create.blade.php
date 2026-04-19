@extends('layouts.app')

@section('page_title', 'Edit Account')

@section('content')

<style>
    .create-page {
        min-height: calc(100vh - 60px);
        font-family: 'Nunito', system-ui, sans-serif;
    }

    .create-form {
        max-width: 600px;
        margin: 0 auto;
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

    .form-control:disabled {
        background: #f8f9fa;
        color: #6C757D;
        cursor: not-allowed;
    }

    .position-relative .form-control {
        padding-right: 50px;
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

    .btn-update {
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

    .btn-update:hover {
        background: #8B0000;
        transform: translateY(-2px);
    }

    .input-group .form-control {
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
    }
</style>

<div class="create-page">
    <div class="create-form">

        <h1 class="create-heading">Edit Account</h1>
        <p class="create-subheading">Update user information</p>
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

        <form action="{{ route('superadmin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Name (disabled) -->
            <div class="mb-4">
                <label class="form-label">Full Name</label>
                <input type="text" name="name"
                    class="form-control"
                    value="{{ old('name', $user->name) }}" disabled>
            </div>

            <!-- Email (disabled) -->
            <div class="mb-4">
                <label class="form-label">Email Address</label>
                <input type="email" name="email"
                    class="form-control"
                    value="{{ old('email', $user->email) }}" disabled>
            </div>

            <!-- Role -->
            <div class="mb-4">
                <label class="form-label">Role</label>
                <select name="role" id="role"
                    class="form-control">
                    <option value="">-- Select Role --</option>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                </select>
            </div>

            <!-- Category -->
            <div class="mb-4" id="categoryWrapper"
                 style="{{ old('role', $user->role) === 'admin' ? 'display:block;' : 'display:none;' }}">
                <label class="form-label">Category</label>
                <div class="input-group">
                    <select name="category" id="category" class="form-control">
                        <option value="">-- Select Category --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}"
                                {{ old('category', $user->category_id) == $category->id ? 'selected' : '' }}>
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
            </div>

            <!-- Submit -->
            <button type="submit" class="btn btn-update">
                Update Account
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Role → Category toggle
    const roleSelect = document.getElementById('role');
    const categoryWrapper = document.getElementById('categoryWrapper');

    function toggleCategory() {
        categoryWrapper.style.display = (roleSelect.value === 'admin') ? 'block' : 'none';
    }
    roleSelect.addEventListener('change', toggleCategory);
    toggleCategory(); // initial state

    // Dynamic category addition
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