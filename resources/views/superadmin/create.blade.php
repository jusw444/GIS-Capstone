@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height: 100vh;">

        @include('sidebars.superadmin')

    <!-- Main Content -->
    <div style="flex:1; padding:40px; max-width:600px;">
        <h2>Create New Admin</h2>

        @if(session('success'))
            <div style="background:green; color:white; padding:10px; border-radius:5px; margin-bottom:15px;">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('superadmin.admins.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="name" class="form-control" required>
                @error('name') <small style="color:red">{{ $message }}</small> @enderror
            </div>  

            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control" required>
                @error('email') <small style="color:red">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
                @error('password') <small style="color:red">{{ $message }}</small> @enderror
            </div>

            <div class="mb-3">
                <label>Confirm Password</label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">Create Admin</button>
        </form>
    </div>
</div>
@endsection
