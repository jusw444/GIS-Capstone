@extends('layouts.app')

@section('page_title', 'Edit Account')

@section('content')
<div class="d-flex justify-content-center align-items-center"
    style="min-height: 100vh; font-family: 'Nunito', sans-serif; background-color: #f8f9fa;">
    
    <div class="card shadow-sm p-5" style="width: 100%; max-width: 600px; border-radius: 15px;">

        <h2 class="mb-4 text-center text-primary">Edit Account</h2>

        {{-- Validation Errors --}}
        @if ($errors->any())
        <div class="alert alert-danger rounded-3 mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- Success --}}
        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
        </div>
        @endif

        {{-- FORM --}}
        <form action="{{ route('superadmin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- NAME --}}
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name"
                    class="form-control form-control-lg"
                    value="{{ old('name', $user->name) }}">
            </div>

            {{-- EMAIL --}}
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email"
                    class="form-control form-control-lg"
                    value="{{ old('email', $user->email) }}">
            </div>

            {{-- ROLE --}}
            <div class="mb-3">
                <label>Role</label>
                <select name="role" id="role"
                    class="form-control form-control-lg">
                    
                    <option value="">--Select Role--</option>
                    
                    <option value="admin"
                        {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>

                    <option value="user"
                        {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>
                        User
                    </option>
                </select>
            </div>

            {{-- CATEGORY --}}
            <div class="mb-3" id="categoryWrapper"
                style="{{ old('role', $user->role) === 'admin' ? 'display:block;' : 'display:none;' }}">
                
                <label>Category</label>

                <div class="input-group mb-2">
                    <select name="category" id="category"
                        class="form-control form-control-lg">
                        
                        <option value="">--Select Category--</option>

                        @foreach ($categories as $category)
                        <option value="{{ $category->id }}"
                            {{ old('category', $user->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>

                    <button type="button" class="btn btn-outline-secondary" id="addCategoryBtn">+</button>
                </div>

                <input type="text" id="newCategoryInput" class="form-control mb-2"
                    placeholder="New category" style="display:none;">

                <button type="button" class="btn btn-success mb-2"
                    id="saveNewCategory" style="display:none;">
                    Add Category
                </button>
            </div>

            {{-- PASSWORD (OPTIONAL) --}}
            <div class="mb-3">
                <label>Password <small class="text-muted">(leave blank to keep current)</small></label>
                <div class="input-group">
                    <input type="password" name="password" id="password"
                        class="form-control form-control-lg">
                    <button type="button" class="btn btn-outline-secondary" id="togglePassword">👁</button>
                </div>
            </div>

            {{-- CONFIRM --}}
            <div class="mb-4">
                <label>Confirm Password</label>
                <div class="input-group">
                    <input type="password" name="password_confirmation" id="password_confirmation"
                        class="form-control form-control-lg">
                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword">👁</button>
                </div>
                <small id="passwordMatchMessage"></small>
            </div>

            {{-- SUBMIT --}}
            <div class="d-grid">
                <button class="btn btn-primary btn-lg rounded-pill">
                    Update Account
                </button>
            </div>

        </form>
    </div>
</div>
@endsection