@extends('layouts.master', ['title' => 'Data Conduct Commission', 'main' => 'Dashboard'])
@section('content')
<!--begin::Content wrapper-->
<div class="d-flex flex-column flex-column-fluid">
    <!--begin::Toolbar-->
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <!--begin::Toolbar container-->
        <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
            <!--begin::Page title-->
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <!--begin::Title-->
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Data Conduct Commission</h1>
                <!--end::Title-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('coach-commission.index') }}" class="text-muted text-hover-primary">Conduct
                            Commission</a>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-muted">{{
                        request()->routeIs('coach-commission.create') ? 'Tambah Conduct Commission' :
                        'Edit Conduct Commission' }}
                        <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
        </div>
        <!--end::Toolbar container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Basic info-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_profile_details" aria-expanded="true"
                    aria-controls="kt_account_profile_details">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">{{ request()->routeIs('coach-commission.create') ?
                            'Tambah Conduct Commission' : 'Edit Conduct Commission' }}</h3>
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form class="form" method="POST" enctype="multipart/form-data"
                        action="{{
                    request()->routeIs('coach-commission.create') ? route('coach-commission.store') : route('coach-commission.update', $personalTrainerConductCommission->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body border-top p-9">

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Coach Level</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <select name="personal_trainer_level_id" id="personal_trainer_level_id"
                                                class="form-select form-select-solid" required>
                                                <option value="">Pilih Coach Level</option>
                                                @foreach ($coachLevels as $coachLevel)
                                                <option value="{{ $coachLevel->id }}" {{
                                                    @$personalTrainerConductCommission->personal_trainer_level_id ==
                                                    $coachLevel->id ? 'selected' : '' }}>
                                                    {{ $coachLevel->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Tipe</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <select name="type" id="type" class="form-select form-select-solid"
                                                required>
                                                <option value="">Pilih Tipe</option>
                                                <option value="PERSONAL_TRAINER_SESSION" {{
                                                    @$personalTrainerConductCommission->type ==
                                                    'PERSONAL_TRAINER_SESSION' ? 'selected' : '' }}>
                                                    Personal Trainer Session</option>
                                                <option value="GYM_CLASS" {{ @$personalTrainerConductCommission->type ==
                                                    'GYM_CLASS' ? 'selected' : '' }}>
                                                    Gym Class</option>
                                                <option value="FITNESS_ASSESSMENT" {{
                                                    @$personalTrainerConductCommission->type ==
                                                    'FITNESS_ASSESSMENT' ? 'selected' : '' }}>
                                                    Fitness Assessment</option>
                                            </select>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Komisi</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="text" name="commission" id="commission"
                                                class="form-control input-money" placeholder="Komisi"
                                                value="{{ @$personalTrainerConductCommission->commission }}" required />
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
                            <a href="{{ route('coach-commission.index') }}"
                                class="btn btn-light btn-active-light-primary me-2">Batal</a>
                            <button type="submit" class="btn btn-primary"
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
@push('js')
<script>
    $(".input-money").on('keyup', function() {
        var n = parseInt($(this).val().replace(/\D/g, ''), 10) || 0
        if (n > 0) {
            // var value = n.toLocaleString()
            // $(this).val(value);
            var value = n.toLocaleString('en-US')
            $(this).val(value.replace(/\./g, ','));
        } else {
            $(this).val(0);
        }
    });
        
    $('form').on('submit', function(e) {
        $(".input-money").each(function() {
            var str = $(this).val();
            var newValue = str.replace(/,/g, '');
            $(this).val(newValue);
        });
    });
</script>
@endpush