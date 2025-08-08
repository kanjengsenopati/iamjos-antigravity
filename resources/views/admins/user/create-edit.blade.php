@extends('layouts.master', ['main' => 'Data User', 'title' => request()->routeIs('user.create') ? 'Tambah User' : 'Edit
User'])
@section('content')
<!--begin::Content wrapper-->
<div class="d-flex pt-6 flex-column flex-column-fluid">
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Basic info-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">{{ request()->routeIs('user.create') ? 'Tambah User' : 'Edit User' }}
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                    <!--begin::Form-->
                    <form id="kt_account_profile_details_form" class="form" method="POST" enctype="multipart/form-data"
                        action="{{ request()->routeIs('user.create') ? route('user.store') : route('user.update',
                        @$user->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body">

                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="name" class="col-lg-4 col-form-label required fw-semibold fs-6">Nama
                                    Lengkap</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="text" id="name" name="name" class="form-control"
                                                placeholder="Nama Lengkap User"
                                                value="{{ @$user->name ?? old('name') }}" required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6"
                                    for="email">Email</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input id="email" type="email" name="email" class="form-control"
                                        placeholder="Masukkan Email User" value="{{ @$user->email ?? old('email') }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="phone" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="required">No Hp</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="Masukkan nomor HP dengan format 62">
                                    </span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input id="phone" type="integer" name="phone" class="form-control"
                                        placeholder="Masukkan nomor HP dengan format +62"
                                        value="{{ @$user->phone ?? old('phone') }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="nik" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="required">Nomor Induk Kependudukan (NIK)</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="Masukkan nomor Induk Kependudukan">
                                    </span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input id="nik" type="text" name="nik" class="form-control"
                                        placeholder="Masukkan nomor Induk Kependudukan" maxlength="16"
                                        value="{{ @$user->nik ?? old('nik') }}" />
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="gender" class="col-lg-4 col-form-label fw-semibold fs-6">Jenis
                                    Kelamin</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <select name="gender" id="gender" class="form-control">
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="MALE" {{ @$user->gender == 'MALE' ? 'selected' : '' }}>Laki-laki
                                        </option>
                                        <option value="FEMALE" {{ @$user->gender == 'FEMALE' ? 'selected' : ''
                                            }}>Perempuan
                                        </option>
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Input group-->
                            <!--begin::Input group-->
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="birth_date" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="required">Tanggal Lahir </span>
                                    <span class="ms-1" data-bs-toggle="tooltip" title="Masukkan Tanggal Lahir">
                                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input type="date" name="birth_date" class="form-control"
                                        placeholder="Masukkan Tanggal Lahir"
                                        value="{{ @$user->birth_date ?? old('birth_date') }}" required />
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="weight" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span>Berat Badan</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="Masukkan Berat Badan dalam satuan KG">
                                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input id="weight" type="integer" name="weight" class="form-control"
                                        placeholder="Masukkan Berat Badan dalam satuan KG"
                                        value="{{ @$user->weight ?? old('weight') }}" />
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="height" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span>Tinggi Badan</span>
                                    <span class=" ms-1" data-bs-toggle="tooltip"
                                        title="Masukkan Tinggi Badan dalam satuan CM">
                                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <input id="height" type="integer" name="height" class="form-control"
                                        placeholder="Masukkan Tinggi Badan dalam satuan CM"
                                        value="{{ @$user->height ?? old('height') }}" />
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="goal" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span>Apa Goal yang ingin anda capai?</span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <select name="goal" id="goal" class="form-control">
                                        <option value="">Pilih Goal</option>
                                        <option value="WEIGHT_LOSS" {{ @$user->goal == 'WEIGHT_LOSS' ? 'selected' : ''
                                            }}>Menurunkan Berat Badan</option>
                                        <option value="MUSCLE_MASS" {{ @$user->goal == 'MUSCLE_MASS' ? 'selected' : ''
                                            }}>Menambah Massa Otot</option>
                                        <option value="BODY_FORMATION" {{ @$user->goal == 'BODY_FORMATION' ? 'selected'
                                            : '' }}>Pembentukan Badan</option>
                                        </option>
                                        <option value="IMPROVE_HEALTH" {{ @$user->goal == 'IMPROVE_HEALTH' ? 'selected'
                                            : ''
                                            }}>Meningkatkan Kesehatan</option>
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="routine_exercise" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span>Pilih Seberapa Sering Anda Berolahraga?</span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <select name="routine_exercise" id="routine_exercise" class="form-control">
                                        <option value="">Pilih Seberapa Sering Anda Berolahraga?</option>
                                        <option value="0" {{ @$user->routine_exercise == '0' ? 'selected' : '' }}>Jarang
                                            Berolahraga</option>
                                        <option value="1-3" {{ @$user->routine_exercise == '1-3' ? 'selected' : '' }}>1
                                            - 3 Hari </option>
                                        <option value="4" {{ @$user->routine_exercise == '4' ? 'selected' : '' }}>4 Hari
                                        </option>
                                        <option value="5" {{ @$user->routine_exercise == '5' ? 'selected' : '' }}>5 Hari
                                        </option>
                                        <option value="6-7" {{ @$user->routine_exercise == '6-7' ? 'selected' : '' }}>6
                                            - 7 Hari </option>
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="duration_exercise" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span>Pilih seberapa lama waktu yang kamu habiskan dalam satu sesi
                                        berolahraga?</span>
                                </label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <select name="duration_exercise" id="duration_exercise" class="form-control">
                                        <option value="">Pilih seberapa lama waktu yang kamu habiskan dalam satu sesi
                                            berolahraga?</option>
                                        <option value="<30" {{ @$user->duration_exercise == '<30' ? 'selected' : '' }}>
                                                <30 Menit</option>
                                        <option value="30-60" {{ @$user->duration_exercise == '30-60' ? 'selected' : ''
                                            }}>30
                                            Menit - 60 Menit</option>
                                        <option value=">60" {{ @$user->duration_exercise == '>60' ? 'selected' : ''
                                            }}>60
                                            Menit</option>
                                    </select>
                                </div>
                                <!--end::Col-->
                            </div>

                            @if (request()->routeIs('user.edit'))
                            <div class="row mb-6">
                                <!-- begin::Label -->
                                <label for="is_avatar_update" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="">Edit Foto Profil</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="pilih apakah avatar pada profil user dapat diupdate"></span>
                                </label>
                                <!-- end::Label -->
                                <!-- begin::Col -->
                                <div class="col-lg-8 fv-row">
                                    <select name="is_avatar_update" id="is_avatar_update" class="form-control">
                                        <option value="1" {{ @$user->is_avatar_update == 1 ? 'selected' : '' }}>Aktif
                                        </option>
                                        <option value="0" {{ @$user->is_avatar_update == 0 ? 'selected' : '' }}>Tidak
                                            Aktif
                                        </option>
                                    </select>
                                </div>
                                <!-- end::Col -->
                            </div>
                            <div class="row mb-6">
                                <!-- begin::Label -->
                                <label for="member_id" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="">Membership ID</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="Masukkan Membership ID dengan Format Yang Sesuai"></span>
                                </label>
                                <!-- end::Label -->
                                <!-- begin::Col -->
                                <div class="col-lg-8 fv-row">
                                    <input id="member_id" type="text" name="member_id" class="form-control"
                                        placeholder="Contoh: NG000001" value="{{ isset($user->membership_user) ?
                                         $user->membership_user->member_id : old('member_id') }}" />
                                </div>
                                <!-- end::Col -->
                            </div>
                            @endif

                            <div class="row mb-6">
                                <!-- begin::Label -->
                                <label for="is_complimentary" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="">Status Complimentary</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="pilih status complimentary user"></span>
                                </label>
                                <!-- end::Label -->
                                <!-- begin::Col -->
                                <div class="col-lg-8 fv-row">
                                    <select name="is_complimentary" id="is_complimentary" class="form-control">
                                        <option value="0" {{ @$user->is_complimentary == 0 ? 'selected' : '' }}>User Non Complimentary</option>
                                        <option value="1" {{ @$user->is_complimentary == 1 ? 'selected' : '' }}>User Complimentary</option>
                                    </select>
                                </div>
                                <!-- end::Col -->
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label for="avatar" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span>Avatar</span>
                                </label>
                                <div class="col-lg-8 fv-row" id="avatar_old">
                                    <img src="{{ asset(@$user->avatar) }}" alt="Avatar"
                                        style="max-height: 150px; max-width: 150px;">
                                </div>
                                <!--end::Label-->
                                <div id="upload_options">
                                    <button class="btn btn-sm btn-primary my-2" type="button" onclick="showUploadOptions()">Upload Avatar</button>
                                </div>
                                <div id="upload_choice" style="display:none;">
                                    <button class="btn btn-sm btn-primary" type="button" onclick="chooseFile()">Choose File</button>
                                    <button class="btn btn-sm btn-primary" type="button" onclick="showCamera()">Take Photo</button>
                                </div>
                                <input type="file" id="file_input" style="display:none;" accept="image/*"
                                    onchange="displayImage(this)">
                                <div class="my-2" id="camera" style="display:none;">
                                    <div id="my_camera"></div>
                                    <button id="take" class="btn btn-sm btn-primary my-2" type="button" onclick="take_snapshot()">Take Snapshot</button>
                                    <div id="results"></div>
                                </div>
                                <!--begin::Col-->
                                <div class="col-lg-8 fv-row">
                                    <img id="avatar_preview" src="" alt="Avatar Preview"
                                        style="display:none; max-height: 150px; max-width: 150px;">
                                    <input type="hidden" id="avatar_input" name="avatar" value="">
                                    <button id="rechoose_photo" style="display:none;" class="btn btn-sm btn-primary mt-2" type="button" onclick="rechoosePhoto()">Rechoose Photo</button>
                                    <button id="retake_photo" style="display:none;" class="btn btn-sm btn-primary mt-2" type="button" onclick="retakePhoto()">Retake Photo</button>
                                    {{--
                                    <x-form.image-upload label="Avatar" name="avatar" :value="@$user->avatar ?? null"
                                        nullable='1' /> --}}
                                </div>
                                <!--end::Col-->
                            </div>

                            <div class="row mb-6">
                                <!-- begin::Label -->
                                <label for="member_id" class="col-lg-4 col-form-label fw-semibold fs-6">
                                    <span class="">Berhasil Upload Avatar ke Gate Service</span>
                                    <span class="ms-1" data-bs-toggle="tooltip"
                                        title="Masukkan Membership ID dengan Format Yang Sesuai"></span>
                                </label>
                                <!-- end::Label -->
                                <!-- begin::Col -->
                                <div class="col-lg-8 fv-row">
                                    @if (@$user->is_gate_avatar_been_uploaded == 1)
                                        <span class="badge badge-light-success mt-4">Sudah Berhasil Upload</span>
                                    @else
                                        <span class="badge badge-light-danger mt-4">Belum Berhasil Upload</span>
                                    @endif
                                </div>
                                <!-- end::Col -->
                            </div>

                            @if (request()->routeIs('user.edit'))
                            <input type="hidden" name="id" value="{{ @$user->id }}">
                            @endif
                        </div>
                        <!--end::Card body-->
                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('user.index') }}" class="btn btn-sm btn-secondary me-3">Batal</a>
                            <button type="submit" class="btn btn-sm btn-primary"
                                id="kt_account_profile_details_submit">Simpan</button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Basic info-->
            <!--begin::Deactivate Account-->
            @if (request()->routeIs('user.edit'))
            <div class="card">
                <!--begin::Card header-->
                <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
                    data-bs-target="#kt_account_deactivate" aria-expanded="true" aria-controls="kt_account_deactivate">
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">Nonaktifkan Akun</h3>
                    </div>
                </div>
                <!--end::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_deactivate" class="collapse show">
                    <!--begin::Form-->
                    <form id="kt_account_deactivate_form" class="form" action="{{ route('user.deactive') }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="id" value="{{ @$user->id }}">
                        <!--begin::Card body-->
                        <div class="card-body border-top p-9">
                            <!--begin::Notice-->
                            <div class="notice d-flex bg-light-warning rounded border-warning border
                                border-dashed mb-9 p-6">
                                <!--begin::Icon-->
                                <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <!--end::Icon-->
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-stack flex-grow-1">
                                    <!--begin::Content-->
                                    <div class="fw-semibold">
                                        <h4 class="text-gray-900 fw-bold">Nonaktifkan Akun</h4>
                                        <div class="fs-6 text-gray-700">Pastikan untuk mengecek ulang dengan cermat
                                            sebelum menonaktifkan akun ini. Tindakan ini akan memblokir akses pengguna
                                            ke platform. Pastikan bahwa penonaktifan ini sesuai dengan kebijakan kami
                                            dan telah melalui proses verifikasi yang
                                            tepat. Harap periksa kembali alasan penonaktifan dan pastikan bahwa langkah
                                            ini diambil dengan pertimbangan matang.
                                            Terima kasih atas perhatiannya.
                                        </div>
                                    </div>
                                    <!--end::Content-->
                                </div>
                                <!--end::Wrapper-->
                            </div>
                            <!--end::Notice-->
                        </div>
                        <!--end::Card body-->
                        <!--begin::Card footer-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            @if (@$user->is_active == 1)
                            <button id="kt_account_deactivate_account_submit" type="submit"
                                class="btn btn-danger btn-sm fw-semibold btn-deactive">Nonaktifkan
                                Akun</button>
                            @else
                            <button id="kt_account_deactivate_account_submit" type="submit"
                                class="btn btn-success btn-sm fw-semibold btn-active-user">Aktifkan
                                Akun</button>
                            @endif
                        </div>
                        <!--end::Card footer-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
            </div>
            @endif
            <!--end::Deactivate Account-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->
