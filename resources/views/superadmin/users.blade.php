@extends('layouts.app')
@section('page_title', $page['pageTitle'])
@section('content')

<div class="gis-users-page">

    {{-- SUCCESS ALERT --}}
    @if(session('success'))
        <div class="gis-alert gis-alert--success" id="alert-success">
            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M12.5 3.5L6 10.5L2.5 7" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- PAGE HEADER --}}
    <div class="gis-page-header">
        <div class="gis-page-header__left">
            <span class="gis-page-header__dot"></span>
            <h1 class="gis-page-header__title">Users &amp; Admins</h1>
            <span class="gis-count-badge">{{ $users->count() }} records</span>
        </div>
        <a href="{{ route('superadmin.admins.create') }}" class="gis-btn gis-btn--primary">
            <svg width="13" height="13" viewBox="0 0 13 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M6.5 1V12M1 6.5H12" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            Add User
        </a>
    </div>

    {{-- USERS TABLE CARD --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-semibold mb-0" style="color:#b71c1c;">
                <i class="fas fa-users me-2"></i>Users Management
            </h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: rgba(183, 28, 28, 0.05);">
                        <tr>
                            <th class="ps-3">Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr class="{{ $user->trashed() ? 'table-secondary' : 'hover-row' }}">

                                {{-- NAME WITH AVATAR --}}
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="gis-avatar-sm {{ $user->trashed() ? 'gis-avatar-sm--muted' : '' }}">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}{{ strtoupper(substr(strrchr($user->name, ' '), 1, 1)) }}
                                        </div>
                                        <span class="fw-semibold">{{ $user->name }}</span>
                                    </div>
                                </td>

                                {{-- EMAIL --}}
                                <td class="text-muted small">{{ $user->email }}</td>

                                {{-- ROLE --}}
                                <td>
                                    @if($user->role === 'admin')
                                        <span class="badge px-3 py-2 rounded-pill"
                                              style="background-color: rgba(183,28,28,0.1); color: #b71c1c;">
                                            Admin
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                            {{ ucfirst($user->role) }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if ($user->role === 'admin')
                                        <span>{{$user->category->name}}</span>
                                        
                                    @else
                                        N/A
                                    @endif
                                </td>

                                {{-- STATUS --}}
                                <td>
                                    @if($user->trashed())
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">
                                            <i class="fas fa-archive me-1"></i> Archived
                                        </span>
                                    @else
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                            <i class="fas fa-check-circle me-1"></i> Active
                                        </span>
                                    @endif
                                </td>

                                {{-- CREATED AT --}}
                                <td class="small text-muted">{{ $user->created_at->format('M d, Y') }}</td>

                                {{-- ACTIONS --}}
                                <td class="text-end pe-3">
                                    @if(!$user->trashed())

                                        <a href="{{ route('superadmin.users.edit', $user->id) }}"
                                           class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>

                                        <form action="{{ route('superadmin.users.destroy', $user->id) }}"
                                              method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Move this user to trash?')">
                                                <i class="fas fa-trash"></i> Trash
                                            </button>
                                        </form>

                                    @else

                                        <form action="{{ route('superadmin.users.restore', $user->id) }}"
                                              method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn btn-sm btn-outline-success me-1"
                                                    onclick="return confirm('Restore this user?')">
                                                <i class="fas fa-undo-alt"></i> Restore
                                            </button>
                                        </form>

                                        <form action="{{ route('superadmin.users.forceDelete', $user->id) }}"
                                              method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Permanently delete this user? This cannot be undone.')">
                                                <i class="fas fa-times"></i> Delete
                                            </button>
                                        </form>

                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                                    <h6 class="text-muted">No users found</h6>
                                    <p class="small text-muted">Add a new user to get started.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{-- PAGINATION --}}
                @if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
                    <div class="pagination-wrapper d-flex justify-content-between align-items-center mt-4 px-3 py-3">
                        <div class="pagination-info small text-muted">
                            Showing
                            <strong class="text-dark">{{ $users->firstItem() ?? 0 }}</strong>
                            to
                            <strong class="text-dark">{{ $users->lastItem() ?? 0 }}</strong>
                            of
                            <strong class="text-dark">{{ $users->total() }}</strong>
                            records
                        </div>
                        <div class="pagination-links">
                            {{ $users->links() }}
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

</div>

{{-- SCOPED STYLES --}}
<style>
.gis-users-page {
    padding: 1.75rem 1.5rem;
}

/* ─── Alert ──────────────────────────────────────────────── */
.gis-alert {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 13.5px;
    font-weight: 500;
    margin-bottom: 1.25rem;
    border: 0.5px solid;
}
.gis-alert--success {
    background: #EAF3DE;
    color: #3B6D11;
    border-color: #C0DD97;
}

/* ─── Page Header ────────────────────────────────────────── */
.gis-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 1.25rem;
    flex-wrap: wrap;
    gap: 12px;
}
.gis-page-header__left {
    display: flex;
    align-items: center;
    gap: 10px;
}
.gis-page-header__dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #A32D2D;
    flex-shrink: 0;
}
.gis-page-header__title {
    font-size: 18px;
    font-weight: 500;
    color: #A32D2D;
    margin: 0;
}
.gis-count-badge {
    font-size: 12px;
    font-weight: 400;
    background: #FCEBEB;
    color: #A32D2D;
    border: 0.5px solid #F7C1C1;
    padding: 2px 10px;
    border-radius: 999px;
}

