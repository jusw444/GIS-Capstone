@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0" style="color:#dc3545;">All Users / Admins</h4>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Category</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>
                                            @if($user->role === 'admin')
                                                <span class="badge bg-success">{{ ucfirst($user->role) }}</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->category}}</td>
                                        <td>{{ $user->created_at->format('Y-m-d') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if($users->isEmpty())
                            <p class="text-center text-muted mt-3">No users found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
