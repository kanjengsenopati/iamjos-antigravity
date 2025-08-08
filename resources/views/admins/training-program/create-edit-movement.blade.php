@extends('layouts.master', ['title' => request()->routeIs('movement.create') ? 'Tambah Movement' : 'Edit Movement'])

@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <div id="kt_content_container" class="app-container container-xxl">
        <div class="row g-7">
            <div class="col-xl-12">
                <div class="card h-lg-100">
                    <div class="card-header">
                        <h3 class="card-title align-items-start flex-column">
                            {{ request()->routeIs('movement.create') ? 'Tambah Movement' : 'Edit Movement' }}
                        </h3>
                    </div>
                    <div class="card-body pt-5">
                        <form action="{{ request()->routeIs('movement.create') ? route('movement.store') : route('movement.update', @$movement->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            @if(!request()->routeIs('movement.create'))
                            @method('PUT')
                            @endif

                            <!-- Name -->
                            <div class="fv-row mb-6">
                                <label for="name" class="fs-6 fw-bold form-label mt-3">Nama Movement</label>
                                <input type="text" name="name" id="name" class="form-control"
                                    value="{{ old('name', @$movement->name) }}" required>
                            </div>

                            <!-- Category -->
                            <select name="category_id" class="form-control" required>
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ isset($movement) && $movement->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                                @endforeach
                            </select>


                            <!-- Image Upload -->
                            <div class="fv-row mb-6">
                                <label class="fs-6 fw-bold form-label mt-3">Upload Gambar</label>
                                <input type="file" name="image" class="form-control">
                                @if(!empty($movement->image_path))
                                <div class="mt-3">
                                    <img src="{{ asset('storage/' . $movement->image_path) }}" alt="Image" width="100">
                                </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('movement.index') }}">
                                    <button type="button" class="btn btn-secondary btn-sm me-3">Batal</button>
                                </a>
                                <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection