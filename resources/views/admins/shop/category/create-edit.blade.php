@extends('layouts.master', ['main' => 'Data Kategori Shop', 'title' => request()->routeIs('shop-category.create') ?
'Tambah
Kategori' : 'Edit Kategori'])
@section('content')
<!--begin::Container-->
<div id="kt_content_container" class="app-container container-xxl pt-6">
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
                        <span class="card-label fw-bold fs-3">{{ request()->routeIs('shop-category.create') ? 'Tambah
                            Kategori'
                            :
                            'Edit Kategori' }}</span>
                    </h3>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-5">
                    <!--begin::Form-->
                    <x-alert.alert-validation />
                    <form id="broadcast"
                        action="{{ request()->routeIs('shop-category.create') ? route('shop-category.store') : route('shop-category.update', @$shopCategory->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.put-method />
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name">
                                <span class="required text-dark">Nama Kategori</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Kategori"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="text" class="form-control" id="name" placeholder="Contoh: Alat Olahraga"
                                name="name" value="{{ @$shopCategory->name ?? old('name') }}" required />
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name_en">
                                <span class="required text-dark">Nama Kategori (English)</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Kategori (English)"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row">
                                <div class="col-10">
                                    <input type="text" class="form-control" id="name_en" placeholder="Contoh: Sport Equipment"
                                        name="name_en" value="{{ @$shopCategory->name_en ?? old('name_en') }}" />
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="translateNameEnglish()" class="btn btn-translate">Translate English</button>
                                </div>
                            </div>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="name_cn">
                                <span class="required text-dark">Nama Kategori (Chinese)</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Nama Kategori (Chinese)"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <div class="row">
                                <div class="col-10">
                                    <input type="text" class="form-control" id="name_cn" placeholder="Contoh: Sport Equipment"
                                        name="name_cn" value="{{ @$shopCategory->name_cn ?? old('name_cn') }}" />
                                </div>
                                <div class="col-2">
                                    <button type="button" onclick="translateNameChinese()" class="btn btn-translate">Translate Chinese</button>
                                </div>
                            </div>
                            <!--end::Input-->
                        </div>

                        @if (auth()->user()->gym_place_id == null)
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="gym_place_id">
                                <span class="required text-dark">Tempat Gym</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih Tempat Gym"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <x-form.gym-place :value="@$shopCategory->gym_place_id ?? null" class="form-control" />
                        </div>
                        @endif
                        <!--begin::Separator-->

                        <!--end::Separator-->
                        <!--begin::Action buttons-->
                        <div class="d-flex justify-content-end">
                            <!--begin::Button-->
                            <a href="{{ route('shop-category.index') }}">
                                <button type="button" data-kt-contacts-type="cancel"
                                    class="btn btn-secondary me-3">Batal</button>
                            </a>
                            <!--end::Button-->
                            <!--begin::Button-->
                            <button type="submit" data-kt-contacts-type="submit" class="btn btn-primary btn-sm">
                                <span class="indicator-label">Simpan</span>
                                <span class="indicator-progress">Mohon Tunggu...
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
    // $('#name').on('change', () => translate('#name', '#name_en'));
    function translateNameEnglish() {
        translate('#name', '#name_en');
    }
    
    function translateNameChinese() {
        translateChinese('#name', '#name_cn');
    }
</script>
@endpush