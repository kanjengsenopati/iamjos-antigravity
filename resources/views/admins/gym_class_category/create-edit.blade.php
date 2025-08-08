@extends('layouts.master', ['main' => 'Kategori Kelas', 'title' => request()->routeIs('gym-class-category.create') ? 'Tambah Kategori Kelas' : 'Edit Kategori Kelas'])
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
                                    request()->routeIs('gym-class-category.create') ? 'Tambah Kategori Kelas' : 'Edit
                                    Kategori Kelas' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Form-->
                            <x-alert.alert-validation />

                            <form class="form" action="{{ request()->routeIs('gym-class-category.create') ?
                                 route('gym-class-category.store') : route('gym-class-category.update',
                                  @$gymClassCategory->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" name="gym_place_id" value="{{request()->gym_place_id ?? @$gymClassCategory->gym_place_id}}" required hidden>
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Kategori Kelas</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input nama Kategori"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <input type="text" class="form-control" name="name" id="name" value="{{ @$gymClassCategory->name ?? old('name') }}" required />
                                    <!--end::Input-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name_en">
                                        <span class="required">Kategori Kelas (English)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input nama Kategori Dalam Inggris"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" class="form-control" name="name_en" id="name_en" value="{{ @$gymClassCategory->name_en ?? old('name_en') }}" required />
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameEnglish()" class="btn btn-translate">Translate Chinese</button>
                                        </div>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name_cn">
                                        <span class="required">Kategori Kelas (Chinese)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input nama Kategori Dalam Inggris"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" class="form-control" name="name_cn" id="name_cn" value="{{ @$gymClassCategory->name_cn ?? old('name_cn') }}" required />
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameChinese()" class="btn btn-translate">Translate Chinese</button>
                                        </div>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Separator-->
                                <div class="separator mb-6"></div>
                                <!--end::Separator-->
                                <!--begin::Action buttons-->
                                <div class="d-flex justify-content-end">
                                    <!--begin::Button-->
                                    <a href="{{ route('gym-place.show',(request()->gym_place_id ?? @$gymClassCategory->gym_place_id).'?tab=gym_class') }}">
                                        <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-sm btn-primary">
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
    function translateNameEnglish() {
        translate('#name', '#name_en');
    }
    
    function translateNameChinese() {
        translateChinese('#name', '#name_cn');
    }
</script>
@endpush
