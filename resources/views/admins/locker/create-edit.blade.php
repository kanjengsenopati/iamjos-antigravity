@extends('layouts.master', ['main' => 'Data Loker', 'title' => request()->routeIs('locker.create') ? 'Tambah
Loker' : 'Edit Loker'])
@section('content')
<!--begin::Container-->
<div id="kt_content_container" class="app-container container-xxl pt-6">
    <!--begin::Contacts App- Add New Contact-->
    <div class="row g-7">
        <!--begin::Content-->
        <div class="col-xl-12">
            <!--begin::Contacts-->
            <div class="card h-lg-100" id="kt_contacts_main">
                <!--begin::Card header-->
                <div class="card-header" id="kt_chat_contacts_header">
                    <!--begin::Card title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3">{{ request()->routeIs('loker.create') ? 'Tambah
                            Loker'
                            :
                            'Edit Loker' }}</span>
                    </h3>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-5">
                    <!--begin::Form-->
                    <x-alert.alert-validation />
                    <form id="locker"
                        action="{{ request()->routeIs('locker.create') ? route('locker.store') : route('locker.update', @$locker->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.put-method />
                        @if(request()->routeIs('locker.create'))
                        <div class="fv-row mb-6">
                            <label class="fs-6 fw-bold form-label" for="gym_place_id">
                                <span class="required text-dark">Pilih Gym Place</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih Gym Place"></i>
                            </label>
                            <select name="gym_place_id" id="gym_place_id" class="form-select" required>
                                <option value="">--Pilih Gym Place--</option>
                                @foreach($gym_places as $gymPlace)
                                    <option value="{{ $gymPlace->id }}" {{ request()->gym_place_id == $gymPlace->id ? 'selected' : '' }}>
                                        {{ $gymPlace->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name">
                                <span class="required text-dark">Nama Loker</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan nama loker"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: 1"
                                value="{{ @$locker->name ?? old('name') }}" required />
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label for="name" class="fs-6 fw-bold form-label mt-3" for="gender">
                                <span class="required text-dark">Jenis Kelamin</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih Jenis Kelamin"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="gender" id="gender" class="form-select">
                                <option value="">--Pilih Jenis Kelamin--</option>
                                <option value="MALE" {{ @$locker->gender == "MALE" ? 'selected' : '' }}>Laki-laki
                                </option>
                                <option value="FEMALE" {{ @$locker->gender == "FEMALE" ? 'selected' : '' }}>Perempuan
                                </option>
                            </select>
                            <!--end::Input-->
                        </div>
                        {{-- <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="type">
                                <span class="required text-dark">Status Loker</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Tipe Broadcast"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="is_available" class="form-select" id="is_available">
                                <option value="">--Pilih Status Loker--</option>
                                <option value="1" @if (@$locker->is_available == 1) selected @endif>Tersedia</option>
                                <option value="0" @if (@$locker->is_available == 0) selected @endif>Tidak Tersedia
                                </option>
                            </select>
                            <!--end::Input-->
                        </div> --}}
                        <!--begin::Action buttons-->
                        <div class="d-flex justify-content-end">
                            <!--begin::Button-->
                            <a href="{{ route('locker.index', ['gym_place_id' => request()->gym_place_id]) }}">
                                <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                            </a>
                            <!--end::Button-->
                            <!--begin::Button-->
                            <button type="submit" data-kt-contacts-type="submit" class="btn btn-primary btn-sm">
                                <span class="indicator-label">Simpan</span>
                                <span class="indicator-progress">Mohon Tunggu...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                            <!--end::Button-->
                        </div>

                        <!--end::Action buttons-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Contacts-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Contacts App- Add New Contact-->
</div>
<!--end::Container-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection