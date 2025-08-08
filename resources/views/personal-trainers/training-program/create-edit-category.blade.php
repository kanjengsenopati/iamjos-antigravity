@extends('layouts.pt-master', ['title' => request()->routeIs('trainer.category-movement.create') ? 'Tambah Kategori' : 'Edit Kategori'])

@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <div id="kt_content_container" class="app-container container-xxl">
        <div class="row g-7">
            <div class="col-xl-12">
                <div class="card h-lg-100">
                    <div class="card-header">
                        <h3 class="card-title align-items-start flex-column">
                            {{ request()->routeIs('trainer.category-movement.create') ? 'Tambah Kategori' : 'Edit Kategori' }}
                        </h3>
                    </div>
                    <div class="card-body pt-5">
                        <form action="{{ request()->routeIs('trainer.category-movement.create') ? route('trainer.category-movement.store') : route('trainer.category-movement.update', @$category->id) }}"
                              method="POST">
                            @csrf
                            @if(!request()->routeIs('trainer.category-movement.create'))
                                @method('PUT')
                            @endif

                            <!-- Name -->
                            <div class="fv-row mb-6">
                                <label for="name" class="fs-6 fw-bold form-label mt-3">Nama Kategori</label>
                                <input type="text" name="name" id="name" class="form-control"
                                       value="{{ old('name', @$category->name) }}" required>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('trainer.category-movement.index') }}">
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
