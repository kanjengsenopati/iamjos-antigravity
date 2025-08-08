@extends('layouts.master', ['title' => 'Kategori Review', 'main' => 'Kategori Review'])
@section('content')
<!--begin::Content-->
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
        <!--begin::Container-->
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
                                    request()->routeIs('category-review.create') ? 'Tambah Kategori Review' : 'Edit
                                    Kategori Review' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-5">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form" action="{{ request()->routeIs('category-review.create') ?
                                 route('category-review.store') : route('category-review.update',
                                  $categoryReview->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Kategori Review</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Kategori Review"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control form-control-solid" name="name" id="name"
                                        value="{{ @$categoryReview->name ?? old('name') }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Kategori Review (English)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Kategori Review dalam bahasa Inggris"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" class="form-control form-control-solid" name="name_en"
                                                id="name_en"
                                                value="{{ @$categoryReview->name_en ?? old('name_en') }}" />
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameEnglish()"
                                                class="btn btn-translate">Translate English</button>
                                        </div>
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Kategori Review (Chinese)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Masukkan Kategori Review dalam bahasa Inggris"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" class="form-control form-control-solid" name="name_cn"
                                                id="name_cn"
                                                value="{{ @$categoryReview->name_cn ?? old('name_cn') }}" />
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameChinese()"
                                                class="btn btn-translate">Translate Chinese</button>
                                        </div>
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Tipe</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Kategori Review"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select class="form-select form-select-solid" name="type" id="type">
                                        <option value="">Pilih Tipe</option>
                                        @foreach ($categories as $category => $value)
                                        <option value="{{ $category }}" {{ @$categoryReview->type == $category ?
                                            'selected' : '' }}>
                                            {{ $value }}</option>
                                        @endforeach
                                    </select>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('category-review.index') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
                                        <span class="indicator-label">Simpan</span>
                                        <span class="indicator-progress">Please wait...
                                            <span
                                                class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
    // $('#name').on('change', () => translate('#name', '#name_en'));
    function translateNameEnglish() {
        translate('#name', '#name_en');
    }
    
    function translateNameChinese() {
        translateChinese('#name', '#name_cn');
    }
</script>
@endpush