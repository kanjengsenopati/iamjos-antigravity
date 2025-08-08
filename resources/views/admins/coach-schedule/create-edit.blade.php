@extends('layouts.master', ['main' => 'Data Jadwal', 'title' => request()->routeIs('coach-schedule.create') ?
'Tambah
Agenda' : 'Edit Agenda'])
@section('content')
<!--begin::Content wrapper-->
<div class="d-flex pt-6 flex-column flex-column-fluid">
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Basic info-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">{{ request()->routeIs('coach-schedule.create') ? 'Tambah Jadwal' : 'Edit
                            Jadwal'
                            }}
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form class="form" method="POST" enctype="multipart/form-data" action="{{
                    request()->routeIs('coach-schedule.create') ? route('coach-schedule.store') : route('coach-schedule.update',
                        @$coachSchedule->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">Tanggal</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="date" name="date" id="date" class="form-control mb-3 mb-lg-0"
                                                placeholder="Tanggal" value="{{ @$coachSchedule->date ?? old('date') }}"
                                                required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="start_time" class="col-lg-4 col-form-label required fw-semibold fs-6">Waktu
                                    Mulai</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="time" name="start_time" id="start_time"
                                                class="form-control mb-3 mb-lg-0" placeholder="Waktu Mulai"
                                                value="{{ @$coachSchedule->start_time ?? old('start_time') }}"
                                                required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="end_time" class="col-lg-4 col-form-label required fw-semibold fs-6">Waktu
                                    Selesai</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="time" name="end_time" id="end_time"
                                                class="form-control mb-3 mb-lg-0" placeholder="Waktu Mulai"
                                                value="{{ @$coachSchedule->end_time ?? old('end_time') }}" required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="quota"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">Kouta</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="number" name="quota" id="quota"
                                                class="form-control mb-3 mb-lg-0" placeholder="Kouta"
                                                value="{{ @$coachSchedule->quota ?? old('quota') }}" required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>

                                @if (request()->routeIs('coach-schedule.create'))
                                <input type="hidden" name="personal_trainer_id" value="{{ request()->personal_trainer_id }}">
                                @endif
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="reason"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">Alasan</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <textarea name="reason" id="reason" class="form-control mb-3 mb-lg-0"
                                                placeholder="Alasan"
                                                required>{{ @$coachSchedule->reason ?? old('reason') }}</textarea>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                        </div>
                        <!--end::Card body-->
                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('coach-schedule.index') }}"
                                class="btn btn-secondary btn-sm me-3">Batal</a>
                            <button type="submit" class="btn btn-sm btn-primary"
                                id="kt_account_profile_details_submit">Simpan</button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Basic info-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
@endsection