/* ─── Add Button ─────────────────────────────────────────── */
.gis-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 12px;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 7px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: opacity 0.15s ease;
    white-space: nowrap;
    font-family: inherit;
}
.gis-btn--primary {
    background: #A32D2D;
    color: #fff;
}
.gis-btn--primary:hover {
    opacity: 0.85;
    color: #fff;
}

/* ─── Avatar ─────────────────────────────────────────────── */
.gis-avatar-sm {
    width: 30px;
    height: 30px;
    min-width: 30px;
    border-radius: 50%;
    background: rgba(183, 28, 28, 0.1);
    color: #b71c1c;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10.5px;
    font-weight: 600;
    letter-spacing: 0.02em;
}
.gis-avatar-sm--muted {
    background: #f0f0f0;
    color: #aaa;
}

/* ─── Table ──────────────────────────────────────────────── */
.table thead th {
    font-size: 12px;
    font-weight: 500;
    color: #6b7280;
    letter-spacing: 0.03em;
    padding-top: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f0;
    white-space: nowrap;
}
.table tbody td {
    padding-top: 12px;
    padding-bottom: 12px;
    vertical-align: middle;
    border-bottom: 1px solid #f7f7f7;
    font-size: 13.5px;
}
.hover-row:hover {
    background-color: rgba(183, 28, 28, 0.04);
    transition: background-color 0.15s ease;
}
.table-secondary td {
    opacity: 0.65;
}

/* ─── Pagination ─────────────────────────────────────────── */
.pagination-wrapper {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 12px 18px;
}
.pagination-info {
    font-size: 13px;
    color: #6b7280;
}
.pagination-info strong {
    color: #111827;
}
.pagination-links nav { margin: 0; }
.pagination-links .pagination { margin: 0; }
.pagination .page-link {
    color: #dc2626;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    margin: 0 3px;
    padding: 6px 12px;
    font-size: 13px;
    transition: all 0.2s ease;
}
.pagination .page-link:hover {
    background: #fee2e2;
    color: #b91c1c;
    border-color: #fecaca;
}
.pagination .active .page-link {
    background: #dc2626;
    border-color: #dc2626;
    color: #ffffff;
}
.pagination .disabled .page-link {
    color: #9ca3af;
}

/* ─── Responsive ─────────────────────────────────────────── */
@media (max-width: 640px) {
    .gis-users-page { padding: 1rem; }
    .gis-page-header__title { font-size: 15px; }
}
</style>

{{-- AUTO-DISMISS ALERT --}}
<script>
    (function () {
        const alert = document.getElementById('alert-success');
        if (alert) {
            setTimeout(function () {
                alert.style.transition = 'opacity 0.4s ease';
                alert.style.opacity = '0';
                setTimeout(function () { alert.remove(); }, 400);
            }, 3000);
        }
    })();
</script>

@endsection