@extends('layouts.master', ['main' => 'Kelas', 'title' => request()->routeIs('gym-class.create') ? 'Tambah Kelas' :
'Edit Kelas'])
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ request()->routeIs('gym-class.create') ? 'Tambah Kelas' : 'Edit Kelas' }}
                                </h1>
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body">
                            <!--begin::Form-->
                            <x-alert.alert-validation />
                            <form class="form"
                                action="{{ request()->routeIs('gym-class.create') ? route('gym-class.store') : route('gym-class.update', $gymClass->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" hidden name="id" value="{{ @$gymClass->id }}">
                                <input type="text" name="gym_place_id"
                                    value="{{ request()->gym_place_id ?? @$gymClass->gym_place_id }}" required hidden>
                                <div class="form-group col-3">
                                    <!--begin::Label-->
                                    <x-form.image-upload label="Thumbnail" name="thumbnail"
                                        :value="@$gymClass->thumbnail"
                                        nullable="request()->routeIs('gym-class.create') ? 1 : 0 " />
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Kelas</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Paket"></i>
                                    </label>
                                    <!--end::Label-->
                                    <input type="text" name="name" id="name"
                                        value="{{ old('name', @$gymClass->name) }}" class="form-control"
                                        required>
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name_en">
                                        <span class="required">Nama Paket (english)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Paket"></i>
                                    </label>
                                    <!--end::Label-->
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" name="name_en" id="name_en"
                                                value="{{ old('name_en', @$gymClass->name_en) }}" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameEnglish()" class="btn btn-translate">Translate English</button>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name_cn">
                                        <span class="required">Nama Paket (chinese)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Paket"></i>
                                    </label>
                                    <!--end::Label-->
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" name="name_cn" id="name_cn"
                                                value="{{ old('name_cn', @$gymClass->name_cn) }}" class="form-control"
                                                required>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameChinese()" class="btn btn-translate">Translate Chinese</button>
                                        </div>
                                    </div>
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                        <span class="required">Tipe</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Kelas"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="type" id="type" class="form-control" required>
                                        @foreach ($types as $key => $type)
                                        <option {{ $key==@$gymClass->type ? 'selected' : '' }}
                                            value="{{ $key }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Input group-->

                                <div class="row">
                                    <div class="col-sm-6 {{ @$gymClass->type == 'PAID' ? '' : 'd-none' }}"
                                        id="form-group-price">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="price">
                                                <span class="required">Harga</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Harga Paket"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="text" name="price" id="price"
                                                value="{{ old('price', @$gymClass->price ?? 0) }}"
                                                class="form-control input-money" required>
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6 {{ @$gymClass->type == 'PAID' ? '' : 'd-none' }}"
                                        id="form-group-strikeout-price">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="strikeout_price">
                                                <span class="">Harga Coret</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Harga Coret"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="text" name="strikeout_price" id="strikeout_price"
                                                value="{{ old('strikeout_price', @$gymClass->strikeout_price) }}"
                                                class="form-control input-money">
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="gym_class_category_id">
                                        <span class="required">Kategori</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Kategori"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="gym_class_category_id" id="gym_class_category_id" class="form-control"
                                        required>
                                        @foreach ($gymClassCategories as $gymClassCategory)
                                        <option {{ @$gymClass?->gym_class_category_id == $gymClassCategory->id ?
                                            'selected' : '' }}
                                            value="{{ $gymClassCategory->id }}">{{ $gymClassCategory->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="level">
                                        <span class="required">Level</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Level"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="level" id="level" class="form-control" required>
                                        @foreach ($gymClassLevels as $gymClassLevel)
                                        <option {{ @$gymClassCategory?->level == $gymClassLevel ? 'selected' : '' }}
                                            value="{{ $gymClassLevel }}">{{ $gymClassLevel }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Input group-->

                                <div class="row">
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="start_date">
                                                <span class="">Tanggal Mulai</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Tanggal Mulai"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="date" id="start_date" class="form-control" name="start_date"
                                                value="{{ old('start_date', @$gymClass->start_date) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="end_date">
                                                <span class="">Tanggal Selesai</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Tanggal Selesai"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="date" id="end_date" class="form-control" name="end_date"
                                                value="{{ old('end_date', @$gymClass->end_date) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-4">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="day">
                                                <span class="required">Hari</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Hari"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <select name="day" class="form-select" required>
                                                @foreach ($days as $day)
                                                <option {{ $day==@$gymClass?->day ? 'selected' : '' }}
                                                    value="{{ $day }}">{{ $day }}</option>
                                                @endforeach
                                            </select>
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-4">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="start_time">
                                                <span class="required">Jam Mulai</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Jam Mulai"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="time" id="start_time" class="form-control discount_date"
                                                name="start_time"
                                                value="{{ old('start_time', @$gymClass->start_time) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-4">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="end_time">
                                                <span class="required">Jam Selesai</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Tanggal Selesai"></i>
                                            </label>
                                            <!--end::Label-->
                                            <!--begin::Input-->
                                            <input type="time" id="end_time" class="form-control" name="end_time"
                                                value="{{ old('end_time', @$gymClass->end_time) }}" />
                                            <!--end::Input-->
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="quota">
                                        <span class="required">Kuota Peserta</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Kuota Peserta"></i>
                                    </label>
                                    <!--end::Label-->
                                    <input type="number" min="0" name="quota" id="quota"
                                        value="{{ old('quota', @$gymClass->quota) }}" class="form-control" required>
                                </div>
                                <!--end::Input group-->

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3">
                                        <span class="required">Tipe Trainer</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Trainer"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select class="form-control" id="trainer_type" name="trainer_type" required>
                                        <option value="">--Pilih Tipe Trainer--</option>
                                        <option {{ @$gymClass->trainer_type == 'INTERNAL' ? 'selected' : '' }}
                                            value="INTERNAL">Internal</option>
                                        <option {{ @$gymClass->trainer_type == 'EXTERNAL' ? 'selected' : '' }}
                                            value="EXTERNAL">External
                                        </option>
                                    </select>
                                </div>
                                <!--end::Input group-->

                                <div class="row">
                                    <div class="col-sm-12 {{ @$gymClass?->trainer_type == 'INTERNAL' || !@$gymClass?->trainer_type ? '' : 'd-none' }}"
                                        id="internal_trainer">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="personal_trainer_id">
                                                <span class="required">Personal Trainer</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Personal Trainer"></i>
                                            </label>
                                            <!--end::Label-->
                                            <select name="personal_trainer_id" id="personal_trainer_id" class="form-control">
                                                <option value="">--Pilih Personal Trainer--</option>
                                                @foreach($trainers as $trainer)
                                                    <option value="{{ $trainer->id }}" {{ (old('personal_trainer_id', @$gymClass?->personal_trainer_id) == $trainer->id) ? 'selected' : '' }}>
                                                        {{ $trainer->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-12 {{ @$gymClass?->trainer_type == 'EXTERNAL' ? '' : 'd-none' }}"
                                        id="external_trainer">
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3"
                                                for="personal_trainer_external_id">
                                                <span>Personal Trainer External</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Pilih Personal Trainer External"></i>
                                            </label>
                                            <!--end::Label-->
                                            <select name="personal_trainer_external_id"
                                                id="personal_trainer_external_id" data-control="select2"
                                                class="form-control">
                                                <option value="" selected>Pilih Personal Trainer External
                                                </option>
                                                @foreach ($trainerExternals as $external)
                                                <option value="{{ $external->id }}" @selected(@$gymClass->
                                                    personal_trainer_external_id == $external->id ? true : false)>
                                                    {{ $external->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3">
                                                <span class="required">Foto</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Upload foto atau gambar"></i>
                                            </label>
                                            <input type="file" class="form-control" name="external_trainer[image]"
                                                value="@$gymClass?->external_trainer?->avatar">
                                        </div>
                                        <img src="{{asset(@$gymClass?->external_trainer?->avatar)}}"
                                            class="img img-thumbnail w-25" alt="">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3">
                                                <span class="required">Nama Personal Trainer</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Nama"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="text" id="external_trainer_name input-external-trainer"
                                                value="{{old('external_trainer[name]', @$gymClass?->external_trainer?->name)}}"
                                                name="external_trainer[name]" class="form-control">
                                        </div>
                                        <!--end::Input group-->
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3">
                                                <span class="required">Bio</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Bio"></i>
                                            </label>
                                            <!--end::Label-->
                                            <textarea type="text" id="external_trainer_bio" name="external_trainer[bio]"
                                                class="form-control input-external-trainer">{{old('external_trainer[bio]', @$gymClass?->external_trainer?->bio)}}</textarea>
                                        </div>
                                        <!--end::Input group--> --}}
                                    </div>
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="description">
                                        <span class="required">Deskripsi</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Deskripsi"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <textarea rows="7" class="form-control" id="description" name="description"
                                        required>{{ old('description', @$gymClass->description) }}</textarea>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="description_en">
                                        <span class="required">Deskripsi (english)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Deskripsi"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row">
                                        <div class="col-10">
                                            <textarea rows="7" class="form-control" id="description_en" name="description_en"
                                                required>{{ old('description_en', @$gymClass->description_en) }}</textarea>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateDescriptionEnglish()" class="btn btn-translate">Translate English</button>
                                        </div>
                                    </div>
                                    <!--end::Input-->
                                </div>
                                <!--end::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="description_cn">
                                        <span class="required">Deskripsi (chinese)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Deskripsi"></i>
                                    </label>
                                    <!--end::Label-->
                                    <!--begin::Input-->
                                    <div class="row">
                                        <div class="col-10">
                                             <textarea rows="7" class="form-control" id="description_cn" name="description_cn"
                                                required>{{ old('description_cn', @$gymClass->description_cn) }}</textarea>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateDescriptionChinese()" class="btn btn-translate">Translate Chinese</button>
                                        </div>
                                    </div>
                                    <!--end::Input-->
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_special_membership">
                                        <span class="required">Khusus Membership</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih status"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="is_special_membership" id="is_special_membership" class="form-control"
                                        required>
                                        <option value="">--Pilih Tipe Diskon--</option>
                                        <option {{ @$gymClass->is_special_membership == 1 ? 'selected' : '' }}
                                            value="1">IYA</option>
                                        <option {{ @$gymClass->is_special_membership == 0 ? 'selected' : '' }}
                                            value="0">TIDAK
                                        </option>
                                    </select>
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
                                    <select name="is_active" id="is_active" class="form-control" required>
                                        <option value="">--Pilih Status--</option>
                                        <option {{ @$gymClass->is_active == 1 ? 'selected' : '' }} value="1">
                                            AKTIF</option>
                                        <option {{ @$gymClass->is_active == 0 ? 'selected' : '' }} value="0">NON
                                            AKTIF
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
                                    <a
                                        href="{{ route('gym-place.show', (request()->gym_place_id ?? @$gymClass->gym_place_id) . '?tab=gym_class') }}">
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
    
        function translateNameEnglish() {
            translate('#name', '#name_en');
        }
        
        function translateNameChinese() {
            translateChinese('#name', '#name_cn');
        }

        function translateDescriptionEnglish() {
            translator('#description', '#description_en');
        }
        
        function translateDescriptionChinese() {
            translateChinesePost('#description', '#description_cn');
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


        $('#trainer_type').on('change', function() {
            if (this.value == 'EXTERNAL') {
                $('#external_trainer').removeClass('d-none');
                $('#internal_trainer').addClass('d-none');
            } else {
                $('#external_trainer').addClass('d-none');
                $('#internal_trainer').removeClass('d-none');
            }
            $('.input-external-trainer').val('')
            $('#personal_trainer_id').val('').trigger('change')
        })


        $('#type').on('change', function() {
            if (this.value == 'PAID') {
                $('#form-group-price').removeClass('d-none');
                $('#form-group-strikeout-price').removeClass('d-none');
            } else {
                $('#form-group-price').addClass('d-none');
                $('#form-group-strikeout-price').addClass('d-none');
            }
            $('#price').val(0);
            $('#strikeout_price').val('');
        })
</script>
@endpush