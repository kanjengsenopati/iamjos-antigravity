@extends('layouts.master', ['title' => 'Detail User', 'main' => 'User'])
@push('css')
<style>
    input#dateRangeTransaction,
    input#dateRangeTransaction:focus {
        border: none;
        outline: none;
        color: white;
    }
</style>
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
                    <div class="card card-flush h-lg-100" id="kt_contacts_main">
                        <!--begin::Card header-->
                        <div class="card-header pt-7" id="kt_chat_contacts_header">
                            <!--begin::Card title-->
                            <div class="card-title d-flex align-items-center gap-3">
                                <!--begin::Svg Icon | path: icons/duotune/communication/com005.svg-->
                                <a href="{{ route('user.index') }}">
                                    <span class="menu-icon back pt-1">
                                        <i class="ki-duotone ki-arrow-left">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </a>
                                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center">Detail User</h1>
                                    <a href="{{ route('user.edit', $user->id) }}">
                                    <i class="ki-duotone ki-notepad-edit fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </a>
                                <!--end::Svg Icon-->
                            </div>
                            <!--end::Card title-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        {{-- <div class="card-body pt-5"> --}}
                            <div class="d-flex flex-column flex-md-row rounded border p-10">
                                <ul class="nav nav-tabs nav-pills border-0 flex-row flex-md-column me-5 mb-3 mb-md-0 fs-6">
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_information">Information User</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_health_information">Health Information</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_membership_personal_trainer">Membership & Coach</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_annual_payment">Annual Payment</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_membership_coach_history">Riwayat Aktivitas</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_transaction_history">Riwayat Pembelian</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_checkin_history">Riwayat CheckIn</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_notes">Notes</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_additional_file">Additional File</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_gym_class_user">Jadwal Kelas</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_pt_user">Sesi Coach</a>
                                    </li>
                                    <li class="nav-item w-md-200px me-0">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_physioterapy_history">Fisioterapi & Sesi</a>
                                    </li>
                                    <li class="nav-item w-md-200px">
                                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_referral_code_history">Riwayat Referral Kode</a>
                                    </li>
                                </ul>
                                <div class="tab-content w-md-950px overflow-auto" id="myTabContent">
                                    <div class="tab-pane fade show active" id="kt_tab_information" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.information')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_health_information" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.health-information')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_membership_personal_trainer" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.membership-personal-trainer')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_annual_payment" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.annual-payment')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_membership_coach_history" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.membership-personal-trainer-history')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_transaction_history" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.transaction-history')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_checkin_history" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.checkin-history')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_notes" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.note')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_additional_file" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.additional-file')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_gym_class_user" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.gymclass')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_pt_user" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.session-user')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_physioterapy_history" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.physioterapy-history')
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="kt_tab_referral_code_history" role="tabpanel">
                                        <div class="card card-flush h-lg-100">
                                            <div class="card-body d-flex flex-column h-100">
                                                @include('admins.user.tab.referral-code')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <!--end::Card body-->

                        {{-- </div> --}}
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
    @include('admins.user.modal')
    @endsection
    @push('js')
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

            function deleteMembership(e) {
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Anda akan menghapus membership ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.value) {
                        $(e).parents('form').submit();
                    }
                });
            }

            function deleteBundling(e) {
                Swal.fire({
                    title: 'Apakah anda yakin?',
                    text: "Anda akan menghapus paket PT ini!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function(result) {
                    if (result.value) {
                        $(e).parents('form').submit();
                    }
                });
            }
    </script>
    @endpush