@extends('layouts.master', ['main' => 'Cuti PT', 'title' => request()->routeIs('personal-trainer-timeoff.create') ?
'Tambah Cuti Personal Trainer' : 'Edit Cuti Personal Trainer'])
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ request()->routeIs('personal-trainer-timeoff.create') ? 'Tambah Cuti Personal
                                    Trainer'
                                    : 'Edit
                                    Cuti Personal Trainer' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form"
                                action="{{ request()->routeIs('personal-trainer-timeoff.create') ? route('personal-trainer-timeoff.store') : route('personal-trainer-timeoff.update', $personalTrainerTimeoff->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" name="id" hidden value="{{ @$personalTrainer->id }}">
                                {{-- <input type="text" name="gym_place_id"
                                    value="{{ request()->gym_place_id ?? @$personalTrainer->gym_place_id }}" required
                                    hidden> --}}
                                <!--begin::Input group-->
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Personal Trainer</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Nama Personal Trainer"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select name="personal_trainer_id" id="personal_trainer_id" class="form-control"
                                        required>
                                        <option value="">--Pilih Personal Trainer--</option>
                                        @foreach ($personalTrainers as $personalTrainer)
                                        <option {{ $personalTrainer->id == @$personalTrainerTimeoff->personal_trainer_id
                                            ? 'selected' : '' }} value="{{
                                            $personalTrainer->id }}">{{ $personalTrainer->name }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="start_date">
                                        <span class="required">Tanggal Mulai</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Tanggal Mulai"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="date" id="start_date" class="form-control" name="start_date"
                                        value="{{ old('start_date', @$personalTrainerTimeoff->start_date) }}"
                                        required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="end_date">
                                        <span class="required">Tanggal Selesai</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Tanggal Selesai"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="date" id="end_date" class="form-control" name="end_date"
                                        value="{{ old('end_date', @$personalTrainerTimeoff->end_date) }}" required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="description">
                                        <span class="required text-dark">Deskripsi Cuti</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Deskripsi"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea class="form-control" id="description"
                                        name="description">{{ old('description', @$personalTrainerTimeoff->description) }}</textarea>
                                    <!--end::Input-->
                                </div>
                                <!--begin::Input group-->
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a
                                        href="{{ route('gym-place.show', (request()->gym_place_id ?? @$personalTrainerTimeoff->gym_place_id) . '?tab=personal_trainer_timeoff') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
{{-- @push('js')
<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
<script>
    tinymce.init({
            selector: '#description',
            height: 350,
            branding: false,
            menubar: false,
            toolbar: ["styleselect fontselect fontsizeselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"
            ],
            plugins: "advlist autolink link image lists charmap print preview code"
        });
</script>
@endpush --}}