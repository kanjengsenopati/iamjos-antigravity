@extends('layouts.master', ['main' => 'Data Scheduler Notif', 'title' =>
request()->routeIs('scheduler-notification.create') ? 'Tambah
Scheduler Notif' : 'Edit
Scheduler Notif'])
@section('content')
<!--begin::Content-->
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div id="kt_content_container" class="app-container container-xxl">
        <!--begin::Contacts App- Add New Contact-->
        <div class="row g-7">
            <!--begin::Content-->
            <div class="col-xl-12">
                <!--begin::Contacts-->
                <div class="card h-lg-100" id="kt_contacts_main">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3">{{ request()->routeIs('scheduler-notification.create')
                                ? 'Tambah Scheduler Notif' :
                                'Edit Scheduler Notif' }}</span>
                        </h3>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <x-alert.alert-validation />
                    <form class="form"
                        action="{{ request()->routeIs('scheduler-notification.create') ? route('scheduler-notification.store') : route('scheduler-notification.update', @$schedulerNotification->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.put-method />
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Form-->
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label" for="title">
                                    <span class="required text-dark">Judul</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Judul notifikasi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="title" class="form-control form-control-lg form-control-solid"
                                    placeholder="Judul notifikasi" value="{{ @$schedulerNotification->title }}"
                                    required />
                                <!--end::Input-->
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label" for="message">
                                    <span class="required text-dark">Isi Pesan</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Silahkan memilih akses yang diberikan"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <textarea name="message" class="form-control form-control-lg form-control-solid"
                                    placeholder="Isi pesan" rows="5">{{ @$schedulerNotification->message }}</textarea>
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label" for="message">
                                    <span class="required text-dark">Satuan Waktu</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Silahkan memilih satuan waktu"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="unit" class="form-select form-select-lg form-select-solid"
                                    data-control="select2">
                                    <option value="">Tidak ada</option>
                                    <option value="MINUTES" @if (@$schedulerNotification->unit == 'MINUTES') selected
                                        @endif>Menit</option>
                                    <option value="HOURS" @if (@$schedulerNotification->unit == 'HOURS') selected
                                        @endif>Jam</option>
                                    <option value="DAYS" @if (@$schedulerNotification->unit == 'DAYS') selected
                                        @endif>Hari</option>
                                </select>
                                <!--end::Input-->
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label" for="unit_value">
                                    <span class="required text-dark">Waktu Notifikasi</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Silahkan memilih waktu notifikasi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" name="unit_value"
                                    class="form-control form-control-lg form-control-solid"
                                    placeholder="Waktu notifikasi" value="{{ @$schedulerNotification->unit_value }}" />
                                <!--end::Input-->
                            </div>

                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label" for="is_active">
                                    <span class="required text-dark">Status</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Silahkan pilih status"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <select name="is_active" class="form-select form-select-lg form-select-solid"
                                    data-control="select2" data-placeholder="Pilih status" required>
                                    <option value="1" @if (@$schedulerNotification->is_active == 1) selected @endif>
                                        Aktif</option>
                                    <option value="0" @if (@$schedulerNotification->is_active == 0) selected @endif>
                                        Tidak Aktif</option>
                                </select>
                                <!--end::Input-->
                            </div>

                            <div class="fv-row mb-6">
                                <x-form.image-upload label="Foto" name="image"
                                    :value="@$schedulerNotification->avatar ?? null" nullable='1' />
                            </div>
                        </div>
                        <!--end::Card body-->
                        <!--begin::Action buttons-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <!--begin::Button-->
                            <a href="{{ route('scheduler-notification.index') }}">
                                <button type="button" data-kt-contacts-type="cancel"
                                    class="btn btn-secondary me-3">Batal</button>
                            </a>
                            <!--end::Button-->
                            <!--begin::Button-->
                            <button type="submit" data-kt-contacts-type="submit" class="btn btn-primary btn-sm">
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