@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height: 100vh; font-family: 'Nunito', sans-serif;    ">

    <!-- Sidebar (already exists in your layout) -->
    <!-- Main Content -->
    <div class="flex-fill p-5">

        <h2 class="mb-4" style="color:#dc3545;">Create New Admin</h2>

        <!-- Success Message -->
        @if(session('success'))
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
                <input type="text" id="name" name="name" class="form-control form-control-lg @error('name') is-invalid @enderror" placeholder="Enter full name" required>
                @error('name') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>  

            <div class="mb-3">
                <label for="email" class="form-label" style="color:#2b2b2b;">Email</label>
                <input type="email" id="email" name="email" class="form-control form-control-lg @error('email') is-invalid @enderror" placeholder="Enter email address" required>
                @error('email') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label" style="color:#2b2b2b;">Password</label>
                <input type="password" id="password" name="password" class="form-control form-control-lg @error('password') is-invalid @enderror" placeholder="Enter password" required>
                @error('password') 
                    <div class="invalid-feedback">{{ $message }}</div> 
                @enderror
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label" style="color:#2b2b2b;">Confirm Password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg" placeholder="Confirm password" required>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" class="btn" style="background-color:#dc3545; color:#fff; font-weight:bold; border-radius:50px; padding:10px;">Create Admin</button>
            </div>
        </form>
    </div>
</div>
@endsection
