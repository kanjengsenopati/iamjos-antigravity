@extends('layouts.master', ['title' => 'Data Perubahan Coach', 'main' => 'Detail Perubahan Coach'])
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
            <div class="row g-5 g-xl-10">
                <div class="col-xl-6 mb-xl-10" style="max-height: 500px !important">
                    <div class="card card-flush">
                        <div class="card-body pt-0">
                            <div class="d-flex gap-2 align-items-center mb-3 border-0 pt-6">
                                <a href="{{ route('coach-change-history.index') }}" class="mt-1">
                                    <span class="menu-icon back pt-1">
                                        <i class="ki-duotone ki-arrow-left">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </a>
                                <h3 class="text-capitalize mb-0">Detail Perubahan Coach</h3>
                            </div>
                            <hr class="mt-8 mb-3">
                            <table class="table table-striped table-bordered">
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Nama User</td>
                                    <td class="text-label">
                                        {{ $coachChangeHistory->personal_trainer_packet_session_histories->user?->name ?? '-' }}
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Waktu Pengajuan</td>
                                    <td class="text-label">
                                        {{ date('d F Y H:i', strtotime($coachChangeHistory->created_at)) }}
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Coach Lama</td>
                                    <td class="text-label">
                                        {{ $coachChangeHistory->old_personal_trainer?->name ?? '-' }}
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Coach Baru</td>
                                    <td class="text-label">
                                        {{ $coachChangeHistory->new_personal_trainer?->name ?? '-' }}
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Alasan Ubah Coach</td>
                                    <td class="text-label">
                                        {{ $coachChangeHistory->reason ?? '-' }}
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Status</td>
                                    <td class="text-label">
                                        @if($coachChangeHistory->status =="PENDING")
                                            <button class="btn btn-success btn-sm mr-1 btn-approve" data-id="{{ $coachChangeHistory->id }}" data-status="APPROVED">Terima</button>
                                            <button class="btn btn-danger btn-sm btn-reject-reason" data-id="{{ $coachChangeHistory->id }}" data-status="REJECTED">Tolak</button>
                                            {{-- <span class='badge badge-warning'>Pending</span> --}}
                                        @elseif($coachChangeHistory->status =="APPROVED")
                                            <span class='badge badge-success'>Approved</span>
                                        @elseif($coachChangeHistory->status =="REJECTED")
                                            <span class='badge badge-danger'>Rejected</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Alasan Tolak</td>
                                    <td class="text-label">
                                        {{ $coachChangeHistory->reject_reason ?? '-' }}
                                    </td>
                                </tr>
                                <tr height=40px>
                                    <td style="width: 35%" class="fw-semibold text-label text-muted">Admin yang Mengkonfirmasi</td>
                                    <td class="text-label">
                                        {{ $coachChangeHistory->admin?->name ?? '-' }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-xl-6 mb-xl-10">
                    <div class="card card-flush">
                        <div class="card-body pt-0">
                            <div class="d-flex gap-2 align-items-center mb-3 border-0 pt-6">
                                <h3 class="text-capitalize mb-0 mt-1">Detail Membership</h3>
                            </div>
                            <hr class="mt-8 mb-3">
                            <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Tempat Gym</td>
                                        <td id="gym_place_membership">{{ $membership_detail['gym_place'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Avatar</td>                                        
                                        <td id="avatar_user"><img src="{{ url($membership_detail['avatar']) }}" width="100px" height="100px" alt=""> </td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td id="user_membership">{{ $membership_detail['name'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Membership ID</td>
                                        <td id="user_member_id">{{ $membership_detail['member_id'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Membership</td>
                                        <td id="name_membership">{{ $membership_detail['membership'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Paket Personal Trainer</td>
                                        <td id="name_personal_trainer">{{ $membership_detail['packet'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Sesi Tersisa</td>
                                        <td id="name_personal_trainer">{{ $membership_detail['remaining_session'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Tanggal Paket Dimulai</td>
                                        <td id="name_personal_trainer">{{ $membership_detail['start_active_date'] }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 35%" class="fw-semibold text-label text-muted">Tanggal Paket Selesai</td>
                                        <td id="remaining_session_personal_trainer">{{ $membership_detail['expiry_date'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(document).ready(() => {
        $(document).on('click', '.btn-reject-reason', function() {
            var button = $(this); // Store reference to 'this'
            var id = button.data('id');
            var status = button.data('status');

            Swal.fire({
                title: 'Alasan Penolakan',
                input: "textarea",
                inputPlaceholder: "Masukkan alasan penolakan...",
                inputAttributes: {
                    "aria-label": "Masukkan alasan penolakan"
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-sm fw-semibold btn-primary',
                    cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('coach-change-history.update', ':id') }}".replace(':id', id),
                        type: 'PUT',
                        data: {
                            id: id,
                            status: status,
                            reject_reason: result.value
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'Tutup',
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'Tutup',
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan pada server.',
                                icon: 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    });
                }
            });
        });


        // Handle approve and reject actions
        $(document).on('click', '.btn-approve', function() {
            var button = $(this); // Store reference to 'this'
            var id = button.data('id');
            var status = button.data('status');
            
            Swal.fire({
                title: `Apakah anda yakin ${status} data ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('coach-change-history.update', ':id') }}".replace(':id', id),
                        type: 'PUT',
                        data: {
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'Tutup',
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'Tutup',
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan pada server.',
                                icon: 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    });
                }
            });
        });
    });
</script>
@endpush