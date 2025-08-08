@extends('layouts.master', ['main' => 'Promo', 'title' => request()->routeIs('promo.create') ? 'Tambah Promo' : 'Edit Promo'])
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
<!--begin::Content-->
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">{{
                                    request()->routeIs('promo.create') ? 'Tambah Promo' : 'Edit Promo' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form" action="{{ request()->routeIs('promo.create') ? route('promo.store') : route('promo.update', $promo->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" hidden name="id" value="{{@$promo->id}}">

                                <!-- SECTION: Data Promo -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Data Promo</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <x-form.image-upload label="Thumbnail" name="image" :value="@$promo->image ?? null" nullable='{{request()->routeIs("promo.create") ?  1 : 0}}' />
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                                        <span class="required">Nama Promo</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Nama Promo"></i>
                                                    </label>
                                                    <input type="text" class="form-control" name="name" id="name" value="{{ old('name', @$promo->name) }}" required />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="name_en">
                                                        <span class="required">Nama Promo (EN)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Nama Promo dalam bahasa inggris"></i>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="name_en" id="name_en" value="{{ old('name_en', @$promo->name_en) }}" required />
                                                        <button type="button" onclick="translateNameEnglish(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Inggris">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="name_cn">
                                                        <span class="required">Nama Promo (Chinese)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Nama Promo dalam bahasa chinese"></i>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="name_cn" id="name_cn" value="{{ old('name_cn', @$promo->name_cn) }}" required />
                                                        <button type="button" onclick="translateNameChinese(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Chinese">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-4 mt-2">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="subname">
                                                        <span class="required">Sub Nama Promo</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Sub Nama Promo"></i>
                                                    </label>
                                                    <input type="text" class="form-control" name="subname" id="subname" value="{{ old('subname', @$promo->subname) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="subname_en">
                                                        <span class="required">Sub Nama Promo (EN)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Sub Nama Promo dalam bahasa inggris"></i>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="subname_en" id="subname_en" value="{{ old('subname_en', @$promo->subname_en) }}" required />
                                                        <button type="button" onclick="translateSubnameEnglish(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Inggris">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="subname_cn">
                                                        <span class="required">Sub Nama Promo (Chinese)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Sub Nama Promo dalam bahasa chinese"></i>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="subname_cn" id="subname_cn" value="{{ old('subname_cn', @$promo->subname_cn) }}" required />
                                                        <button type="button" onclick="translateSubnameChinese(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Chinese">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-4 mt-2">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="gym_place_id">
                                                        <span class="required">Tempat Gym</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Tipe"></i>
                                                    </label>
                                                    <select name="gym_place_id" class="form-control">
                                                        <option value="" {{ @$promo?->gym_place_id == null ? 'selected' : '' }}>Semua Tempat Gym</option>
                                                        @foreach ($gym_places as $gym_place)
                                                        <option value="{{ $gym_place->id }}" {{@$gym_place->id == @$promo?->gym_place_id ? 'selected' : ''}}>{{$gym_place->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="club_type">
                                                        <span class="required">Tipe Club</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Tipe"></i>
                                                    </label>
                                                    <select name="club_type" class="form-control" required>
                                                        <option value="ALL" {{ @$promo?->club_type == 'ALL' ? 'selected' : '' }}>ALL CLUB</option>
                                                        <option value="SINGLE" {{ @$promo?->club_type == 'SINGLE' ? 'selected' : '' }}>SINGLE CLUB</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-4 mt-2">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="member_type">
                                                        <span class="required">Tipe Member</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Tipe"></i>
                                                    </label>
                                                    <select name="member_type" class="form-control">
                                                        <option value="">Semua Member</option>
                                                        @foreach ($member_types as $key => $type)
                                                        <option {{$key==@$promo?->member_type ? 'selected' : ''}} value="{{$key}}">{{$type}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                                        <span class="required">Tipe Promo</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Tipe"></i>
                                                    </label>
                                                    <select name="type" class="form-control" required>
                                                        <option value="">--Pilih Tipe--</option>
                                                        @foreach ($types as $key => $type)
                                                        <option {{$key==@$promo?->type ? 'selected' : ''}} value="{{$key}}">{{$type}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Data Promo -->

                                <!-- SECTION: Kode & Kuota -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Kode & Kuota</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="code">
                                                        <span class="required">Kode Promo</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Kode Promo"></i>
                                                    </label>
                                                    <input type="text" class="form-control" name="code" value="{{ old('code', @$promo->code) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="quota">
                                                        <span class="required">Kuota</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Kode Promo"></i>
                                                    </label>
                                                    <input type="number" min="0" class="form-control" name="quota" value="{{ old('quota', @$promo->quota) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="max_use">
                                                        <span class="required">Max. Penggunaan / User</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Max Penggunaan promo"></i>
                                                    </label>
                                                    <input type="number" min="0" class="form-control" name="max_use" value="{{ old('max_use', @$promo->max_use) }}" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Kode & Kuota -->

                                <!-- SECTION: Diskon -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Diskon</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="discount_type">
                                                        <span class="required">Tipe Diskon</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Tipe Diskon"></i>
                                                    </label>
                                                    <select name="discount_type" id="discount_type" class="form-control" required>
                                                        <option value="">--Pilih Tipe Diskon--</option>
                                                        <option {{@$promo->discount_type == 'PERCENT' ? 'selected' : ''}} value="PERCENT">Persen</option>
                                                        <option {{@$promo->discount_type == 'FIXED' ? 'selected' : ''}} value="FIXED">Nominal Tetap</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6 {{@$promo->discount_type == 'FIXED' ? 'd-none' : ''}}" id="input_discount_percent">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="discount_percent">
                                                        <span class="required">Diskon (%)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Diskon"></i>
                                                    </label>
                                                    <input type="number" id="discount_percent" min="1" step="0.1" class="form-control" name="discount_percent" value="{{ old('discount_percent', @$promo->discount_percent) }}" />
                                                </div>
                                            </div>
                                            <div class="col-md-6 {{@$promo->discount_type == 'FIXED' ? '' : 'd-none'}}" id="input_discount_fixed">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="discount_fixed">
                                                        <span class="required">Diskon (Rp)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Diskon"></i>
                                                    </label>
                                                    <input type="text" id="discount_fixed" class="form-control input-money" name="discount_fixed" value="{{ old('discount_fixed',  (int) @$promo->discount_fixed) }}" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-4 mt-2">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="min_purchase">
                                                        <span class="required">Min.Pembelian</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Minimal Pembelian"></i>
                                                    </label>
                                                    <input type="text" id="min_purchase" class="form-control input-money" name="min_purchase" value="{{ old('min_purchase', @$promo->min_purchase) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-6 {{@$promo->discount_type == 'FIXED' ? 'd-none' : ''}}" id="input_max_discount">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="max_discount">
                                                        <span class="required">Max.Diskon</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Max Diskon"></i>
                                                    </label>
                                                    <input type="text" id="max_discount" class="form-control input-money" name="max_discount" value="{{ old('max_discount', @$promo->max_discount) }}" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Diskon -->

                                <!-- SECTION: Periode -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Periode</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="start_date">
                                                        <span class="required">Tanggal Mulai Berlaku</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Tanggal Mulai Berlaku"></i>
                                                    </label>
                                                    <input type="date" id="start_date" class="form-control" name="start_date" value="{{ old('start_date', @$promo->start_date) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="start_time">
                                                        <span class="required">Jam Mulai</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Tanggal Expired"></i>
                                                    </label>
                                                    <input type="time" id="start_time" class="form-control" name="start_time" value="{{ old('start_time', @$promo->start_time) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="expiry_date">
                                                        <span class="required">Tanggal Expired</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Tanggal Expired"></i>
                                                    </label>
                                                    <input type="date" id="expiry_date" class="form-control" name="expiry_date" value="{{ old('expiry_date', @$promo->expiry_date) }}" required />
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="expiry_time">
                                                        <span class="required">Jam Expired</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input Tanggal Expired"></i>
                                                    </label>
                                                    <input type="time" id="expiry_time" class="form-control" name="expiry_time" value="{{ old('expiry_time', @$promo->expiry_time) }}" required />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Periode -->

                                <!-- SECTION: Syarat & Ketentuan -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Syarat & Ketentuan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-12 mb-3">
                                                <label class="fs-6 fw-bold form-label" for="term_and_condition">
                                                    <span class="required">Syarat & Ketentuan (Indonesia)</span>
                                                </label>
                                                <textarea class="form-control" id="term_and_condition" name="term_and_condition" rows="6">{{ old('term_and_condition', @$promo->term_and_condition) }}</textarea>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-6 fw-bold form-label" for="term_and_condition_en">
                                                    <span class="required">Syarat & Ketentuan (EN)</span>
                                                </label>
                                                <div class="input-group">
                                                    <textarea class="form-control" id="term_and_condition_en" name="term_and_condition_en" rows="4">{{ @$promo->term_and_condition_en ?? old('term_and_condition_en') }}</textarea>
                                                    <button type="button" onclick="translateTermAndConditionEnglish(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Inggris">
                                                        <i class="fas fa-language"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-6 fw-bold form-label" for="term_and_condition_cn">
                                                    <span class="required">Syarat & Ketentuan (Chinese)</span>
                                                </label>
                                                <div class="input-group">
                                                    <textarea class="form-control" id="term_and_condition_cn" name="term_and_condition_cn" rows="4">{{ @$promo->term_and_condition_cn ?? old('term_and_condition_cn') }}</textarea>
                                                    <button type="button" onclick="translateTermAndConditionChinese(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Chinese">
                                                        <i class="fas fa-language"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Syarat & Ketentuan -->

                                <!-- SECTION: Status -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                                        <span class="required">Status</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih status"></i>
                                                    </label>
                                                    <select name="is_active" id="is_active" class="form-control" required>
                                                        <option value="">--Pilih Status--</option>
                                                        <option {{@$promo->is_active == 1 ? 'selected' : ''}} value="1">AKTIF</option>
                                                        <option {{@$promo->is_active == 0 ? 'selected' : ''}} value="0">NON AKTIF</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="is_published">
                                                        <span class="required">Status Publish</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Pilih Status Publish"></i>
                                                    </label>
                                                    <select name="is_published" id="is_published" class="form-control" required>
                                                        <option value="">--Pilih Tipe Publish--</option>
                                                        <option {{@$promo->is_published == 1 ? 'selected' : ''}} value="1">Promo di Tampilkan Untuk Publik/ di Aplikasi</option>
                                                        <option {{@$promo->is_published == 0 ? 'selected' : ''}} value="0">Promo di Sembunyikan</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Status -->

                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('promo.index') }}">
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
<script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
<script>
    function translateNameEnglish(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        translate('#name', '#name_en');
        setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
    }
    
    function translateNameChinese(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        translateChinese('#name', '#name_cn');
        setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
    }

    function translateSubnameEnglish(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        translate('#subname', '#subname_en');
        setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
    }
    
    function translateSubnameChinese(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        translateChinese('#subname', '#subname_cn');
        setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
    }
</script>
<script>
    function delay(callback, ms) {
        var timer = 0;
        return function() {
            var context = this,
                args = arguments;
            clearTimeout(timer);
            timer = setTimeout(function() {
                callback.apply(context, args);
            }, ms || 0);
        };
    }

    function translateTermAndConditionEnglish() {
        let content = tinymce.get('term_and_condition').getContent();
        
        $.ajax({
            type: 'POST',
            url: "{{ route('translate_post') }}",
            data: {
                text: content
            },
            cache: false,
            success: function(msg) {
                tinymce.get('term_and_condition_en').setContent(msg);
            },
            error: function(data) {
                console.log('error:', data)
            },
        });
    }

    function translateTermAndConditionChinese() {
        let content = tinymce.get('term_and_condition').getContent();
        $.ajax({
            type: 'POST',
            url: "{{ route('translate_post.chinese') }}",
            data: {
                text: content
            },
            cache: false,
            success: function(msg) {
                tinymce.get('term_and_condition_cn').setContent(msg);
            },
            error: function(data) {
                console.log('error:', data)
            },
        });
    }

    tinymce.init({
        selector: '#term_and_condition, #term_and_condition_en, #term_and_condition_cn',
        height: 500,
        menubar: false,
        branding: false,
        toolbar: ["styleselect fontselect fontsizeselect",
        "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
        "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | code"
        ],
        plugins: "advlist autolink link image lists charmap print preview code"
    });

    $('#discount_type').on('change', function() {
        if (this.value == 'FIXED') {
            $('#input_discount_fixed').removeClass('d-none');
            $('#input_discount_percent').addClass('d-none');
            $('#input_max_discount').addClass('d-none');
        } else {
            $('#input_discount_percent').removeClass('d-none');
            $('#input_discount_fixed').addClass('d-none');
            $('#input_max_discount').removeClass('d-none');
        }
    })

    $(".input-money").on('keyup', function() {
        var n = parseInt($(this).val().replace(/\D/g, ''), 10) || 0
        if (n > 0) {
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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush