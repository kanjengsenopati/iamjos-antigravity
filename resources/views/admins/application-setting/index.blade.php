@extends('layouts.master', ['main' => 'Pengaturan Aplikasi','title' => 'Edit Pengaturan Aplikasi'])
@section('content')
<!--begin::Content-->
<div id="kt_app_content_container" class="app-container container-xxl pt-6">
    <!--begin::Basic info-->
    <div class="card mb-5 mb-xl-10">
        <!--begin::Card header-->
        <div class="card-header">
            <!--begin::Card title-->
            <div class="card-title m-0">
                <h3 class="fw-bold m-0">{{ request()->routeIs('application-setting.create') ? 'Tambah Tentang
                    Aplikasi' :
                    'Edit Pengaturan Aplikasi'
                    }}
                </h3>
            </div>
            <!--end::Card title-->
        </div>
        <!--begin::Card header-->
        <!--begin::Content-->
        <div id="kt_account_settings_profile_details" class="collapse show">
            <!--begin::Form-->
            <form class="form" method="POST" enctype="multipart/form-data"
                action="{{ route('application-setting.store') }}">
                @csrf
                <x-form.put-method />
                <x-alert.alert-validation />
                <!--begin::Card body-->
                <div class="card-body">

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="time_booking_gym_class">
                            <span class="required">Waktu Booking Kelas</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input waktu kapan booking kelas dapat dilakukan"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" placeholder="hh:mm" class="form-control time" name="time_booking_gym_class"
                            value="{{ old('time_booking_gym_class', @$applicationSetting?->time_booking_gym_class) }}"
                            required />
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-6">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label mt-3" for="payment_expiry_time">
                            <span class="required">Batas Pembayaran Expired</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input batas waktu pembayaran"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" placeholder="hh:mm" class="form-control time" name="payment_expiry_time"
                            value="{{ old('payment_expiry_time', @$applicationSetting?->payment_expiry_time) }}"
                            required />
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="payment_cancel_time_class">
                            <span class="required">Batas Pembatalan Kelas</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input batas waktu cancel pembelian kelas"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" placeholder="hh:mm" class="form-control time"
                            name="payment_cancel_time_class"
                            value="{{ old('payment_cancel_time_class', @$applicationSetting?->payment_cancel_time_class) }}"
                            required />
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="payment_cancel_time_pt">
                            <span class="required">Batas Pembatalan Booking Sesi Personal Trainer</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input batas waktu cancel pembelian personal trainer"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" placeholder="hh:mm" class="form-control time" name="payment_cancel_time_pt"
                            value="{{ old('payment_cancel_time_pt', @$applicationSetting?->payment_cancel_time_pt) }}"
                            required />
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="xendit_fee">
                            <span class="required">Payment Fee</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input biaya payment"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" class="form-control input-money" name="xendit_fee"
                            value="{{ old('xendit_fee', @$applicationSetting?->xendit_fee) }}" required />
                        <!--end::Input-->
                    </div>


                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="user_timeoff_fee">
                            <span class="required">Biaya Cuti User per Bulan</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input Biaya Cuti perbulan"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" class="form-control input-money" name="user_timeoff_fee"
                            value="{{ old('user_timeoff_fee', @$applicationSetting?->user_timeoff_fee) }}" required />
                        <!--end::Input-->
                    </div>


                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="annual_payment_fee">
                            <span class="required">Biaya Membership Tahunan</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input Biaya Membership Tahunan"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" class="form-control input-money" name="annual_payment_fee"
                            value="{{ old('annual_payment_fee', @$applicationSetting?->annual_payment_fee) }}"
                            required />
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="fitness_asessment_expiry_month">
                            <span class="required">Lama Masa Aktif Fitness Assessment (Bulan)</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input Lama Masa Aktif Membership Bonus Fitness Assessment (Bulan)"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="number" class="form-control input-money" name="fitness_asessment_expiry_month"
                            value="{{ old('fitness_asessment_expiry_month', @$applicationSetting?->fitness_asessment_expiry_month) }}"
                            required />
                        <!--end::Input-->
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="session_price_for_accrual">
                            <span class="required">Harga Sesi per Pertemuan (For Accrual Revenue)</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Masukkan Harga Sesi per Pertemuan untuk Menentukan harga Paket Coach/Coach Plus (For Accrual Revenue)"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" class="form-control input-money" name="session_price_for_accrual"
                            value="{{ old('session_price_for_accrual', @$applicationSetting?->session_price_for_accrual) }}" required />
                        <!--end::Input-->
                    </div>

                    <!-- Separator -->
                    <div class="separator my-10"></div>

                    {{-- add title authentikasi --}}
                    <div class="fv-row mb-7">
                        <h3 class="fw-bold">Authentikasi</h3>
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="whatsapp_login">
                            <span class="required">Whatsapp Login</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Aktifkan atau nonaktifkan login whatsapp"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <select class="form-select form-select-solid" name="whatsapp_login">
                            <option value="1" {{ old('whatsapp_login', @$applicationSetting?->whatsapp_login) == 1 ?
                                'selected' : '' }}>
                                Aktif</option>
                            <option value="0" {{ old('whatsapp_login', @$applicationSetting?->whatsapp_login) == 0 ?
                                'selected' : '' }}>
                                Tidak Aktif</option>
                        </select>
                        <!--end::Input-->
                    </div>
                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="qontak_token">
                            <span class="required">Qontak Token</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input token qontak"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <input type="text" class="form-control" name="qontak_token"
                            value="{{ old('qontak_token', @$applicationSetting?->qontak_token) }}" required />
                        <!--end::Input-->
                    </div>

                    <!-- Separator -->
                    <div class="separator my-10"></div>

                    <div class="fv-row mb-7">
                        <h3 class="fw-bold">Pengaturan Lainnya</h3>
                    </div>

                    <div class="row mb-7">
                        <!-- Kolom 1 -->
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="android_version">
                                <span class="required">Versi APP Android</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input versi app android"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control" name="android_version" placeholder="1.0.0"
                                value="{{ old('android_version', @$applicationSetting?->android_version) }}" required />
                            <!--end::Input-->
                        </div>
                        <!-- Kolom 2 -->
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="ios_version">
                                <span class="required">Versi APP IOS</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input versi app ios"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control" name="ios_version" placeholder="1.0.0"
                                value="{{ old('ios_version', @$applicationSetting?->ios_version) }}" required />
                            <!--end::Input-->
                        </div>
                    </div>

                    <div class="row mb-7">
                        <!-- Kolom 1 -->
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="android_force_update">
                                <span class="required">Force Update APP Android</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih apakah app android wajib diupdate atau tidak"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select class="form-select form-select-solid" name="android_force_update">
                                <option value="1" {{ old('android_force_update', @$applicationSetting?->
                                    android_force_update) == 1 ?
                                    'selected' : '' }}>
                                    Ya</option>
                                <option value="0" {{ old('android_force_update', @$applicationSetting?->
                                    android_force_update) == 0 ?
                                    'selected' : '' }}>
                                    Tidak</option>
                            </select>
                            <!--end::Input-->
                        </div>
                        <!-- Kolom 2 -->
                        <div class="col-md-6 fv-row">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="ios_force_update">
                                <span class="required">Force Update APP IOS</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih apakah app ios wajib diupdate atau tidak"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select class="form-select form-select-solid" name="ios_force_update">
                                <option value="1" {{ old('ios_force_update', @$applicationSetting?->ios_force_update) ==
                                    1 ?
                                    'selected' : '' }}>
                                    Ya</option>
                                <option value="0" {{ old('ios_force_update', @$applicationSetting?->ios_force_update) ==
                                    0 ?
                                    'selected' : '' }}>
                                    Tidak</option>
                            </select>
                            <!--end::Input-->
                        </div>
                    </div>

                    <div class="fv-row mb-7">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label" for="app_update_message">
                            <span class="required">Pesan Update APP</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input pesan update app"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea class="form-control" placeholder="Pesan Update APP" rows="5"
                            name="app_update_message">{{ old('app_update_message', @$applicationSetting?->app_update_message) }}</textarea>
                        <!--end::Input-->
                    </div>


                </div>
                <!--end::Card body-->
                <!--begin::Actions-->
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('application-setting.index') }}" class="btn btn-sm btn-secondary me-3">Batal</a>
                    <button type="submit" class="btn btn-sm btn-primary"
                        id="kt_account_profile_details_submit">Simpan</button>
                </div>
                <!--end::Actions-->
            </form>
            <!--end::Form-->
            <!--end::Form-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Basic info-->
</div>
<!--end::Content-->
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $('.time').mask('00:00', {
        reverse: true
    });
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

    $(':submit').on('click', function(e) {
        var x = $(".input-money");
        for (var i = 0; i < x.length; i++) {
            var str = x[i].value;
            x[i].value = str.replace(/,(?=\d{3})/g, '');
        }
    })
</script>
@endpush