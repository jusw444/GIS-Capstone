@extends('layouts.app')

@section('page_title', 'Manage Classifications')

@section('content')

    <div class="container">

        <h2 class="mb-4 text-danger">Manage Classifications</h2>
        @if (session('success'))
            <div class="alert alert-success" id = "alert-success">
                {{ session('success') }}
            </div>
        @endif
        <!-- Form to add classification -->
        <form action="{{ route('classifications.store') }}" method="POST">
            @csrf
            @php
                $user = auth()->user();
            @endphp

            <div class="mb-3">
                <label class="form-label">Category</label>

                @if ($user->role === 'super_admin')
                    <!-- SUPER ADMIN: can choose -->
                    <select name="category_id" id="category_id" class="form-control" required>
                        <option value="">--Select Category--</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                @else
                    <!-- ADMIN: fixed category -->
                    <input type="text" class="form-control" value="{{ $categories->first()->name }}" disabled>

                    <input type="hidden" name="category_id" value="{{ $categories->first()->id }}">
                @endif
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Classification Name</label>
                <input type="text" name="name" id="name" class="form-control"
                    placeholder="Enter classification name" required>
            </div>

            <div class="mb-3">
                <label for="color" class="form-label">Color</label>
                <input type="color" name="color" id="color" class="form-control color-picker" value="#ff0000">
            </div>

            <button type="submit" id="submitBtn" class="btn btn-danger">Add Classification</button>
        </form>

        <hr>

        <!-- List existing classifications -->
        <h4 class="mt-4">Existing Classifications</h4>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead style="background-color: rgba(183, 28, 28, 0.05);">
                    <tr>
                        <th>ID</th>
                        <th>Category</th>
                        <th>Classification</th>
                        <th>Color</th>
                        <th>Date Created</th>
                        <th class="pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classifications as $classification)
                        <tr class="{{ $classification->trashed() ? 'table-secondary' : '' }}">
                            <td>{{ $classification->id }}</td>
                            <td>{{ $classification->category->name ?? '-' }}</td>
                            <td>{{ $classification->name }}</td>

                            <td>
                                <div
                                    style="width: 30px; height: 20px; background-color: {{ $classification->color ?? '#fff' }}">
                                </div>
                            </td>
                            <td>
                                {{ $classification->created_at }}
                            </td>
                            <td>
                                @if (!$classification->trashed())
                                    <!-- ACTIVE STATE -->
                                    <a href="{{ route('classifications.edit', $classification->id) }}"
                                        class="btn btn-sm btn-outline-primary">Edit</a>

                                    <form action="{{ route('classifications.destroy', $classification->id) }}"
                                        method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-warning"
                                            onclick="return confirm('Move to trash?')">
                                            Trash
                                        </button>
                                    </form>
                                @else
                                    <!-- TRASHED STATE -->
                                    <form action="{{ route('classifications.restore', $classification->id) }}"
                                        method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-sm btn-outline-success"
                                            onclick="return confirm('Restore this?')">
                                            Restore
                                        </button>
                                    </form>

                                    <form action="{{ route('classifications.forceDelete', $classification->id) }}"
                                        method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Permanent delete?')">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No classifications found.</td>
                        </tr>
                    @endforelse
                </tbody>
        </div>


    </div>
    <script>

        const alert = document.getElementById('alert-success');

        setTimeout(() => {
            alert.style.display = 'none';
        }, 3000);
    </script>
@endsection
