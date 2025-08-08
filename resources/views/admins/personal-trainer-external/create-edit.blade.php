@extends('layouts.master', ['main' => 'Coach External', 'title' => request()->routeIs('personal-trainer-external.create') ? 'Tambah Coach External' : 'Edit Coach External'])
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
<!--begin::Content-->
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-xxl app-container">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">{{ request()->routeIs('personal-trainer-external.create') ? 'Tambah Coach External' : 'Edit Coach External' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form" action="{{ request()->routeIs('personal-trainer-external.create') ? route('personal-trainer-external.store') : route('personal-trainer-external.update', $personalTrainerExternal->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" name="id" hidden value="{{@$personalTrainerExternal->id}}">
                                <input type="text" name="gym_place_id" value="{{request()->gym_place_id ?? @$personalTrainerExternal->gym_place_id}}" required hidden>
                                <input type="hidden" name="route" value="{{ request()->route ?? 'personal-trainer-external' }}">
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <x-form.image-upload label="Avatar Image" name="avatar" :value="@$personalTrainerExternal->avatar" />
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Nama Coach External"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" id="name" class="form-control" name="name" value="{{ old('name', @$personalTrainerExternal->name) }}" required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Bio</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Bio"></i>
                                    </label>
                                    <!--end::Label-->
                                    <textarea type="text" id="bio" name="bio" class="form-control input-external-trainer">{{ old('bio', @$personalTrainerExternal?->bio) }}</textarea>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('gym-place.show',(request()->gym_place_id ?? @$personalTrainerExternal->gym_place_id).'?tab=personal_trainer') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
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
    <!--end::Post-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection