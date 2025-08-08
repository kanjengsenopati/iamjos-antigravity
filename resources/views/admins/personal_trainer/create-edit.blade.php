@extends('layouts.master', ['main' => 'Coach', 'title' => request()->routeIs('personal-trainer.create') ? 'Tambah Coach' : 'Edit Coach'])
@push('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush
@section('content')
    <!--begin::Content-->
    <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="container-xxl app-container">
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
                                        {{ request()->routeIs('personal-trainer.create') ? 'Tambah Coach' : 'Edit Coach' }}
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
                                    action="{{ request()->routeIs('personal-trainer.create') ? route('personal-trainer.store') : route('personal-trainer.update', $personalTrainer->id) }}"
                                    method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <x-form.put-method />
                                    <input type="text" name="id" hidden value="{{ @$personalTrainer->id }}">
                                    <input type="text" name="gym_place_id"
                                        value="{{ request()->gym_place_id ?? @$personalTrainer->gym_place_id }}" required
                                        hidden>
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        {{-- <label class="fs-6 fw-bold form-label mt-3" for="">
                                            <span class="required">Avatar</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Tambahkan foto atau gambar"></i>
                                        </label> --}}
                                        <x-form.image-upload label="Avatar" name="avatar" :value="@$personalTrainer->avatar" />
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="name">
                                            <span class="required">Nama</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Input Nama Coach"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="text" id="name" class="form-control" name="name"
                                            value="{{ old('name', @$personalTrainer->name) }}" required />
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="gender">
                                            <span class="required">Gender</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Input Gender Coach"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <select name="gender" id="gender" class="form-control" required>
                                            <option value="">--Pilih Gender--</option>
                                            <option value="male" {{ old('gender', @$personalTrainer->gender) == 'male' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="female" {{ old('gender', @$personalTrainer->gender) == 'female' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="email">
                                            <span class="required">Email</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Input Email Coach"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="email" id="email" class="form-control" name="email"
                                            value="{{ old('email', @$personalTrainer->email) }}" required />
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input group-->
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
                                                <option
                                                    {{ $level->id == @$personalTrainer->personal_trainer_level_id ? 'selected' : '' }}
                                                    value="{{ $level->id }}">{{ $level->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="start_experience_year">
                                            <span class="required">Tahun Awal Menjadi PT</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Input  Tahun Awal Menjadi Coach"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" min="1" id="start_experience_year"
                                            placeholder="ex: 2020" class="form-control" name="start_experience_year"
                                            value="{{ old('start_experience_year', @$personalTrainer->start_experience_year) }}"
                                            required />
                                        <!--end::Input-->
                                    </div>

                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="max_member">
                                            <span class="required">Maksimal Member</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Input Maksimal Member Yang Dapat Ditangani"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="number" min="1" id="max_member" placeholder="ex: 10"
                                            class="form-control" name="max_member"
                                            value="{{ old('max_member', @$personalTrainer->max_member) }}" required />
                                        <!--end::Input-->
                                    </div>
                                    <!--end::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="password">
                                            <span class="required">Password</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Password"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="password" placeholder="Kosongkan password jika tidak ingin mengganti"
                                            class="form-control" id="password" name="password"
                                            value="{{ old('password') }}" />
                                        <!--end::Input-->
                                    </div>
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label" for="password_confirmation">
                                            <span class="required">Konfirmasi Password</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Masukkan Konfirmasi Password"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <input type="password" class="form-control"
                                            placeholder="Kosongkan password jika tidak ingin mengganti"
                                            id="password_confirmation" name="password_confirmation"
                                            value="{{ old('password_confirmation') }}" />
                                        <!--end::Input-->
                                    </div>
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                            <span class="required">Benefit</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih atau Buat Benefit"></i>
                                        </label>
                                        <!--end::Label-->
                                        <select name="benefits[]" id="benefit" multiple class="form-control select2"
                                            required>
                                            @foreach ($benefits as $benefit)
                                                <option
                                                    {{ in_array($benefit->name, @$personalTrainer?->personal_trainer_benefits?->pluck('name')?->toArray() ?? [])
                                                        ? 'selected'
                                                        : '' }}
                                                    value="{{ $benefit->name }}">{{ $benefit->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--end::Input group-->

                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6 en-feature">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                            <span class="required">Benefit (English)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih atau Buat Benefit"></i>
                                        </label>
                                        <!--end::Label-->
                                        <div class="row">
                                            <div class="col-10">
                                                <select name="en_benefits[]" id="en_benefit" multiple
                                                    class="form-control select2">
                                                    @foreach ($enBenefits as $benefit)
                                                        <option {{ in_array($benefit->name, 
                                                            @$personalTrainer?->personal_trainer_en_benefits?->pluck('name')?->toArray() ?? [])
                                                            ? 'selected'
                                                            : '' }}
                                                            value="{{ $benefit->name }}">{{ $benefit->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" onclick="translateBenefitsEnglish()" class="btn btn-translate">Translate English</button>
                                            </div>
                                        </div>
                                    </div>

                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                            <span class="required">Benefit (Chinese)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Pilih atau Buat Benefit"></i>
                                        </label>
                                        <!--end::Label-->
                                        <div class="row">
                                            <div class="col-10">
                                                <select name="cn_benefits[]" id="cn_benefit" multiple class="form-control select2">
                                                    @foreach ($cnBenefits as $benefit)
                                                    <option {{ in_array($benefit->name,
                                                        @$personalTrainer?->personal_trainer_cn_benefits?->pluck('name')?->toArray() ?? [])
                                                        ? 'selected'
                                                        : '' }}
                                                        value="{{ $benefit->name }}">{{ $benefit->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" onclick="translateBenefitsChinese()" class="btn btn-translate">Translate Chinese</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="skill">
                                            <span class="required">Keahlian</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                data-bs-toggle="tooltip" title="Tambah Keahlian"></i>
                                        </label>
                                        <!--end::Label-->
                                        <select name="personal_trainer_skills[]" id="skill" multiple
                                            class="form-control select2" required>
                                            @foreach ($skills as $skill)
                                                <option
                                                    {{ in_array($skill->name, @$personalTrainer?->personal_trainer_skills?->pluck('name')?->toArray() ?? [])
                                                        ? 'selected'
                                                        : '' }}
                                                    value="{{ $skill->name }}">{{ $skill->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="skill_en">
                                            <span class="required">Keahlian (English)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                data-bs-toggle="tooltip" title="Tambah Keahlian"></i>
                                        </label>
                                        <!--end::Label-->
                                        <div class="row">
                                            <div class="col-10">
                                                <select name="personal_trainer_en_skills[]" id="skill_en" multiple
                                                    class="form-control select2">
                                                    @foreach ($enSkills as $skill)
                                                        <option
                                                            {{ in_array($skill->name, @$personalTrainer?->personal_trainer_en_skills?->pluck('name')?->toArray() ?? [])
                                                                ? 'selected'
                                                                : '' }}
                                                            value="{{ $skill->name }}">{{ $skill->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" onclick="translateSkillEnglish()" class="btn btn-translate">Translate English</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Input group-->
                                    <!--begin::Input group-->
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="skill_cn">
                                            <span class="required">Keahlian (Chinese)</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7"
                                                data-bs-toggle="tooltip" title="Tambah Keahlian"></i>
                                        </label>
                                        <!--end::Label-->
                                        <div class="row">
                                            <div class="col-10">
                                                <select name="personal_trainer_cn_skills[]" id="skill_cn" multiple
                                                    class="form-control select2">
                                                    @foreach ($cnSkills as $skill)
                                                        <option
                                                            {{ in_array($skill->name, @$personalTrainer?->personal_trainer_cn_skills?->pluck('name')?->toArray() ?? [])
                                                                ? 'selected'
                                                                : '' }}
                                                            value="{{ $skill->name }}">{{ $skill->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-2">
                                                <button type="button" onclick="translateSkillChinese()" class="btn btn-translate">Translate Chinese</button>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Input group-->
                                    @if (request()->routeIs('personal-trainer.edit'))
                                        <div class="card bg-light-warning card-xl-stretch py-2 px-4 mt-4 mb-2">
                                            <p class="text-warning">
                                                <span class="fw-bold">Perhatian!</span> Isi data sesuai dengan jadwal
                                                masing
                                                masing Coach, kosongkan hari yang tidak memiliki jadwal.
                                            </p>
                                        </div>
                                        <label class="fs-6 fw-bold form-label mt-3 mb-2">
                                            <span class="required">Sesi</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Isi sesi berdasarkan hari"></i>
                                        </label>
                                        <div class="row px-4">
                                            @foreach ($days as $day)
                                                <div class="col-sm-6 border pb-2">
                                                    <div class="row">
                                                        <div class="col-auto mt-2">
                                                            <div class="fv-row my-2">
                                                                <h5>{{ $day }}</h5>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    @foreach ($personalTrainer->personal_trainer_schedules()->where('day', $day)->orderBy('start_time')->get() as $key => $schedule)
                                                        <div class="border-buttom clone-wrapper{{ $day }}">
                                                            <div class="d-flex gap-1 align-items-center">
                                                                <div>
                                                                    <div class="fv-row my-1">
                                                                        <label>Mulai</label>
                                                                        <input type="time" class="form-control"
                                                                            value="{{ date('H:i', strtotime($schedule->start_time)) }}"
                                                                            name="personal_trainer_schedules[{{ $day }}][start_time][]"
                                                                            required />
                                                                    </div>
                                                                </div>
                                                                <div>
                                                                    <div class="fv-row my-1">
                                                                        <label>Selesai</label>
                                                                        <input type="time" class="form-control"
                                                                            value="{{ date('H:i', strtotime($schedule->end_time)) }}"
                                                                            name="personal_trainer_schedules[{{ $day }}][end_time][]"
                                                                            required />
                                                                    </div>
                                                                </div>
                                                                <div class="me-3">
                                                                    <div class="fv-row my-1">
                                                                        <label>Kuota</label>
                                                                        <input type="number" min="0"
                                                                            class="form-control"
                                                                            value="{{ $schedule->quota }}"
                                                                            name="personal_trainer_schedules[{{ $day }}][quota][]"
                                                                            required />
                                                                    </div>
                                                                </div>
                                                                <div class="pt-5">
                                                                    <a type="button" class="mx-0 px-0"
                                                                        id="btn-delete-element{{ $day }}"><i
                                                                            data-id="{{ $schedule->id }}"
                                                                            class="fa fa-times text-danger"></i></a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    <div id="to-replace-clone{{ $day }}">
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-auto">
                                                            <a type="button" class="btn btn-secondary"
                                                                id="btn-add-input{{ $day }}"><i
                                                                    class="fa fa-plus text-primary"></i></a>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                    <div class="fv-row mb-6">
                                        <!--begin::Label-->
                                        <label class="fs-6 fw-bold form-label mt-3" for="description">
                                            <span class="required text-dark">Deskripsi</span>
                                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                                title="Input Deskripsi"></i>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Input-->
                                        <textarea class="form-control" id="description" name="description">{{ old('description', @$personalTrainer->description) }}</textarea>
                                        <!--end::Input-->
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
                                            <option value="">--Pilih Status Aktif--</option>
                                            <option {{ @$personalTrainer->is_active == 1 ? 'selected' : '' }}
                                                value="1">AKTIF</option>
                                            <option {{ @$personalTrainer->is_active == 0 ? 'selected' : '' }}
                                                value="0">NON AKTIF</option>
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
                                            href="{{ route('gym-place.show', (request()->gym_place_id ?? @$personalTrainer->gym_place_id) . '?tab=personal_trainer') }}">
                                            <button type="button" class="btn btn-sm btn-secondary me-3">Batal</button>
                                        </a>
                                        <!--end::Button-->
                                        <!--begin::Button-->
                                        <button type="submit" data-kt-contacts-type="submit"
                                            class="btn btn-sm btn-primary">
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
    @foreach ($days as $key => $day)
        <div class="d-none">
            <div class="border-buttom clone-wrapper{{ $day }}" id="for-clone{{ $day }}">
                <div class="d-flex gap-1 align-items-center">
                    <div>
                        <div class="fv-row my-1">
                            <label>Mulai</label>
                            <input type="time" class="form-control"
                                name="personal_trainer_schedules[{{ $day }}][start_time][]" required />
                        </div>
                    </div>
                    <div>
                        <div class="fv-row my-1">
                            <label>Selesai</label>
                            <input type="time" class="form-control"
                                name="personal_trainer_schedules[{{ $day }}][end_time][]" required />
                        </div>
                    </div>
                    <div>
                        <div class="fv-row my-1">
                            <label>Kuota</label>
                            <input type="number" min="0" class="form-control"
                                name="personal_trainer_schedules[{{ $day }}][quota][]" required />
                        </div>
                    </div>
                    <div class="pt-4">
                        <a type="button" class="mx-0 px-0" id="btn-delete-element{{ $day }}"><i
                                class="fa fa-times text-danger"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                $("#btn-add-input{{ $day }}").click(function() {
                    var parent = $("#for-clone{{ $day }}");
                    var field_input = parent.clone().appendTo("#to-replace-clone{{ $day }}");
                });
                $(document).on("click", "#btn-delete-element{{ $day }}", function(e) {
                    let schedule_id = e.target.dataset.id;
                    if (schedule_id != undefined) {
                        $.ajax({
                            url: "{{ url('personal_trainer_schedule') }}/" + schedule_id,
                            method: "DELETE",
                            type: "JSON",
                            success: function(response) {
                                console.log(response);
                            },
                            error: function(response) {
                                console.log(response)
                            }
                        });
                    }
                    $(this).parents(".clone-wrapper{{ $day }}").remove();

                })
            });
        </script>
    @endforeach
    <script>
        $('#benefit').on('change', () => translator('#benefit', '#benefit_en'));
    </script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $('#skill').select2({
            placeholder: 'Pilih atau Buat Baru',
            tags: true
        });
        $('#skill_en').select2({
            placeholder: 'Pilih atau Buat Baru',
            tags: true
        });
        $('#skill_cn').select2({
            placeholder: 'Pilih atau Buat Baru',
            tags: true
        });

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
    </script>
    <script src="{{ asset('assets/plugins/custom/tinymce/tinymce.bundle.js') }}"></script>
    <script>
        tinymce.init({
            selector: '#description',
            height: 350,
            branding: false,
            menubar: false,
            toolbar: ["styleselect fontselect fontsizeselect",
                "undo redo | cut copy paste | bold italic | link image | alignleft aligncenter alignright alignjustify",
                "bullist numlist | outdent indent | blockquote subscript superscript | advlist | autolink | lists charmap | print preview |  code"
            ],
            plugins: "advlist autolink link image lists charmap print preview code"
        });
    </script>
    <script>
        function translateBenefitsEnglish() {
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
            });
        }
        
        function translateBenefitsChinese() {
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
            });
        }

        function translateSkillEnglish() {
            var selectedSkill = $('#skill').val();
            var translatedSkill = [];
            var promisesSkill = selectedSkill.map(function(skill) {
                return axios.get("{{ route('translate') }}", {
                    params: {
                        text: skill,
                    }
                }).then(function(response) {
                    console.log(response.data);
                    translatedSkill.push(response.data);
                });
            });
            Promise.all(promisesSkill).then(function() {
                $('#skill_en').empty().select2({
                    tags: true,
                    placeholder: 'Pilih atau Buat Baru',
                    allowClear: true
                });
                translatedSkill.forEach(function(skill) {
                    $('#skill_en').append(new Option(skill, skill, false, true));
                });
                $('#skill_en').trigger('change');
            });
        }
        
        function translateSkillChinese() {
            var selectedSkill = $('#skill').val();
            var translatedChineseSkill = [];
            var promiseSkillChinese = selectedSkill.map(function(skill) {
                return axios.get("{{ route('translate.chinese') }}", {
                    params: {
                        text: skill,
                    }
                }).then(function(response) {
                    translatedChineseSkill.push(response.data);
                })
            });
            Promise.all(promiseSkillChinese).then(function() {
                $('#skill_cn').empty().select2({
                    tags: true,
                    placeholder: 'Pilih atau Buat Baru',
                    allowClear: true
                });
                translatedChineseSkill.forEach(function(skill) {
                    $('#skill_cn').append(new Option(skill, skill, false, true));
                });
                $('#skill_cn').trigger('change');
            });
        }
    </script>
@endpush
