@extends('layouts.app')
@section('page_title', $page['pageTitle'])
@section('content')
<div class="container-fluid py-4">

    @if(session('success'))
        <div id="alert-success" class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">

                <div class="card-header d-flex justify-content-between align-items-center">
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
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($users as $user)
                                    <tr class="{{ $user->trashed() ? 'table-secondary' : '' }}">

                                        <td>{{ $user->name }}</td>

                                        <td>{{ $user->email }}</td>

                                        <td>
                                            @if($user->role === 'admin')
                                                <span class="badge bg-success">Admin</span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($user->role) }}</span>
                                            @endif
                                        </td>

                                        {{-- STATUS --}}
                                        <td>
                                            @if($user->trashed())
                                                <span class="badge bg-secondary">Trashed</span>
                                            @else
                                                <span class="badge bg-success">Active</span>
                                            @endif
                                        </td>

                                        <td>{{ $user->created_at->format('m-d-Y') }}</td>

                                        <td>
                                            @if(!$user->trashed())

                                                {{-- EDIT (optional if you have edit page) --}}
                                                <a href="{{ route('superadmin.users.edit', $user->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>

                                                {{-- SOFT DELETE --}}
                                                <form action="{{ route('superadmin.users.destroy', $user->id) }}"
                                                      method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-outline-warning"
                                                            onclick="return confirm('Move user to trash?')">
                                                        Trash
                                                    </button>
                                                </form>

                                            @else

                                                {{-- RESTORE --}}
                                                <form action="{{ route('superadmin.users.restore', $user->id) }}"
                                                      method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('PUT')

                                                    <button class="btn btn-sm btn-outline-success"
                                                            onclick="return confirm('Restore user?')">
                                                        Restore
                                                    </button>
                                                </form>

                                                {{-- FORCE DELETE --}}
                                                <form action="{{ route('superadmin.users.forceDelete', $user->id) }}"
                                                      method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Permanently delete user?')">
                                                        Delete
                                                    </button>
                                                </form>

                                            @endif
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">
                                            No users found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

</div>

{{-- AUTO HIDE ALERT --}}
<script>
    const alertBox = document.getElementById('alert-success');

    if (alertBox) {
        setTimeout(() => {
            alertBox.style.display = 'none';
        }, 3000);
    }
</script>

@endsection