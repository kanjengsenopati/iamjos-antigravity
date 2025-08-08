@extends('layouts.master', ['main' => 'Gate Card', 'title' => request()->routeIs('gate-card.create') ? 'Tambah Kartu Gerbang' : 'Edit Kartu Gerbang'])
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
                        <h3 class="fw-bold m-0">{{ request()->routeIs('gate-card.create') ? 'Tambah Kartu Gerbang' : 'Edit Kartu Gerbang' }}
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form class="form" method="POST" enctype="multipart/form-data" action="{{
                    request()->routeIs('gate-card.create') ? route('gate-card.store') : route('gate-card.update',
                        @$gateCard->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body">

                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="card_owner">
                                    <span class="required">Nama Pemilik Kartu</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Nama Pemilik Kartu"></i>
                                </label>
                                <!--end::Label-->
                                <input type="text" name="card_owner" id="card_owner"
                                    class="form-control mb-3 mb-lg-0"
                                    placeholder="Nama Pemilik Kartu"
                                    value="{{ @$gateCard->card_owner ?? old('card_owner') }}" required />
                            </div>
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="card_number">
                                    <span class="required">Nomor Kartu</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Nomor Kartu"></i>
                                </label>
                                <!--end::Label-->
                                <input type="text" name="card_number" id="card_number"
                                    class="form-control mb-3 mb-lg-0"
                                    placeholder="Nomor Kartu"
                                    value="{{ @$gateCard->card_number ?? old('card_number') }}" required />
                            </div>
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                    <span class="required">Status</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Pilih status"></i>
                                </label>
                                <!--end::Label-->
                                <select name="is_active" id="is_active" class="form-control" required>
                                    <option value="">--Pilih Status Aktif--</option>
                                    @if (request()->routeIs('gate-card.create'))
                                    <option value="1" selected>AKTIF</option>
                                    <option value="0">NON AKTIF</option>
                                    @else
                                    <option {{ @$gateCard->is_active == 1 ? 'selected' : '' }}
                                        value="1">AKTIF</option>
                                    <option {{ @$gateCard->is_active == 0 ? 'selected' : '' }}
                                        value="0">NON AKTIF</option>
                                    @endif
                                </select>
                            </div>
                            <!--end::Input group-->
                            <!--end::Input group-->
                        </div>
                        <!--end::Card body-->
                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('gate-card.index') }}"
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