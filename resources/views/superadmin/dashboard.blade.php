@extends('layouts.app')

@section('content')
<div class="d-flex" style="min-height: 100vh;">

        @include('sidebars.superadmin')

    <!-- Main Content -->
    <div style="flex:1; padding:40px;">
        <h1>Welcome, Super Admin!</h1>
        <p>Total Admins: {{ $totalAdmins }}</p>
        <p>Total Users: {{ $totalUsers }}</p>
    </div>
</div>
@endsection
