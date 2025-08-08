@extends('layouts.master', ['main' => 'Paket Fisioterapi', 'title' =>
request()->routeIs('physiotherapy-packet-session-packet-session.create') ? 'Paket Fisioterapi' : 'Paket Fisioterapi'])
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
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">
                                    {{ request()->routeIs('physiotherapy-packet-session-packet-session.create')
                                    ? 'Paket Fisioterapi'
                                    : 'Paket Fisioterapi' }}
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
                                action="{{ request()->routeIs('physiotherapy-packet-session.create') ? route('physiotherapy-packet-session.store') : route('physiotherapy-packet-session.update', @$physiotherapyPacketSession->id) }}"
                                method="POST" enctype="multipart/form-data">
                                @csrf
                                <x-form.put-method />
                                <input type="text" hidden name="id" value="{{ @$physiotherapyPacketSession->id }}">
                                <!--end::Input group-->
                                <div class="form-group">
                                    <!--begin::Label-->
                                    <x-form.image-upload label="Thumbnail" name="thumbnail"
                                        :value="@$physiotherapyPacketSession->thumbnail" />
                                </div>
                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name">
                                        <span class="required">Nama Paket</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Paket"></i>
                                    </label>
                                    <!--end::Label-->
                                    <input type="text" name="name" id="name"
                                        value="{{ old('name', @$physiotherapyPacketSession->name) }}"
                                        class="form-control" required>
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
                                                value="{{ old('name_en', @$physiotherapyPacketSession->name_en) }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameEnglish()"
                                                class="btn btn-translate">Translate English</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="name_cn">
                                        <span class="required">Nama Paket (chinese)</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Input Nama Paket"></i>
                                    </label>
                                    <div class="row">
                                        <div class="col-10">
                                            <input type="text" name="name_cn" id="name_cn"
                                                value="{{ old('name_cn', @$physiotherapyPacketSession->name_cn) }}"
                                                class="form-control" required>
                                        </div>
                                        <div class="col-2">
                                            <button type="button" onclick="translateNameChinese()"
                                                class="btn btn-translate">Translate Chinese</button>
                                        </div>
                                    </div>
                                    <!--end::Label-->
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                        <span class="required">Level</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Periode"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="personal_trainer_level_id" id="personal_trainer_level_id"
                                        class="form-control" required>
                                        <option value="">--Pilh level--</option>
                                        @foreach ($personalTrainerLevels as $level)
                                        <option {{ $level->id ==
                                            @$physiotherapyPacketSession->personal_trainer_level_id ? 'selected' : ''
                                            }}
                                            value="{{ $level->id }}">{{ $level->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!--begin::Input group-->
                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="type">
                                        <span class="required">Tipe</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Tipe Periode"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="type" id="type" class="form-control" required>
                                        @foreach ($types as $key => $type)
                                        <option {{ $key==@$physiotherapyPacketSession->type ? 'selected' : '' }}
                                            value="{{ $key }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Input group-->

                                <div class="row">
                                    <div class="col-sm-6" id="form-group-periode">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="training_period">
                                                <span class="required">Lama Berlangganan (Hari)</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Hari"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="number" min="1" name="training_period" id="training_period"
                                                value="{{ old('training_period', @$physiotherapyPacketSession->training_period) }}"
                                                class="form-control">
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    {{-- <div class="col-sm-6 {{ @$physiotherapyPacketSession->type == 'LIMIT_DAY_AND_SESSION' ? '' : 'd-none' }}" --}}
                                    <div class="col-sm-6"
                                        id="form-group-total-session">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="total_session">
                                                <span class="required">Total Sesi / Pertemuan</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Total Pertemuan"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="number" min="1" name="total_session" id="total_session"
                                                value="{{ old('total_session', @$physiotherapyPacketSession->total_session) }}"
                                                class="form-control">
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="price">
                                                <span class="required">Harga</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Harga Paket"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="text" name="price"
                                                value="{{ old('price', @$physiotherapyPacketSession->price) }}"
                                                class="form-control input-money" min="0">
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                    <div class="col-sm-6">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="non_member_price">
                                                <span class="">Harga Non Member</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Harga Non Member"></i>
                                            </label>
                                            <!--end::Label-->
                                            <input type="text" name="non_member_price" id="non_member_price"
                                                value="{{ old('non_member_price', @$physiotherapyPacketSession->non_member_price) }}"
                                                class="form-control input-money">
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <!--begin::Input group-->
                                        <div class="fv-row mb-6">
                                            <!--begin::Label-->
                                            <label class="fs-6 fw-bold form-label mt-3" for="description">
                                                <span class="required">Deskripsi</span>
                                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                    title="Input Deskripsi Paket"></i>
                                            </label>
                                            <!--end::Label-->
                                            <textarea name="description" id="description"
                                                class="form-control">{{ old('description', @$physiotherapyPacketSession->description) }}</textarea>
                                        </div>
                                        <!--end::Input group-->
                                    </div>
                                </div>

                                <div class="fv-row mb-6">
                                    <!--begin::Label-->
                                    <label class="fs-6 fw-bold form-label mt-3" for="is_active">
                                        <span class="required">Status Publish</span>
                                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                            title="Pilih Status Publish"></i>
                                    </label>
                                    <!--end::Label-->
                                    <select name="is_published" id="is_published" class="form-control" required>
                                        <option value="">--Pilih Tipe Publish--</option>
                                        <option {{ @$physiotherapyPacketSession->is_published == 1 ? 'selected' : ''
                                            }}
                                            value="1">Paket Fisioterapi di Tampilkan Untuk Publik/ di Aplikasi</option>
                                        <option {{ @$physiotherapyPacketSession->is_published == 0 ? 'selected' : ''
                                            }}
                                            value="0">Paket Fisioterapi di Sembunyikan</option>
                                    </select>
                                </div>

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
                                        <option {{ @$physiotherapyPacketSession->is_active == 1 ? 'selected' : '' }}
                                            value="1">AKTIF</option>
                                        <option {{ @$physiotherapyPacketSession->is_active == 0 ? 'selected' : '' }}
                                            value="0">NON AKTIF
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
                                    <a href="{{ route(
                                                'gym-place.show',
                                                (request()->gym_place_id ?? @$physiotherapyPacketSession->gym_place_id) . '?tab=personal_trainer_packet_session',
                                            ) }}">
                                        <button type="button" class="btn btn-secondary me-3">Batal</button>
                                    </a>
                                    <!--end::Button-->
                                    <!--begin::Button-->
                                    <button type="submit" data-kt-contacts-type="submit" class="btn btn-primary">
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
    // $('#name').on('change', () => translate('#name', '#name_en'));    
        function translateNameEnglish() {
            translate('#name', '#name_en');
        }
        
        function translateNameChinese() {
            translateChinese('#name', '#name_cn');
        }

        $(".input-money").on('keyup', function() {
            var n = parseInt($(this).val().replace(/\D/g, ''), 10) || 0;
            if (!isNaN(n)) {
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

        $('#type').on('change', function() {
            if (this.value == 'LIMIT_DAY_AND_SESSION') {
                $('#form-group-periode').removeClass('d-none');
                $('#form-group-total-session').removeClass('d-none');
            } else {
                $('#form-group-periode').removeClass('d-none');
                $('#form-group-total-session').addClass('d-none');
            }
            $('#training_period').val('');
            $('#total_session').val('');
        });

    
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
{{-- add ckeditor on #description --}}
<script src="https://cdn.ckeditor.com/ckeditor5/29.0.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#description'))
        .catch(error => {
            console.error(error);
        });
</script>
@endpush