<div class="row px-2 pt-5">
    <div class="col-sm-3">
        <img class="img img-fluid"
            src="{{ $user->avatar ? asset($user->avatar) : asset('/assets/media/avatars/blank.png') }}"
            alt="Avatar User">
    </div>
    <div class="col ms-1">
        <table class="profile table-bordered table">
            <tr>
                <td class="grey w-25">Membership ID</td>

                <td>{{ $user->membership_user?->member_id }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Price Reset Code</td>

                <td>
                    <div class="d-flex align-items-center">
                        <span id="priceResetCode" class="text-muted">••••••</span>
                        <button class="btn btn-link btn-sm ms-2 me-3" type="button" onclick="togglePriceResetCode()">
                            <i class="fa fa-eye"></i>
                        </button>
                        <button class="btn btn-primary btn-sm" onclick="generatePriceResetCode('{{ $user->id }}')">
                            Generate Code
                        </button>
                    </div>
                    <script>
                        const originalCode = "{{ $user->price_reset_code }}";
                        function togglePriceResetCode() {
                            const span = document.getElementById('priceResetCode');
                            if(span.textContent === '••••••') {
                                span.textContent = originalCode;
                                span.classList.remove('text-muted');
                            } else {
                                span.textContent = '••••••';
                                span.classList.add('text-muted');
                            }
                        }
                        
                        function generatePriceResetCode(userId) {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = `generate-price-reset-code/${userId}`;
                            
                            const csrfToken = document.createElement('input');
                            csrfToken.type = 'hidden';
                            csrfToken.name = '_token';
                            csrfToken.value = '{{ csrf_token() }}';
                            form.appendChild(csrfToken);
                            
                            document.body.appendChild(form);
                            form.submit();
                        }
                    </script>
                </td>
            </tr>
            <tr>
                <td class="grey w-25">Nama</td>

                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Email</td>

                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Phone</td>

                <td>{{ $user->phone }}</td>
            </tr>
            <tr>
                <td class="grey w-25">NIK</td>

                <td>{{ $user->nik }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Jenis Kelamin</td>

                <td>{{ $user->gender == 'MALE' ? 'Laki-laki' : 'Perempuan' }}
                </td>
            </tr>
            <tr>
                <td class="grey w-25">Tanggal Lahir</td>

                <td>{{ $user->birth_date ?? 'Belum diatur' }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Bergabung Sejak</td>

                <td>{{ $user->created_at }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Status Complimentary</td>

                <td>
                    @if ($user->is_complimentary == true)
                    <span class="badge badge-success">User Complimentary</span>
                    @else
                    <span class="badge badge-danger">User Non Complimentary</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="grey w-25">Status</td>

                <td>
                    @if ($user->is_active)
                    <span class="badge badge-success">Aktif</span>
                    @else
                    <span class="badge badge-danger">Non Aktif</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="grey w-25">Tinggi Badan</td>

                <td>{{ $user->height }} Cm</td>
            </tr>
            <tr>
                <td class="grey w-25">Berat Badan</td>

                <td>{{ $user->weight }} Kg</td>
            </tr>
            <tr>
                <td class="grey w-25">Target Yang Ingin Dicapai</td>

                <td>{{ $user->goal_translated }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Rutinitas</td>

                <td>{{ $user->routine_translated }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Durasi</td>

                <td>{{ $user->duration_translated }}</td>
            </tr>
            <tr>
                <td class="grey w-25">Berhasil Upload Avatar ke Gate Service</td>

                <td>
                    @if ($user->is_gate_avatar_been_uploaded)
                    <span class="badge badge-light-success mt-4">Sudah Berhasil Upload</span>
                    @else
                    <span class="badge badge-light-danger mt-4">Belum Berhasil Upload</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="grey w-25">Firebase ID</td>

                <td>
                    <form id="resetFirebaseForm" action="{{ route('user.update.firebase-id', $user->id) }}" method="post">
                        @csrf
                    </form>
                    <button type="button" class="btn btn-sm btn-danger" onclick="resetFirebaseId()">
                        Reset Firebase ID
                    </button>

                    <script>
                        function resetFirebaseId() {
                            Swal.fire({
                                title: 'Reset Firebase ID?',
                                text: "Anda yakin ingin mereset Firebase ID user ini?",
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonColor: '#3085d6',
                                cancelButtonColor: '#d33',
                                confirmButtonText: 'Ya, Reset!',
                                cancelButtonText: 'Batal'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    document.getElementById('resetFirebaseForm').submit();
                                }
                            });
                        }
                    </script>
                </td>
            </tr>
        </table>
    </div>
</div>
<!--begin::Deactivate Account-->
<div class="card mt-6">
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
        <form id="kt_account_deactivate_form" class="form" action="{{ route('user.deactive') }}" method="POST"
            enctype="multipart/form-data">
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
                            <h4 class="text-gray-900 fw-bold">Nonaktifkan Akun
                            </h4>
                            <div class="fs-6 text-gray-700">Pastikan untuk
                                mengecek
                                ulang dengan cermat
                                sebelum menonaktifkan akun ini. Tindakan ini
                                akan
                                memblokir akses pengguna
                                ke platform. Pastikan bahwa penonaktifan ini
                                sesuai
                                dengan kebijakan kami
                                dan telah melalui proses verifikasi yang
                                tepat. Harap periksa kembali alasan penonaktifan
                                dan
                                pastikan bahwa langkah
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
                    class="btn btn-danger btn-sm fw-semibold btn-deactive btn-superadmin">Nonaktifkan
                    Akun</button>
                @else
                <button id="kt_account_deactivate_account_submit" type="submit"
                    class="btn btn-success btn-sm fw-semibold btn-active-user btn-superadmin">Aktifkan
                    Akun</button>
                @endif
            </div>
            <!--end::Card footer-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Content-->
</div>
<!--end::Deactivate Account-->