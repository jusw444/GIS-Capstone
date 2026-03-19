@extends('layouts.app')
@section('page_title', $page['pageTitle'])
@section('content')
<div class="container">
    <h1 class="mb-4">Edit Classification</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.classifications.update', $classification->id) }}" method="POST">
        @csrf
        @method('PUT') <!-- Makes this a PUT request -->

        <!-- Category Dropdown -->
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">--Select Category--</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $classification->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Classification Name -->
        <div class="mb-3">
            <label for="name" class="form-label">Classification Name</label>
            <input type="text" name="name" id="name" class="form-control" 
                   value="{{ old('name', $classification->name) }}" required>
        </div>

        <!-- Color Picker -->
        <div class="mb-3">
            <label for="color" class="form-label">Color</label>
            <input type="color" name="color" id="color" class="form-control color-picker" 
                   value="{{ old('color', $classification->color ?? '#ff0000') }}">
        </div>

        <button type="submit" class="btn btn-success" id="submitBtn">Update Classification</button>
        <a href="{{ route('superadmin.classifications') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<script>
    const category = document.getElementById('category_id');
    const button = document.getElementById('submitBtn');
    const classification = document.getElementById('name');
    
    function toggleButton() {
    if (category.value !== '' && classification.value !== '') {
        button.disabled = false;
    } else {
        button.disabled = true;
    }
    }

    category.addEventListener('change', toggleButton);
    classification.addEventListener('input', toggleButton);

</script>
@endsection