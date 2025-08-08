@extends('layouts.master', ['main' => 'Kategori Artikel', 'title' => request()->routeIs('article-category.create') ? 'Tambah Kategori Artikel' : 'Edit Kategori Artikel'])
@section('content')
<!--begin::Content-->
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
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
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3">{{
                                request()->routeIs('article-category.create') ? 'Tambah Kategori Artikel' : 'Edit
                                Kategori Artikel' }}
                            </span>
                        </h3>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-5">
                        <!--begin::Form-->
                        <x-alert.alert-validation />
                        <form class="form" action="{{ request()->routeIs('article-category.create') ?
                             route('article-category.store') : route('article-category.update',
                              $articleCategory->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <x-form.put-method />
                            <!--begin::Input group-->
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="name">
                                    <span class="required text-dark">Kategori Artikel</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input nama kategori"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" class="form-control" name="name" id="name" value="{{ @$articleCategory->name ?? old('name') }}" required />
                                <!--end::Input-->
                            </div>

                            <div class="fv-row mb-6 en-feature">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="name">
                                    <span class="required text-dark">Kategori Artikel (English)</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="Input nama kategori dalam Bahasa Inggris"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" class="form-control" name="name_en" id="name_en" value="{{ @$articleCategory->name_en ?? old('name_en') }}" required />
                                <!--end::Input-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Separator-->
                            <div class="separator mb-6"></div>
                            <!--end::Separator-->
                            <!--begin::Action buttons-->
                            <div class="d-flex justify-content-end">
                                <!--begin::Button-->
                                <a href="{{ route('article-category.index') }}">
                                    <button type="button" class="btn btn-secondary btn-sm me-3">Batal</button>
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
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $('#name').on('change', () => translate('#name', '#name_en'));
</script>
@endpush