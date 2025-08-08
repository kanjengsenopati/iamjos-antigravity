@extends('layouts.master', ['main' => 'Membership', 'title' => request()->routeIs('membership.create') ? 'Tambah
Membership' : 'Edit Membership'])
@section('content')
<!--begin::Content-->
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="d-flex flex-column-fluid" id="kt_post">
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ request()->routeIs('membership.create') ? 'Tambah Membership' : 'Edit Membership'
                                    }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-3">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form"
                                action="{{ request()->routeIs('membership.create') ? route('membership.store') : route('membership.update', $membership->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" hidden name="id" value="{{ @$membership->id }}">
                                <input type="text" name="gym_place_id"
                                    value="{{ request()->gym_place_id ?? @$membership->gym_place_id }}" required hidden>

                                <!-- SECTION: Data Membership -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Data Membership</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <x-form.image-upload label="Thumbnail" name="thumbnail"
                                                    :value="@$membership->thumbnail" />
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                                        <span class="required">Nama Paket</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Input Nama Paket"></i>
                                                    </label>
                                                    <input type="text" name="name" id="name"
                                                        value="{{ old('name', @$membership->name) }}" class="form-control" required>
                                                    <div class="invalid-feedback">Nama paket wajib diisi.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="name_en">
                                                        <span class="required">Nama Paket (English)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Input Nama Paket"></i>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" name="name_en" id="name_en"
                                                            value="{{ old('name_en', @$membership->name_en) }}" class="form-control"
                                                            required>
                                                        <button type="button" onclick="translateNameEnglish(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Inggris">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback">Nama paket (English) wajib diisi.</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="name_cn">
                                                        <span class="required">Nama Paket (Chinese)</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Input Nama Paket"></i>
                                                    </label>
                                                    <div class="input-group">
                                                        <input type="text" name="name_cn" id="name_cn"
                                                            value="{{ old('name_cn', @$membership->name_cn) }}" class="form-control"
                                                            required>
                                                        <button type="button" onclick="translateNameChinese(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Chinese">
                                                            <i class="fas fa-language"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback">Nama paket (Chinese) wajib diisi.</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row g-4 mt-2">
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-6 fw-bold form-label mt-3" for="type">
                                                    <span class="required">Tipe</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Tipe Periode"></i>
                                                </label>
                                                <select name="type" id="type" class="form-control" required>
                                                    <option value="">Pilih Tipe Periode</option>
                                                    @foreach ($types as $key => $type)
                                                    <option {{ $key==@$membership->type ? 'selected' : '' }}
                                                        value="{{ $key }}">{{ $type }}</option>
                                                        @endforeach
                                                    </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="fs-6 fw-bold form-label mt-3" for="club_type">
                                                    <span class="required">Tipe Club</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Pilih apakah berlaku untuk all club atau single club saja"></i>
                                                </label>
                                                <select name="club_type" id="club_type" class="form-control" required>
                                                    <option value="ALL" {{ @$membership->club_type == 'ALL' ? 'selected' : '' }}>ALL CLUB</option>
                                                    <option value="SINGLE" {{ @$membership->club_type == 'SINGLE' ? 'selected' : '' }}>SINGLE CLUB</option>
                                                </select>
                                            </div>
                                            <div class="col-sm-6  {{ in_array(@$membership->type, ['LIMIT_DAY', 'LIMIT_DAY_AND_SESSION']) ? '' : 'd-none' }}"
                                                id="form-group-periode">
                                                <!--begin::Label-->
                                                <label class="fs-6 fw-bold form-label mt-3" for="period">
                                                    <span class="required">Lama Berlangganan (Hari)</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Input Hari"></i>
                                                </label>
                                                <!--end::Label-->
                                                <input type="number" min="1" name="period" id="period"
                                                    value="{{ old('period', @$membership->period) }}" class="form-control">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Data Membership -->

                                <!-- SECTION: Harga & Diskon -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Harga & Diskon</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-2">
                                                <label class="fs-6 fw-bold form-label mt-3" for="membership_period">
                                                    <span class="required">Periode Harga</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Input Harga Paket"></i>
                                                </label>
                                                <input type="number" name="membership_period"
                                                    value="{{ old('membership_period', @$membership->membership_period ?? 1) }}"
                                                    class="form-control input-money" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="fs-6 fw-bold form-label mt-3" for="price">
                                                    <span class="required">Harga</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Input Harga Paket"></i>
                                                </label>
                                                <input type="text" name="price"
                                                    value="{{ old('price', @$membership->price) }}"
                                                    class="form-control input-money" required>
                                            </div>
                                            <div class="col-md-5">
                                                <label class="fs-6 fw-bold form-label mt-3" for="discount_price">
                                                    <span>Harga Diskon</span>
                                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                        title="Input Harga Diskon"></i>
                                                </label>
                                                <input type="text" name="discount_price" id="discount_price"
                                                    value="{{ old('discount_price', @$membership->discount_price) }}"
                                                    class="form-control input-money">
                                            </div>
                                        </div>
                                        <div class="row g-4 mt-2 {{ old('discount_price', @$membership->discount_price) > 0 ? '' : 'd-none' }}"
                                            id="form-group-discount">
                                            <div class="col-md-3">
                                                <label class="fs-6 fw-bold form-label mt-3" for="start_date_discount">
                                                    <span class="required">Tanggal Mulai Diskon</span>
                                                </label>
                                                <input type="date" id="start_date_discount"
                                                    class="form-control discount_date" name="start_date_discount"
                                                    value="{{ old('start_date_discount', @$membership->start_date_discount) }}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="fs-6 fw-bold form-label mt-3" for="start_time_discount">
                                                    <span class="required">Jam Mulai Diskon</span>
                                                </label>
                                                <input type="time" id="start_time_discount"
                                                    class="form-control discount_date" name="start_time_discount"
                                                    value="{{ old('start_time_discount', @$membership->start_time_discount) }}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="fs-6 fw-bold form-label mt-3" for="end_date_discount">
                                                    <span class="required">Tanggal Selesai Diskon</span>
                                                </label>
                                                <input type="date" id="end_date_discount" class="form-control discount_date"
                                                    name="end_date_discount"
                                                    value="{{ old('end_date_discount', @$membership->end_date_discount) }}" />
                                            </div>
                                            <div class="col-md-3">
                                                <label class="fs-6 fw-bold form-label mt-3" for="end_time_discount">
                                                    <span class="required">Jam Selesai Diskon</span>
                                                </label>
                                                <input type="time" id="end_time_discount" class="form-control discount_date"
                                                    name="end_time_discount"
                                                    value="{{ old('end_time_discount', @$membership->end_time_discount) }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Harga & Diskon -->

                                <!-- SECTION: Benefit -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Benefit</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-4">
                                                <label class="fs-6 fw-bold form-label mt-3" for="benefit">
                                                    <span class="required">Benefit</span>
                                                </label>
                                                <select name="benefits[]" id="benefit" multiple class="form-control select2"
                                                    required>
                                                    @foreach ($benefits as $benefit)
                                                    <option {{ in_array($benefit->name,
                                                        @$membership?->membership_benefits?->pluck('name')?->toArray() ?? [])
                                                        ? 'selected'
                                                        : '' }}
                                                        value="{{ $benefit->name }}">{{ $benefit->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fs-6 fw-bold form-label mt-3" for="en_benefit">
                                                    <span class="required">Benefit (English)</span>
                                                </label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <select name="en_benefits[]" id="en_benefit" multiple
                                                        class="form-control select2">
                                                        @foreach ($enBenefits as $benefit)
                                                        <option {{ in_array($benefit->name,
                                                            @$membership?->en_membership_benefits?->pluck('name')?->toArray() ??
                                                            [])
                                                            ? 'selected'
                                                            : '' }}
                                                            value="{{ $benefit->name }}">{{ $benefit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" onclick="translateBenefitsEnglish(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Inggris"><i class="fas fa-language"></i></button>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="fs-6 fw-bold form-label mt-3" for="cn_benefit">
                                                    <span class="required">Benefit (Chinese)</span>
                                                </label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <select name="cn_benefits[]" id="cn_benefit" multiple
                                                        class="form-control select2">
                                                        @foreach ($cnBenefits as $benefit)
                                                        <option {{ in_array($benefit->name,
                                                            @$membership?->cn_membership_benefits?->pluck('name')?->toArray() ??
                                                            [])
                                                            ? 'selected'
                                                            : '' }}
                                                            value="{{ $benefit->name }}">{{ $benefit->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" onclick="translateBenefitsChinese(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Chinese"><i class="fas fa-language"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Benefit -->

                                <!-- SECTION: Deskripsi -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Deskripsi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="fs-6 fw-bold form-label mt-3" for="description">
                                                <span class="required">Deskripsi</span>
                                            </label>
                                            <textarea class="form-control" id="description" name="description"
                                                required>{{ old('description', @$membership->description) }}</textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fs-6 fw-bold form-label mt-3" for="description_en">
                                                <span class="required">Deskripsi (English)</span>
                                            </label>
                                            <div class="input-group">
                                                <textarea class="form-control" id="description_en" name="description_en"
                                                    required>{{ old('description_en', @$membership->description_en) }}</textarea>
                                                <button type="button" onclick="translateDescriptionEnglish(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Inggris"><i class="fas fa-language"></i></button>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="fs-6 fw-bold form-label mt-3" for="description_cn">
                                                <span class="required">Deskripsi (Chinese)</span>
                                            </label>
                                            <div class="input-group">
                                                <textarea class="form-control" id="description_cn" name="description_cn"
                                                    required>{{ old('description_cn', @$membership->description_cn) }}</textarea>
                                                <button type="button" onclick="translateDescriptionChinese(this)" class="btn btn-outline-secondary" title="Terjemahkan ke Chinese"><i class="fas fa-language"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Deskripsi -->

                                <!-- SECTION: Status -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Status</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4">
                                            <div class="col-md-4">
                                                <div class="fv-row mb-6">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                                        <span class="required">Status</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Pilih status"></i>
                                                    </label>
                                                    <select name="is_active" id="is_active" class="form-control" required>
                                                        <option value="">--Pilih Status--</option>
                                                        <option {{ @$membership->is_active == 1 ? 'selected' : '' }} value="1">
                                                            AKTIF</option>
                                                        <option {{ @$membership->is_active == 0 ? 'selected' : '' }} value="0">
                                                            NON AKTIF
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="fv-row mb-6">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                                        <span class="required">Status Publish</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Pilih Status Publish"></i>
                                                    </label>
                                                    <select name="is_published" id="is_published" class="form-control" required>
                                                        <option value="">--Pilih Tipe Publish--</option>
                                                        <option {{ @$membership->is_published == 1 ? 'selected' : '' }}
                                                            value="1">Membership di Tampilkan Untuk Publik/ di Aplikasi</option>
                                                        <option {{ @$membership->is_published == 0 ? 'selected' : '' }}
                                                            value="0">Membership di Sembunyikan</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="fv-row mb-6">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="is_renew_period_price">
                                                        <span class="required">Status Harga Membership Per Periode</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Menentukan apakah membership menggunakan harga periode saat pembelian atau harga terkini, ketika melakukan pembelian Renew membership"></i>
                                                    </label>
                                                    <select name="is_renew_period_price" id="is_renew_period_price" class="form-control" required>
                                                        <option value="">--Pilih Status Harga Membership Per Periode--</option>
                                                        <option {{ @$membership->is_renew_period_price == 1 ? 'selected' : '' }} value="1">
                                                            AKTIF</option>
                                                        <option {{ @$membership->is_renew_period_price == 0 ? 'selected' : '' }} value="0">
                                                            NON AKTIF
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- END SECTION: Status -->

                                <!-- SECTION: Reward -->
                                <div class="card mb-4">
                                    <div class="card-header bg-secondary d-flex align-items-center">
                                        <h5 class="mb-0">Reward</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="shop_product_id">
                                                <span class="required">Reward Produk</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Produk Sebagai Reward Membership"></i>
                                            </label>
                                            <!--end::Label-->
                                            <select name="shop_product_id[]" class="form-select mb-3" id="select2"
                                                data-control="select2" data-allow-clear="true" multiple="multiple">
                                                @foreach ($shopProducts as $shopProduct)
                                                <option value="{{ $shopProduct->id }}" @if (in_array(@$shopProduct->id,
                                                    @$shopProductValues)) selected @endif>
                                                    {{ $shopProduct->name }}
                                                </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="fv-row mb-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="fs-6 fw-bold form-label mt-3" for="is_extra_month_membership">
                                                        <span class="required">Reward Extra Bulan Membership</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Reward Extra Bulan Membership"></i>
                                                    </label>
                                                    <!--begin::Switch-->
                                                    <div class="form-check form-switch form-check-custom form-check-solid me-10">
                                                        <label class="fs-6 fw-bold form-label mt-2 me-3" for="autotimezone">Tidak</label>
                                                        <input type="hidden" name="is_extra_month_membership" value="0" />
                                                        <input class="form-check-input h-30px w-50px me-3" name="is_extra_month_membership" id="is_extra_month_membership" type="checkbox" value="1" @checked(@$membership->is_extra_month_membership ? true : false) />
                                                        <label class="fs-6 fw-bold form-label ml-3 mt-2" for="autotimezone">Ya</label>
                                                    </div>
                                                    <!--begin::Switch-->
                                                </div>
                                                <div class="col-md-6">
                                                    <!--begin::Label-->
                                                    <label class="fs-6 fw-bold form-label mt-3" for="extra_month_membership">
                                                        <span class="required">Extra Bulan Membership</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="input Total Extra Bulan Membership"></i>
                                                    </label>
                                                    <!--end::Label-->
                                                    <input type="number" name="extra_month_membership"
                                                        value="{{ old('extra_month_membership', @$membership->extra_month_membership ?? 0) }}"
                                                        class="form-control" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="fv-row mb-6">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <!--begin::Label-->
                                                    <label class="fs-6 fw-bold form-label mt-3" for="free_fitness_assessment">
                                                        <span class="required">Free Fitness Assessment</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Free Fitness Assessment"></i>
                                                    </label>
                                                    <!--end::Label-->
                                                    <input type="number" name="free_fitness_assessment"
                                                        value="{{ old('free_fitness_assessment', @$membership->free_fitness_assessment ?? 0) }}"
                                                        class="form-control" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <!--begin::Label-->
                                                    <label class="fs-6 fw-bold form-label mt-3" for="free_fitness_assessment">
                                                        <span class="required">Tipe Mendapatkan Fitness Assessment</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Tipe Fitness Assessment"></i>
                                                    </label>
                                                    <!--end::Label-->
                                                    <div class="mt-1">
                                                        <div class="mb-2">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="0" id="flexCheckDefault1" name="is_free_fitness_assessment_for_new_members" @checked(@$membership->is_free_fitness_assessment_for_new_members == false ? true : false) />
                                                                <label class="fs-6 fw-bold form-label" for="flexCheckDefault1">
                                                                    Mendapatkan Fitness Assessment Untuk Setiap Transaksi
                                                                </label>
                                                            </div>
                                                        </div>
        
                                                        <div class="mb-0">
                                                            <div class="form-check">
                                                                <input class="form-check-input" type="radio" value="1" id="flexCheckChecked1" name="is_free_fitness_assessment_for_new_members" @checked(@$membership->is_free_fitness_assessment_for_new_members == true ? true : false) />
                                                                <label class="fs-6 fw-bold form-label" for="flexCheckChecked1">
                                                                    Mendapatkan Fitness Assessment Untuk Member Baru
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="fv-row mb-6">
                                            <!--begin::Col-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="free_fitness_assessment">
                                                <span class="required">Mendapatkan Bonus dari Kode Referral</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Mendapatkan Bonus dari Kode Referral Ketika Pendaftaran"></i>
                                            </label>
                                            <!--end::Col-->
                                            <!--begin::Col-->
                                            <!--begin::Switch-->
                                            <div class="form-check form-switch form-check-custom form-check-solid me-10">
                                                <label class="fs-6 fw-bold form-label mt-2 me-3" for="is_referral_bonus_active">Tidak</label>
                                                <input type="hidden" name="is_referral_bonus_active" value="0" />
                                                <input class="form-check-input h-30px w-50px me-3" name="is_referral_bonus_active" type="checkbox" id="is_referral_bonus_active" value="1" @checked(@$membership->is_referral_bonus_active ?? false) />
                                                <label class="fs-6 fw-bold form-label ml-3 mt-2" for="is_referral_bonus_active">Ya</label>
                                            </div>
                                            <!--begin::Switch-->
                                            <!--end::Col-->
                                        </div>
                                        
                                        <div class="fv-row mb-6 card mt-4" 
                                            @if (@$membership->is_referral_bonus_active == false || request()->routeIs('membership.create'))
                                                style="display: none"
                                            @else
                                                style="display: block"
                                            @endif
                                            id="referralBonus">
                                            <div class="card-body">
                                                <h5 class="card-title">Bonus Kode Referral</h5>
                                                <div class="col-md-12">
                                                    <!--begin::Label-->
                                                    <label class="fs-6 fw-bold form-label mt-3" for="referral_owner_bonus_max">
                                                        <span class="required">Maksimal Klaim Referral Kode per User</span>
                                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                            title="Maksimal Klaim Referral Kode per User"></i>
                                                    </label>
                                                    <!--end::Label-->
                                                    <input type="number" name="referral_owner_bonus_max"
                                                        value="{{ old('referral_owner_bonus_max', @$membership->referral_owner_bonus_max ?? 0) }}"
                                                        class="form-control" >
                                                </div>

                                                <div class="row mt-3">
                                                    <div class="card col-md-6 p-6 bg-secondary">
                                                        <h5 class="card-title mb-3">Bonus Pemilik Kode Referral</h5>
                                                        <div class="fv-row mb-6">
                                                            <!--begin::Label-->
                                                            <label class="fs-6 fw-bold form-label mt-3" for="referral_owner_bonus_type">
                                                                <span class="required">Tipe Bonus</span>
                                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                                    title="Pilih Tipe Bonus"></i>
                                                            </label>
                                                            <!--end::Label-->
                                                            <select name="referral_owner_bonus_type" id="referral_owner_bonus_type" class="form-control" onchange="referralOwnerBonusType(this.value)" >
                                                                <option value="">--Pilih Tipe Bonus--</option>
                                                                <option {{ @$membership->referral_owner_bonus_type == "EXTEND_MEMBERSHIP_PERIOD" ? 'selected' : '' }}
                                                                    value="EXTEND_MEMBERSHIP_PERIOD">Perpanjang Masa Membership</option>
                                                                <option {{ @$membership->referral_owner_bonus_type == "EXTEND_SESSION" ? 'selected' : '' }}
                                                                    value="EXTEND_SESSION">Tambahkan Sesi</option>
                                                                <option {{ @$membership->referral_owner_bonus_type == "MERCHANDISE" ? 'selected' : '' }}
                                                                    value="MERCHANDISE">Merchandise</option>
                                                            </select>
                                                        </div>
                                                        <div id="referral_owner_bonus_form"></div>
                                                    </div>
                                                    <div class="card col-md-6 p-6 bg-secondary">
                                                        <h5 class="card-title mb-3">Bonus Pengguna Kode Referral</h5>
                                                        <div class="fv-row mb-6">
                                                            <!--begin::Label-->
                                                            <label class="fs-6 fw-bold form-label mt-3" for="referral_user_bonus_type">
                                                                <span class="required">Tipe Bonus</span>
                                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                                    title="Pilih Tipe Bonus"></i>
                                                            </label>
                                                            <!--end::Label-->
                                                            <select name="referral_user_bonus_type" id="referral_user_bonus_type" class="form-control" onchange="referralUserBonusType(this.value)" >
                                                                <option value="">--Pilih Tipe Bonus--</option>
                                                                <option {{ @$membership->referral_user_bonus_type == "EXTEND_MEMBERSHIP_PERIOD" ? 'selected' : '' }}
                                                                    value="EXTEND_MEMBERSHIP_PERIOD">Perpanjang Masa Membership</option>
                                                                <option {{ @$membership->referral_user_bonus_type == "EXTEND_SESSION" ? 'selected' : '' }}
                                                                    value="EXTEND_SESSION">Tambahkan Sesi</option>
                                                                <option {{ @$membership->referral_user_bonus_type == "MERCHANDISE" ? 'selected' : '' }}
                                                                    value="MERCHANDISE">Merchandise</option>
                                                            </select>
                                                        </div>
                                                        <div id="referral_user_bonus_form"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a
                                        href="{{ route('gym-place.show', (request()->gym_place_id ?? @$membership->gym_place_id) . '?tab=membership') }}">
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
    
    function translateBenefitsEnglish(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        var selectedBenefits = $('#benefit').val();
        var translatedBenefits = [];
        var promises = selectedBenefits.map(function(benefit) {
            return axios.get("{{ route('translate') }}", {
                params: {
                    text: benefit,
                }
            }).then(function(response) {
                translatedBenefits.push(response.data);
            });
        });
        Promise.all(promises).then(function() {
            $('#en_benefit').empty().select2({
                tags: true,
                placeholder: 'Pilih atau Buat Baru',
                allowClear: true
            });
            translatedBenefits.forEach(function(benefit) {
                $('#en_benefit').append(new Option(benefit, benefit, false, true));
            });
            $('#en_benefit').trigger('change');
            setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
        });
    }
    
    function translateBenefitsChinese(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        var selectedBenefits = $('#benefit').val();
        var translatedChineseBenefits = [];
        var promiseChinese = selectedBenefits.map(function(benefit) {
            return axios.get("{{ route('translate.chinese') }}", {
                params: {
                    text: benefit,
                }
            }).then(function(response) {
                translatedChineseBenefits.push(response.data);
            })
        });
        Promise.all(promiseChinese).then(function() {
            $('#cn_benefit').empty().select2({
                tags: true,
                placeholder: 'Pilih atau Buat Baru',
                allowClear: true
            });
            translatedChineseBenefits.forEach(function(benefit) {
                $('#cn_benefit').append(new Option(benefit, benefit, false, true));
            });
            $('#cn_benefit').trigger('change');
            setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
        });
    }
    
    function translateDescriptionEnglish(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        translator('#description', '#description_en');
        setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
    }
    
    function translateDescriptionChinese(btn) {
        var $btn = $(btn);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        translateChinesePost('#description', '#description_cn');
        setTimeout(function(){ $btn.prop('disabled', false).html('<i class="fas fa-language"></i>'); }, 1000);
    }

    $(document).ready(function() {
        let membership = "{{ @$membership ?? null }}";
        if ('{{ @$membership }}' && '{{ @$membership->referral_user_bonus_type }}' && '{{ @$membership->referral_user_bonus_type }}') {
            referralOwnerBonusType('{{ @$membership->referral_owner_bonus_type }}');
            referralUserBonusType('{{ @$membership->referral_user_bonus_type }}');
        }
    });

    function referralOwnerBonusType(value) {
        if (value == 'EXTEND_MEMBERSHIP_PERIOD' || value == 'EXTEND_SESSION') {
            $('#referral_owner_bonus_form').empty();

            let input = value === 'EXTEND_MEMBERSHIP_PERIOD' ? 
                `<label class="fs-6 fw-bold form-label mt-3" for="referral_user_bonus">
                    <span class="required">Lama Perpanjangan Membership (Bulan)</span>
                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                        title="Input Bulan"></i>
                </label>` :
                `<label class="fs-6 fw-bold form-label mt-3" for="referral_user_bonus">
                    <span class="required">Tambahkan Sesi</span>
                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                        title="Input Total Sesi"></i>
                </label>`;
            
            $('#referral_owner_bonus_form').append(`
                <!--begin::Input group-->
                <div class="fv-row mb-6">
                    <!--begin::Label-->
                    ${input} 
                    <!--end::Label-->
                    <input type="number" min="1" name="referral_owner_bonus" id="referral_owner_bonus"
                        value="{{ old('referral_owner_bonus', @$membership->referral_owner_bonus) }}" class="form-control">
                </div>
                <!--end::Input group-->
            `);
        } else if (value == 'MERCHANDISE') {
            $('#referral_owner_bonus_form').empty();

            let array = {!! json_encode($shopProducts) !!};

            $('#referral_owner_bonus_form').append(`
                <div class="fv-row mb-6">
                    <!--begin::Label-->
                    <label class="fs-6 fw-bold form-label mt-3" for="referral_owner_bonus">
                        <span class="required">Produk</span>
                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                            title="Pilih Produk"></i>
                    </label>
                    <!--end::Label-->
                    <select name="referral_owner_bonus" id="referral_owner_bonus" class="form-control" >
                        <option value="">--Pilih Produk--</option>
                        ${array.map(product => `
                            <option value="${product.id}"  ${product.id == "{{ old('referral_owner_bonus', @$membership->referral_owner_bonus) }}" ? 'selected' : ''}>
                                ${product.name}
                            </option>`).join('')}
                    </select>
                </div>
            `);
        }
    }

    function referralUserBonusType(value) {
        if (value == 'EXTEND_MEMBERSHIP_PERIOD' || value == 'EXTEND_SESSION') {
            $('#referral_user_bonus_form').empty();

            let input = value === 'EXTEND_MEMBERSHIP_PERIOD' ? 
                `<label class="fs-6 fw-bold form-label mt-3" for="referral_user_bonus">
                    <span class="required">Lama Perpanjangan Membership (Bulan)</span>
                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                        title="Input Bulan"></i>
                </label>` :
                `<label class="fs-6 fw-bold form-label mt-3" for="referral_user_bonus">
                    <span class="required">Tambahkan Sesi</span>
                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                        title="Input Total Sesi"></i>
                </label>`;
            
            $('#referral_user_bonus_form').append(`
                <!--begin::Input group-->
                <div class="fv-row mb-6">
                    <!--begin::Label-->
                    ${input}    
                    <!--end::Label-->
                    <input type="number" min="1" name="referral_user_bonus" id="referral_user_bonus"
                        value="{{ old('referral_user_bonus', @$membership->referral_user_bonus) }}" class="form-control">
                </div>
                <!--end::Input group-->
            `);
        } else if (value == 'MERCHANDISE') {
            $('#referral_user_bonus_form').empty();

            let array = {!! json_encode($shopProducts) !!};

            $('#referral_user_bonus_form').append(`
                <div class="fv-row mb-6">
                    <!--begin::Label-->
                    <label class="fs-6 fw-bold form-label mt-3" for="referral_user_bonus">
                        <span class="required">Produk</span>
                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                            title="Pilih Produk"></i>
                    </label>
                    <!--end::Label-->
                    <select name="referral_user_bonus" id="referral_user_bonus" class="form-control" required>
                        <option value="">--Pilih Produk--</option>
                        ${array.map(product => `
                            <option value="${product.id}"  ${product.id == "{{ old('referral_user_bonus', @$membership->referral_user_bonus) }}" ? 'selected' : ''}>
                                ${product.name}
                            </option>`).join('')}
                    </select>
                </div>
            `);
        }
    }

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

    $('#discount_price').on('keyup', function() {
            if (this.value.replace(/,(?=\d{3})/g, '') > 0) {
                $('#form-group-discount').removeClass('d-none');
            } else {
                $('#form-group-discount').addClass('d-none')
            }
            $('.discount_date').val('');
    })

    $('#type').on('change', function() {
            if (this.value == 'LIMIT_DAY_AND_SESSION') {
                $('#form-group-periode').removeClass('d-none');
                $('#form-group-total-session').removeClass('d-none');
            } else if (this.value == 'DAILY_VISIT') {
                $('#form-group-periode').addClass('d-none');
                $('#form-group-total-session').addClass('d-none');
                $('#period').val(1);
            } else if (this.value == 'LIMIT_DAY') {
                $('#form-group-periode').removeClass('d-none');
                $('#form-group-total-session').addClass('d-none');
            } else {
                $('#form-group-periode').addClass('d-none');
                $('#form-group-total-session').addClass('d-none');
            }
            $('#period').val('');
            $('#total_session').val('');
    })

    $('#benefit').select2({
        placeholder: 'Pilih atau Buat Baru',
        tags: true
    });
    $('#en_benefit').select2({
        placeholder: 'Pilih atau Buat Baru',
        tags: true
    });
    $('#cn_benefit').select2({
        placeholder: 'Pilih atau Buat Baru',
        tags: true
    });

    document.getElementById('is_referral_bonus_active').addEventListener('change', function () {
        const referralBonus = document.getElementById('referralBonus');
        if (this.checked) {
            referralBonus.style.display = 'block'; // Show card if checkbox is checked (Ya)
        } else {
            referralBonus.style.display = 'none'; // Hide card if checkbox is unchecked (Tidak)
        }
    });
</script>
@endpush