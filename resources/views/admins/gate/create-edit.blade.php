@extends('layouts.master', ['main' => 'Gate', 'title' => request()->routeIs('gate.create') ? 'Tambah Data Gerbang' : 'Edit Data Gerbang'])
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
                        <h3 class="fw-bold m-0">{{ request()->routeIs('gate.create') ? 'Tambah Data Gerbang' : 'Edit Data Gerbang' }}
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form class="form" method="POST" enctype="multipart/form-data" action="{{ request()->routeIs('gate.create') ? route('gate.store') : route('gate.update', @$gate->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body">
                            <div class="fv-row mb-6">
                                <label class="fs-6 fw-bold form-label mt-3" for="gym_place_id">
                                    <span class="required">Pilih Gym Place</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Pilih Gym Place untuk Gate ini"></i>
                                </label>
                                <select name="gym_place_id" id="gym_place_id" class="form-select mb-3 mb-lg-0" required>
                                    <option value="">-- Pilih Gym Place --</option>
                                    @foreach($gym_places as $gym_place)
                                        <option value="{{ $gym_place->id }}"
                                            @if((isset($gate) && $gate->gym_place_id == $gym_place->id) || old('gym_place_id') == $gym_place->id) selected @endif>
                                            {{ $gym_place->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="name">
                                    <span class="required">Nama Gate</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Nama Gate"></i>
                                </label>
                                <!--end::Label-->
                                <input type="text" name="name" id="name"
                                    class="form-control mb-3 mb-lg-0"
                                    placeholder="Nama Gate"
                                    value="{{ @$gate->name ?? old('name') }}" required />
                            </div>
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="address">
                                    <span class="required">Address/Url Tujuan</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Address/Url Tujuan"></i>
                                </label>
                                <!--end::Label-->
                                <textarea name="address" id="address" class="form-control mb-3 mb-lg-0" cols="30" rows="4" required>{{ @$gate->address ?? old('address') }}</textarea>
                            </div>
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="auth_user">
                                    <span class="required">Auth User</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Auth User"></i>
                                </label>
                                <!--end::Label-->
                                <input type="text" name="auth_user" id="auth_user"
                                    class="form-control mb-3 mb-lg-0"
                                    placeholder="Auth User"
                                    value="{{ @$gate->auth_user ?? old('auth_user') }}" required />
                            </div>
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="auth_pass">
                                    <span class="required">Auth Password</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Auth Password"></i>
                                </label>
                                <!--end::Label-->
                                <div class="input-group">
                                    <input type="password" name="auth_pass" id="auth_pass"
                                        class="form-control mb-3 mb-lg-0"
                                        placeholder="Auth Password"
                                        value="" />
                                    <span class="input-group-text">
                                        <i type="button" class="fas fa-eye" id="show-pass"></i>
                                        <i type="button" class="fas fa-eye-slash" id="hide-pass" style="display: none;"></i>
                                    </span>
                                </div>
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
                                    @if (request()->routeIs('gate.create'))
                                    <option value="1" selected>AKTIF</option>
                                    <option value="0">NON AKTIF</option>
                                    @else
                                    <option {{ @$gate->is_active == 1 ? 'selected' : '' }}
                                        value="1">AKTIF</option>
                                    <option {{ @$gate->is_active == 0 ? 'selected' : '' }}
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
                            <a href="{{ route('gate.index') }}"
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

@push('js')
<script>
    const showPass = document.getElementById('show-pass');
    const hidePass = document.getElementById('hide-pass');
    const passwordInput = document.getElementById('auth_pass');

    showPass.addEventListener('click', () => {
        passwordInput.type = 'text';
        showPass.style.display = 'none';
        hidePass.style.display = 'block';
    });

    hidePass.addEventListener('click', () => {
        passwordInput.type = 'password';
        showPass.style.display = 'block';
        hidePass.style.display = 'none';
    });
</script>
@endpush