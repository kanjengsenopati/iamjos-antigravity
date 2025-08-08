@extends('layouts.master', ['title' => 'Promo'])
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
        <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
            <!--begin::Page title-->
            <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                class="page-title d-flex align-items-center flex-wrap me-3 mb-5 mb-lg-0">
                <!--begin::Title-->
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Promo</h1>
                <!--end::Title-->
                <!--begin::Separator-->
                <span class="h-20px border-gray-300 border-start mx-4"></span>
                <!--end::Separator-->
                <!--begin::Breadcrumb-->
                <ul class="breadcrumb breadcrumb-separatorless fw-bold fs-7 my-1">
                    <!--begin::Item-->

                    <!--end::Item-->
                    <!--begin::Item-->
                    <a class="breadcrumb-item" href="{{ route('promo.index') }}">
                        <li class="text-muted">
                            Promo
                        </li>
                    </a>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-300 w-5px h-2px"></span>
                    </li>
                    <!--end::Item-->
                    <!--begin::Item-->
                    <li class="breadcrumb-item text-dark">
                        {{ request()->routeIs('promo.create') ? 'Tambah Promo' : 'Edit Promo' }}
                    </li>
                    <!--end::Item-->
                </ul>
                <!--end::Breadcrumb-->
            </div>
            <!--end::Page title-->
            <!--begin::Actions-->

            <!--end::Actions-->
        </div>
        <!--end::Container-->
    </div>
    <!--end::Toolbar-->
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="container-fluid">
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
                                    request()->routeIs('promo.create') ? 'Tambah Promo' : 'Edit Promo' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form"
                                action="{{ request()->routeIs('promo.create') ? route('promo.store') : route('promo.update', $promo->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" hidden name="id" value="{{@$promo->id}}">
                                <div class="form-group">
                                    <!--begin::Label-->
                                    <x-form.image-upload label="Thumbnail" name="image" :value="@$promo->image ?? null"
                                        nullable='{{request()->routeIs("promo.create") ?  1 : 0}}' />
                                </div>
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Tipe Promo</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="type" class="form-control form-control-solid" required>
                                        <option value="">--Pilih Tipe--</option>
                                        @foreach ($types as $key => $type )
                                        <option {{$key==@$promo?->type ? 'selected' : ''}} value="{{$key}}">{{$type}}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Promo</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Promo"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        value="{{ old('name', @$promo->name) }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Promo (EN)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Promo dalam bahasa inggris"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="name_en"
                                        id="name_en" value="{{ old('name_en', @$promo->name_en) }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Sub Nama Promo</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Sub Nama Promo"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="subname"
                                        id="subname" value="{{ old('subname', @$promo->subname) }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Sub Nama Promo (EN)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Sub Nama Promo dalam bahasa inggris"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="subname_en"
                                        id="subname_en" value="{{ old('subname_en', @$promo->subname_en) }}" required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="code">
                                        <span class="required">Kode Promo</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Kode Promo"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="code"
                                        value="{{ old('code', @$promo->code) }}" required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="quota">
                                        <span class="required">Kuota</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Kode Promo"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="number" min="0" class="form-control form-control-solid" name="quota"
                                        value="{{ old('quota', @$promo->quota) }}" required />
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="discount_type">
                                        <span class="required">Tipe Diskon</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Diskon"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="discount_type" id="discount_type"
                                        class="form-control form-control-solid" required>
                                        <option value="">--Pilih Tipe Diskon--</option>
                                        <option {{@$promo->discount_type == 'PERCENT' ? 'selected' : ''}}
                                            value="PERCENT">Persen</option>
                                        <option {{@$promo->discount_type == 'FIXED' ? 'selected' : ''}}
                                            value="FIXED">Nominal Tetap</option>
                                    </select>
                                </div>
                                <!--end::Input group-->
                                <div class="row">
                                    <div class="col-sm-12  {{@$promo->discount_type == 'FIXED' ? 'd-none' : ''}}"
                                        id="input_discount_percent">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="discount_percent">
                                                <span class="required">Diskon (%)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Diskon"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="number" id="discount_percent" min="1" step="0.1"
                                                class="form-control form-control-solid" name="discount_percent"
                                                value="{{ old('discount_percent', @$promo->discount_percent) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-12  {{@$promo->discount_type == 'FIXED' ? '' : 'd-none'}}"
                                        id="input_discount_fixed">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="discount_fixed">
                                                <span class="required">Diskon (Rp)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Diskon"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" id="discount_fixed"
                                                class="form-control form-control-solid input-money"
                                                name="discount_fixed"
                                                value="{{ old('discount_fixed', @$promo->discount_fixed) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="min_purchase">
                                                <span class="required">Min.Pembelian</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Minimal Pembelian"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" id="min_purchase"
                                                class="form-control form-control-solid input-money" name="min_purchase"
                                                value="{{ old('min_purchase', @$promo->min_purchase) }}" required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="max_discount">
                                                <span class="required">Max.Diskon</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Max Diskon"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="text" id="max_discount"
                                                class="form-control form-control-solid input-money" name="max_discount"
                                                value="{{ old('max_discount', @$promo->max_discount) }}" required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="start_date">
                                                <span class="required">Mulai Berlaku</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Tanggal Mulai Berlaku"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="date" id="start_date" class="form-control form-control-solid"
                                                name="start_date" value="{{ old('start_date', @$promo->start_date) }}"
                                                required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="expiry_date">
                                                <span class="required">Tanggal Expired</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Tanggal Expired"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="date" id="expiry_date" class="form-control form-control-solid"
                                                name="expiry_date"
                                                value="{{ old('expiry_date', @$promo->expiry_date) }}" required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="expiry_time">
                                                <span class="required">Jam Expired</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Tanggal Expired"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="time" id="expiry_time" class="form-control form-control-solid"
                                                name="expiry_time"
                                                value="{{ old('expiry_time', @$promo->expiry_time) }}" required />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="term_and_condition">
                                        <span class="required">Syarat & Ketentuan</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Syarat & Ketentuan"></i>
                                    </label>
                                    <textarea class="form-control form-control-solid" id="term_and_condition"
                                        name="term_and_condition">{{ old('term_and_condition',@$promo->term_and_condition) }}</textarea>
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="term_and_condition_en">
                                        <span class="required">Syarat & Ketentuan (EN)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Syarat & Ketentuan"></i>
                                    </label>
                                    <textarea class="form-control form-control-solid" id="term_and_condition_en"
                                        name="term_and_condition_en">{{ @$promo->term_and_condition_en ??
                                         old('term_and_condition_en') }}</textarea>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                        <span class="required">Status</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih status"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="is_active" id="is_active" class="form-control form-control-solid"
                                        required>
                                        <option value="">--Pilih Tipe Diskon--</option>
                                        <option {{@$promo->is_active == 1 ? 'selected' : ''}} value="1">AKTIF</option>
                                        <option {{@$promo->is_active == 0 ? 'selected' : ''}} value="0">NON AKTIF
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
    $('#name').on('change', () => translate('#name', '#name_en'));
    $('#subname').on('change', () => translate('#subname', '#subname_en'));
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
    
    tinymce.init({
    setup: function(ed) {
    ed.on('change', delay(function(e) {
    let content = ed.getContent();
    $.ajax({
    type: 'POST',
    url: "{{ route('translate.post') }}",
    data: {
    translate: content
    },
    cache: false,
    
    success: function(msg) {
    tinymce.get('term_and_condition_en').setContent(msg);
    },
    error: function(data) {
    console.log('error:', data)
    },
    })
    }, 2000));
    },
    selector: '#term_and_condition, #term_and_condition_en',
    height: 300,
    menubar: false,
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
        } else {
            $('#input_discount_percent').removeClass('d-none');
            $('#input_discount_fixed').addClass('d-none');
        }
    })

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
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush