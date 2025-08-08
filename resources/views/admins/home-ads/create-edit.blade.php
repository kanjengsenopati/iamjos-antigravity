@extends('layouts.master', ['main' => 'Data Iklan', 'title' => request()->routeIs('home-ads.create') ? 'Tambah Iklan' : 'Edit Iklan'])
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
                            <span
                                class="card-label fw-bold fs-3">{{ request()->routeIs('home-ads.create') ? 'Tambah Iklan' : 'Edit Iklan' }}</span>
                        </h3>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-5">
                        <!--begin::Form-->
                        <x-alert.alert-validation />
                        <form id="home-ads"
                            action="{{ request()->routeIs('home-ads.create') ? route('home-ads.store') : route('home-ads.update', @$homeAds->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <x-form.put-method />
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label for="media" class="fs-6 fw-bold form-label mt-3">
                                    <span class="text-dark">Media</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Upload Media"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                {{-- tampilkan preview saat edit --}}
                                @if (request()->routeIs('home-ads.edit') && @$homeAds->media_url)
                                    <div class="mb-3">
                                        @if (@$homeAds->media_type === 'video')
                                            <video width="320" height="240" controls>
                                                <source src="{{ asset(@$homeAds->media_url) }}" type="video/mp4">
                                                Your browser does not support the video tag.
                                            </video>
                                        @else
                                            <img src="{{ asset(@$homeAds->media_url) }}" class="img-fluid"
                                                alt="Media Iklan">
                                        @endif
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="media" name="media"
                                    value="{{ old('media', @$homeAds->media) }}" placeholder="Upload Media" />

                                <!--end::Input-->
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label for="link" class="fs-6 fw-bold form-label mt-3">
                                    <span class="text-dark">Link</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Link"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="url" class="form-control" id="link" name="link"
                                    value="{{ old('link', @$homeAds->link) }}" placeholder="Masukkan Link" />
                                <!--end::Input-->
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label for="order" class="fs-6 fw-bold form-label mt-3">
                                    <span class="text-dark">Urutan</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Masukkan Urutan"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="number" class="form-control" id="order" name="order"
                                    value="{{ old('order', @$homeAds->order) }}" placeholder="Masukkan Urutan" />
                                <!--end::Input-->
                            </div>
                            @if (request()->routeIs('home-ads.edit'))
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label for="is_active" class="fs-6 fw-bold form-label mt-3">
                                        <span class="text-dark">Status</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Aktifkan atau Nonaktifkan Iklan"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <select class="form-select" id="is_active" name="is_active">
                                        <option value="1"
                                            {{ old('is_active', @$homeAds->is_active) == 1 ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0"
                                            {{ old('is_active', @$homeAds->is_active) == 0 ? 'selected' : '' }}>Nonaktif
                                        </option>
                                    </select>
                                    <!--end::Input-->
                                </div>
                            @endif
                            <!--end::Separator-->
                            <!--begin::Action buttons-->
                            <div class="d-flex justify-content-end">
                                <!--begin::Button-->
                                <a href="{{ route('home-ads.index') }}">
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
