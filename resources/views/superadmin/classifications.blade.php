@extends('layouts.app')

@section('page_title', 'Manage Classifications')

@section('content')
<div class="container">

    <h2 class="mb-4 text-danger">Manage Classifications</h2>

    <!-- Form to add classification -->
    <form action="{{ route('superadmin.classifications.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="category_id" class="form-label">Category</label>
            <select name="category_id" id="category_id" class="form-control">
                <option value="">--Select Category--</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Classification Name</label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Enter classification name">
        </div>

        <div class="mb-3">
            <label for="color" class="form-label">Color (optional)</label>
            <input type="color" name="color" id="color" class="form-control" value="#ff0000">
        </div>

        <button type="submit" class="btn btn-danger">Add Classification</button>
    </form>

    <hr>

    <!-- List existing classifications -->
    <h4 class="mt-4">Existing Classifications</h4>
    <ul>
        @foreach($classifications as $c)
            <li>{{ $c->category->name }} → {{ $c->name }} <span style="background: {{ $c->color }};">&nbsp;&nbsp;&nbsp;</span></li>
        @endforeach
    </ul>

</div>
@endsection