@endsection
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.26/webcam.min.js"></script>
<script type="text/javascript">
        function showUploadOptions() {
            document.getElementById('upload_options').style.display = 'none';
            document.getElementById('upload_choice').style.display = 'block';
        }

        // re upload photo
        function rechoosePhoto() {
            document.getElementById('rechoose_photo').style.display = 'none'
            document.getElementById('upload_choice').style.display = 'block'
            document.getElementById('avatar_preview').src = '';
            document.getElementById('avatar_preview').style.display = 'none';
            document.getElementById('avatar_input').value = '';
        }

        // choose photo
        function chooseFile() {
            Webcam.reset()
            document.getElementById('file_input').click();
            document.getElementById('camera').style.display = 'none'
            document.getElementById('take').style.display = 'none'
        }

        // function to show camera
        function showCamera() {
            document.getElementById('camera').style.display = 'block';
            Webcam.set({
                width: 320,
                height: 240,
                image_format: 'jpeg',
                jpeg_quality: 90
            });
            Webcam.attach('#my_camera');
        }

        // take snapshot photo
        let stream = ''
        function take_snapshot() {
            Webcam.snap(function(data_uri) {
                document.getElementById('results').innerHTML = '<img src="' + data_uri + '"/>';
                document.getElementById('avatar_preview').src = data_uri;
                document.getElementById('avatar_preview').style.display = 'block';
                document.getElementById('avatar_input').value = data_uri;
                document.getElementById('camera').style.display = 'none';
                document.getElementById('upload_choice').style.display = 'none';
                document.getElementById('avatar_old').style.display = 'none';
                document.getElementById('retake_photo').style.display = 'block'
            });
            Webcam.reset();
        }

        // retake photo
        function retakePhoto() {
            document.getElementById('avatar_preview').src = '';
            document.getElementById('avatar_preview').style.display = 'none';
            document.getElementById('avatar_input').value = '';
            document.getElementById('upload_choice').style.display = 'block'
            document.getElementById('results').style.display = 'none'

            showCamera();
            document.getElementById('retake_photo').style.display = 'none'
        }

        // display image after choose image
        function displayImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('avatar_preview').src = e.target.result;
                    document.getElementById('avatar_preview').style.display = 'block';
                    document.getElementById('avatar_input').value = e.target.result;
                };
                reader.readAsDataURL(input.files[0]);
                document.getElementById('upload_choice').style.display = 'none';
                document.getElementById('avatar_old').style.display = 'none';
            }
            document.getElementById('rechoose_photo').style.display = 'block'
        }
</script>
<script>
    // add sweetalert on btn-deactive class
    $('.btn-deactive').on('click', function(e) {
        e.preventDefault();
        var form = $(this).parents('form');
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Anda akan menonaktifkan akun ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Nonaktifkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function(result) {
            if (result.value) {
                form.submit();
            }
        });
    });
    $('.btn-active-user').on('click', function(e) {
        e.preventDefault();
        var form = $(this).parents('form');
        Swal.fire({
            title: 'Apakah anda yakin?',
            text: "Anda akan mengaktifkan akun ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Aktifkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then(function(result) {
            if (result.value) {
                form.submit();
            }
        });
    });
</script>
@endpush