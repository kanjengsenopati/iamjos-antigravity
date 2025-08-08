@extends('layouts.master', ['main' => 'Riwayat Coach Plus', 'title' => request()->routeIs('gym-class-bundling-history.create') ? 'Tambah Riwayat Coach Plus' : 'Edit Riwayat Coach Plus'])
@section('content')
<!--begin::Content--> 
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post mt-6 d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
            <!--begin::Contacts App- Add New Contact-->
            <div class="row g-7">
                <!--begin::Content-->
                <div class="col-xl-12">
                    <!--begin::Contacts-->
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title">
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">{{
                                    request()->routeIs('gym-class-bundling-history.create') ? 'Tambah Riwayat Coach Plus' : 'Edit Riwayat Coach Plus' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form" action="{{ request()->routeIs('gym-class-bundling-history.create') ? route('gym-class-bundling-history.store') : route('gym-class-bundling-history.update', $gymClassBundlingHistory->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" hidden name="id" value="{{@$gymClassBundlingHistory->id}}">
                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="user_id">
                                        <span class="required">User</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Membership"></i>
                                    </label>
                                    <!--end::Label-->
                                    <input type="text" id="user_id" value="{{$gymClassBundlingHistory->user->name}}" class="form-control" readonly>
                                </div>
                                <!--begin::Input group-->
                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="gym_class_bundling_id">
                                        <span class="required">Coach Plus</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Coach Plus"></i>
                                    </label>
                                    <!--end::Label-->
                                    <input type="text" class="form-control" readonly value="{{@$gymClassBundlingHistory?->gym_class_bundling?->name}}">
                                </div>
                                <!--end::Input group-->
                                <div class="row">
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="start_active_at">
                                                <span class="required">Tanggal Mulai Berlangganan</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Tanggal Mulai Berlangganan"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="date" id="start_active_date" class="form-control" name="start_active_date" value="{{ old('start_active_date', @$gymClassBundlingHistory->start_active_date) }}" required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-7">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="expiry_date">
                                                <span class="">Tanggal Expired</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Tanggal Expired"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="date" id="expiry_date" class="form-control" name="expiry_date" value="{{ old('expiry_date', @$gymClassBundlingHistory->expiry_date) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-7">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                        <span class="required">Status</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih status"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="is_active" id="is_active" class="form-control" required>
                                        <option value="">--Pilih Status Aktif--</option>
                                        <option {{@$gymClassBundlingHistory->is_active == 1 ? 'selected' : ''}} value="1">AKTIF</option>
                                        <option {{@$gymClassBundlingHistory->is_active == 0 ? 'selected' : ''}} value="0">NON AKTIF
                                        </option>
                                    </select>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('gym-place.show',(request()->gym_place_id ?? @$gymClassBundlingHistory->gym_class_bundling->gym_place_id).'?tab=gym_class_bundling_history') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
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
@push('js')

@endpush