@extends('layouts.pt-master', ['title' => 'Detail Member'])
@push('css')
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />

<style>
    [data-bs-theme="light"] {
        --color-gray-9: #8C8C8C;
        --color-gray-10: #F1F1F2;
        --color-white: #262626;
        --shadow: none;
    }

    [data-bs-theme="dark"] {
        --color-gray-9: #434343;
        --color-gray-10: #262626;
        --color-white: #FFFFFF;
        --shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.12), 0px 0px 2px 0px rgba(0, 0, 0, 0.12);
    }

    .text-white2 {
        color: var(--color-white) !important;
    }

    .page-item .page-link {
        border-radius: 50%;
        height: 2.625rem;
        min-width: 2.625rem;
        font-weight: 500;
        font-size: 0.875rem;
    }

    .input-wrap {
        color: #8C8C8C;
        border-radius: 0.625rem !important;
        background: var(--color-gray-10);
        box-shadow: var(--shadow);
    }

    .form-select,
    .form-control,
    .form-control:focus {
        color: #8C8C8C;
        border-radius: 0.625rem;
        background-color: var(--color-gray-10);
        border: none;

    }

    img.search {
        position: absolute;
        left: 1.2rem;
        top: 1rem;
    }

    .input-wrap input,
    .input-wrap input[type=text]:focus {
        border: none;
        outline: none;
        margin-left: 2.2rem;
        background-color: var(--color-gray-10);
        border-radius: 0.625rem;
        color: #8C8C8C;
        font-weight: 400;
        padding-top: 12px;
    }

    .input-wrap input::placeholder {
        color: #8C8C8C;
    }

    .btn.type {
        border-radius: 50%;
        width: 2.5rem;
        height: 3.1rem;
        background: #262626;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .btn.type.active {
        background-color: #434343;
    }

    .text-grey {
        color: #BFBFBF !important;
    }

    .text-grey2 {
        color: #8C8C8C !important;
    }

    th,
    .fw-400 {
        font-weight: 400 !important;
    }

    .btn-status {
        font-size: 0.75rem;
        outline: none;
        border: none;
        border-radius: var(--radius-m, 0.75rem);
        padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
    }

    .bg-dark {
        background-color: var(--color-gray-10) !important;
        border: none !important;
        position: relative;
        overflow: hidden;
    }

    .bg-red {
        background-color: #D83C15;
    }

    .bg-blue {
        background-color: #1C7EFF;
    }

    .bg-gray,
    .bg-gray:hover {
        background: var(--color-gray-10);
    }

    .bg-orange {
        background-color: #E27900;
    }

    .bg-green {
        background-color: #74A00C;
    }


    .bg-red {
        background-color: #D83C15;
    }

    .bg-green100 {
        background-color: #EBFEF3;
    }

    .bg-purple100 {
        background-color: #FCF6FD;
    }

    .bg-dark.purple:before {
        content: '';
        position: absolute;
        background-color: #C366CF;
        width: 4px;
        height: 100%;
        bottom: 0;
        left: -1px;
        top: 0;
    }

    .bg-dark.green:before {
        content: '';
        position: absolute;
        background-color: #0EC776;
        width: 4px;
        height: 100%;
        bottom: 0;
        left: -1px;
        top: 0;
    }

    .type {
        width: 1.5rem;
        height: 1.5rem;
    }

    .type img {
        width: 1rem;
        height: 1rem;
    }

    .text-blue {
        color: #2896FF;
    }

    .text-green {
        color: #99CD15;
    }

    .text-orange {
        color: #FFA100;
    }

    .text-red {
        color: #D83C15;
    }

    .status {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.125rem;
        /* 150% */
        border-radius: var(--radius-m, 0.75rem);
        background: var(--color-gray-10);
        padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
        justify-content: center;
        align-items: center;
    }

    .violet {
        color: var(--fuchsia-add-2500, #C366CF);
    }

    .green {
        color: var(--salem-add-1500, #0ABF70);
    }

    td {
        color: white !important;
        font-weight: 400 !important;
    }

    .border-radius-xxl {
        border-radius: 1.25rem !important;
    }

    .border-radius-xxxl {
        border-radius: 2rem !important;
    }

    .fw-600 {
        font-weight: 600 !important;
    }

    .fw-500 {
        font-weight: 500 !important;
    }

    .wrap-btn button {
        border: none;
        outline: none;
    }

    .border-dash {
        border-radius: var(--radius-s, 0.5rem);
        border: var(--spacing-00, 1px) dashed var(--color-gray-9, #434343);
    }

    hr {
        border-top: 1px solid #434343;
    }

    a {
        text-decoration: none;
    }

    .tab {
        font-weight: 500;
        color: #8C8C8C;
    }

    .tab.active {
        color: var(--color-white);
        border-bottom: 1px solid #B18D41;
        padding-bottom: 0.6rem;
        font-weight: 600;
    }

    .fs-big {
        font-size: 2.4375rem;
    }

    .wrap-button {
        border-radius: var(--radius-xl, 1.25rem);
        background: var(--color-gray-10);
        padding: var(--spacing-02, 0.25rem) var(--spacing-04, 0.75rem) var(--spacing-02, 0.25rem) var(--spacing-02, 0.25rem);
    }

    .btn-gray {
        font-weight: 500;
        text-decoration: none;
        font-size: 0.875rem;
        color: #8C8C8C;
        margin: 0.5rem 0;
        padding: var(--spacing-03, 0.5rem) var(--spacing-05, 1rem);
    }

    .btn-gray.active {
        color: white;
        border-radius: var(--radius-xl, 1.25rem);
        background: var(--color-gray-9, );
    }

    .modal-content {
        border-radius: 1rem !important;
    }

    .btn-active-primary {
        color: var(--color-white);
    }



    @media screen and (max-width: 768px) {
        .fs-big {
            font-size: 1.8rem;
        }
    }

    @media (min-width: 992px) {
        .wrap-card {
            height: 120vh;
            overflow: scroll;
        }
    }
</style>
@endpush
@section('content')
<div class="content pt pt-5 d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="app-container container-xxl">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <!--begin::Container-->
            <div id="kt_toolbar_container" class="d-flex flex-stack">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                    data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                    class="page-title d-flex flex-column flex-wrap me-3 mb-5 mb-lg-0">
                    <!--begin::Title-->
                    <h2 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Member </h2>
                    <p class="text-grey"><span class="text-primary">Home</span> - <span
                            class="text-primary">Member</span> - Detail Member</p>
                    <!--end::Title-->
                </div>
            </div>
            <!--end::Container-->
        </div>
        <div class="row">
            <div class="col-lg-5 mb-8">
                <div class="card border-radius-xxl">
                    <!--begin::Card body-->
                    <div class="card-body d-flex flex-center flex-column pt-12 p-9">
                        <!--begin::Avatar-->
                        <div class="symbol symbol-100px symbol-circle mb-5">
                            <img src="{{ asset($user->avatar ?? 'assets/media/avatars/blank.png') }}" alt="">
                        </div>
                        <!--end::Avatar-->
                        <!--begin::Name-->
                        <div class="d-flex justify-content-center text-center flex-column align-items-center">
                            <h6 class="fs-2 fw-600 text-hover-primary mb-0">{{ $user->name ?? '' }}</h6>
                            <br>
                            <button class="btn-status fw-600 bg-gray text-blue mt-3 mb-6">Aktif</button>
                        </div>
                        <!--end::Name-->
                        <!--begin::Position-->
                        <h1 class="fs-6 fw-600 mb-2">Health Information</h1>
                        <div class="d-flex justify-content-center flex-wrap gap-4 align-items-center mt-3">
                            <div class="border-dash text-center p-4">
                                <h1 class="fs-6 fw-600">{{ $user->weight ?? '' }} kg</h1>
                                <p class="text-grey mb-0">Berat Badan</p>
                            </div>
                            <div class="border-dash text-center p-4">
                                <h1 class="fs-6 fw-600">{{ $user->height ?? '' }} cm</h1>
                                <p class="text-grey mb-0">Tinggi Badan</p>
                            </div>
                            <div class="border-dash text-center p-4">
                                <h1 class="fs-6 fw-600">{{ $user->muscle_mass ?? 0 }} kg</h1>
                                <p class="text-grey mb-0">Muscle Mass</p>
                            </div>
                        </div>
                        <!--end::Position-->
                        <div
                            class="d-flex w-100 text-sm-center text-lg-start justify-content-start flex-column gap-4 mt-8">
                            <div>
                                <h1 class="fs-6 fw-600">Jenis Kelamin</h1>
                                <p class="text-grey mb-0">{{ $user->gender == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                            </div>
                            <div>
                                <h1 class="fs-6 fw-600">Masa Aktif</h1>
                                <p class="text-grey mb-0">{{ $activePackage->start_active_date ?? '' }} s/d {{
                                    $activePackage->expiry_date ?? '' }}</p>
                            </div>
                            <div>
                                <h1 class="fs-6 fw-600">Berat Badan</h1>
                                <p class="text-grey mb-0">{{ $user->weight ?? '' }} kg</p>
                            </div>
                            <div>
                                <h1 class="fs-6 fw-600">Tinggi Badan</h1>
                                <p class="text-grey mb-0">{{ $user->height ?? '' }} cm</p>
                            </div>
                            <div>
                                <h1 class="fs-6 fw-600">Muscle Mass</h1>
                                <p class="text-grey mb-0">{{ $user->muscle_mass ?? 0 }} kg</p>
                            </div>
                        </div>

                    </div>
                    <!--end::Card body-->
                </div>
            </div>
            <div class="col-lg-7">
                <ul class="nav nav-pills d-flex flex-nowrap hover-scroll-x gap-6 me-4 mb-8 mb-sm-0 h-45px">
                    <li class="nav-item">
                        <a class="tab text-nowrap fs-4 active" data-bs-toggle="tab" href="#kt_project_health_pane">
                            Health Information
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="tab text-nowrap fs-4" data-bs-toggle="tab" href="#kt_project_aktivitas_pane">
                            Riwayat Aktivitas
                        </a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="tab text-nowrap fs-4" data-bs-toggle="tab" href="#kt_project_pembelian_pane">
                            Riwayat Pembelian
                        </a>
                    </li> --}}
                </ul>
                <div class="tab-content mt-8">
                    <div id="kt_project_health_pane" class="tab-pane show active fade">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap gap-3 mb-6 justify-content-between align-items-start">
                                    <div>
                                        <h1 class="fs-4">Health Information</h1>
                                        <p class="text-grey2">Statistik perkembangan kesehatan member</p>
                                    </div>
                                    <label for="dateRange"
                                        class="btn btn-sm btn-light text-dark fw-600 d-flex align-items-center px-4">
                                        <input placeholder="Pick date rage"
                                            class="bg-transparent text-dark fw-600 cursor-pointer" id="dateRange" />
                                        <i class="ki-duotone ki-calendar fs-1 ms-1 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                            <span class="path6"></span>
                                        </i>
                                    </label>
                                    {{-- <div
                                        class="d-flex input-wrap align-items-center position-relative my-1 w-150px">
                                        <img class="search" src="{{asset('assets/media/icons/Calendar2.svg')}}" alt="">
                                        <!--begin::Datepicker-->
                                        <input name="due_date" class="ps-4 form-control" placeholder="Pilih tanggal" />
                                        <!--end::Datepicker-->
                                    </div> --}}
                                    {{-- <div style="width: max-content" class="ms-auto my-4">
                                        <x-form.date-range-filter />
                                        <input type="text" id="start_date" hidden>
                                        <input type="text" id="end_date" hidden>
                                    </div> --}}
                                </div>
                                <div
                                    class="d-flex flex-column flex-column-reverse flex-sm-row justify-content-between flex-wrap align-items-start mt-2 gap-2">
                                    <div class="tab-content">
                                        <div id="kt_project_berat_pane" class="tab-pane show active fade">
                                            <div class="d-flex align-items-center gap-1">
                                                <h1 class="fs-big">{{ $user->weight ?? '' }} kg</h1>
                                                <img src="{{asset('assets/media/icons/TrendUp.svg')}}" alt="">
                                            </div>
                                            <p class="text-grey2">Berat Badan (BB)</p>
                                        </div>
                                        <div id="kt_project_tinggi_pane" class="tab-pane fade">
                                            <div class="d-flex align-items-center gap-1">
                                                <h1 class="fs-big">{{ $user->height ?? '' }} cm</h1>
                                            </div>
                                            <p class="text-grey2">Tinggi Badan</p>
                                        </div>
                                        <div id="kt_project_muslce_pane" class="tab-pane fade">
                                            <div class="d-flex align-items-center gap-1">
                                                <h1 class="fs-big">{{ $user->muscle_mass ?? 0 }} kg</h1>
                                            </div>
                                            <p class="text-grey2">Muscle Mass</p>
                                        </div>
                                    </div>
                                    <ul
                                        class="nav nav-pills wrap-button d-flex flex-nowrap justify-content-between align-items-center mb-2 mb-sm-0 px-1 py-1">
                                        <li class="nav-item my-1 type_information" data-type="weight">
                                            <a class="btn-gray text-nowrap active" data-bs-toggle="tab"
                                                href="#kt_project_berat_pane">
                                                Berat Badan
                                            </a>
                                        </li>
                                        <li class="nav-item my-1 type_information" data-type="height">
                                            <a class="btn-gray text-nowrap" data-bs-toggle="tab"
                                                href="#kt_project_tinggi_pane">
                                                Tinggi Badan
                                            </a>
                                        </li>
                                        <li class="nav-item my-1 type_information" data-type="muscle_mass">
                                            <a class="btn-gray text-nowrap" data-bs-toggle="tab"
                                                href="#kt_project_muslce_pane">
                                                Muscle Mass
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                                <div id="kt_charts_health" style="height: 350px"></div>
                            </div>
                        </div>
                        <div class="card mt-6">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h1 class="fs-4">Riwayat Health Information</h1>
                                    <button data-bs-toggle="modal" data-bs-target="#kt_modal_create_app"
                                        class="btn btn-primary fs-7 d-flex gap-2 align-items-center fw-400 border-radius-xxxl">
                                        <i class="fa fa-plus fw-400"></i>
                                        Tambah
                                    </button>
                                </div>
                                <!--begin::Table container-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <x-alert.alert-validation />
                                    <table class="table align-middle table-row-dashed fs-6 gy-5"
                                        id="table-health-information">
                                        <thead>
                                            <tr class="text-start text-grey fw-bold fs-7 text-uppercase gs-0">
                                                <th class="min-w-200px">HARI/TANGGAL</th>
                                                <th class="min-w-125px fw-500" id="nama_riwayat">TOTAL BERAT BADAN</th>
                                                <th class="min-w-100px text-center">PERUBAHAN</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">

                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->
                            </div>
                        </div>
                    </div>
                    <div id="kt_project_aktivitas_pane" class="tab-pane fade">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex flex-wrap justify-content-between align-items-start mb-4 gap-4">
                                    <div>
                                        <h1 class="fs-4">Riwayat Aktivitas</h1>
                                        <p class="text-grey2 mb-0">Ini adalah jadwal client personal trainer kamu</p>
                                    </div>
                                    <a href="#" class="input-wrap px-4 py-2 fw-500 text-white2 border-radius-xxl">
                                        Lihat Semua
                                    </a>
                                </div>
                                <!--begin::Dates-->
                                <ul id="wrap_date" class="nav nav-pills d-flex flex-nowrap hover-scroll-x py-2 mb-3">

                                </ul>
                                <!--end::Dates-->
                                <div id="list-schedules">
                                </div>
                                {{-- <div data-bs-toggle="modal" data-bs-target="#kt_modal_detail_class"
                                    class="bg-dark cursor-pointer purple card p-6 mb-6">
                                    <div class="d-flex justify-content-between align-items-cente mb-3">
                                        <div class="d-flex align-items-center gap-4">
                                            <div
                                                class="type d-flex justify-content-center align-items-center bg-purple100 border-radius-xxxl p-1">
                                                <img src="{{asset('assets/media/icons/PersonSimpleRun.svg')}}" alt="">
                                            </div>
                                            <p class="mb-0 fw-500">Kelas</p>
                                        </div>
                                        <div class="bg-orange fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                            Dalam
                                            Proses</div>
                                    </div>
                                    <p class="text-grey2 fw-400 mb-1 fs-6">16.00 - 17.00</p>
                                    <h1 class="fs-6">Session with Logan Weaver</h1>
                                </div> --}}
                                {{-- <div data-bs-toggle="modal" data-bs-target="#kt_modal_detail_activity_pt"
                                    class="bg-dark cursor-pointer green card p-6 mb-6">
                                    <div class="d-flex justify-content-between align-items-cente mb-3">
                                        <div class="d-flex align-items-center gap-4">
                                            <div
                                                class="type d-flex justify-content-center align-items-center bg-green100 border-radius-xxxl p-1">
                                                <img src="{{asset('assets/media/icons/PersonArmsSpread.svg')}}" alt="">
                                            </div>
                                            <p class="mb-0 fw-500">Personal Trainer</p>
                                        </div>
                                        <div class="bg-green fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                            Selesai
                                        </div>
                                    </div>
                                    <p class="text-grey2 fw-400 mb-1 fs-6">07.00 - 08.00</p>
                                    <h1 class="fs-6">Session with Logan Weaver</h1>
                                </div>
                                <div data-bs-toggle="modal" data-bs-target="#kt_modal_detail_activity_pt"
                                    class="bg-dark cursor-pointer green card p-6 mb-6">
                                    <div class="d-flex justify-content-between align-items-cente mb-3">
                                        <div class="d-flex align-items-center gap-4">
                                            <div
                                                class="type d-flex justify-content-center align-items-center bg-green100 border-radius-xxxl p-1">
                                                <img src="{{asset('assets/media/icons/PersonArmsSpread.svg')}}" alt="">
                                            </div>
                                            <p class="mb-0 fw-500">Personal Trainer</p>
                                        </div>
                                        <div class="bg-red fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">Tidak
                                            Hadir</div>
                                    </div>
                                    <p class="text-grey2 fw-400 mb-1 fs-6">07.00 - 08.00</p>
                                    <h1 class="fs-6">Session with Logan Weaver</h1>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    {{-- <div id="kt_project_pembelian_pane" class="tab-pane fade">
                        <div class="d-flex justify-content-start">
                            <ul
                                class="nav nav-pills wrap-button d-flex justify-content-between align-items-center mb-2 mb-sm-0 px-1 py-1">
                                <li class="nav-item my-1">
                                    <a class="btn-gray active" data-bs-toggle="tab" href="#kt_project_all_pane">
                                        Semua
                                    </a>
                                </li>
                                <li class="nav-item my-1">
                                    <a class="btn-gray" data-bs-toggle="tab" href="#kt_project_active_pane">
                                        Aktif
                                    </a>
                                </li>
                                <li class="nav-item my-1">
                                    <a class="btn-gray" data-bs-toggle="tab" href="#kt_project_finish_pane">
                                        Selesai
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="wrap-card mt-8 pe-lg-4">
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-12 mb-6 ">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center gap-4 mb-4">
                                                <h1 class="fs-3 fw-600 mb-0">Detail Order</h1>
                                                <button class="btn-status fw-bold bg-gray text-green">Aktif</button>
                                            </div>
                                            <!--begin::Table container-->
                                            <div class="table-responsive">
                                                <!--begin::Table-->
                                                <table class="table align-middle table-row-dashed fs-6 gy-5"
                                                    id="kt_table_users">
                                                    <tbody class="text-gray-600 fw-semibold">
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/PersonArmsSpread2.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Jenis Paket
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <p class="fw-600 mb-0">10 Sesi/3 Bulan</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/ClockCountdown.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Sisa Sesi</p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                <div
                                                                    class="bg-red fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                                                    2 Sesi</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/Clock.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Masa Berlaku
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                <div
                                                                    class="bg-blue fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                                                    Tersedia 14 Hari</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/Calendar2.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Tanggal
                                                                        Pembelian
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                10 Januari 2024
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!--end::Table-->
                                            </div>
                                            <!--end::Table container-->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-12 mb-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center gap-4 mb-4">
                                                <h1 class="fs-3 fw-600 mb-0">Detail Order</h1>
                                                <button class="btn-status fw-bold bg-gray text-blue">Selesai</button>
                                            </div>
                                            <!--begin::Table container-->
                                            <div class="table-responsive">
                                                <!--begin::Table-->
                                                <table class="table align-middle table-row-dashed fs-6 gy-5"
                                                    id="kt_table_users">
                                                    <tbody class="text-gray-600 fw-semibold">
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/PersonArmsSpread2.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Jenis Paket
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <p class="fw-600 mb-0">10 Sesi/3 Bulan</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/ClockCountdown.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Sisa Sesi</p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                <div
                                                                    class="bg-red fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                                                    2 Sesi</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/Clock.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Masa Berlaku
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                <div
                                                                    class="bg-blue fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                                                    Tersedia 14 Hari</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/Calendar2.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Tanggal
                                                                        Pembelian
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                10 Januari 2024
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!--end::Table-->
                                            </div>
                                            <!--end::Table container-->
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 col-lg-12 mb-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center gap-4 mb-4">
                                                <h1 class="fs-3 fw-600 mb-0">Detail Order</h1>
                                                <button class="btn-status fw-bold bg-gray text-green">Aktif</button>
                                            </div>
                                            <!--begin::Table container-->
                                            <div class="table-responsive">
                                                <!--begin::Table-->
                                                <table class="table align-middle table-row-dashed fs-6 gy-5"
                                                    id="kt_table_users">
                                                    <tbody class="text-gray-600 fw-semibold">
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/PersonArmsSpread2.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Jenis Paket
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="text-end">
                                                                <p class="fw-600 mb-0">10 Sesi/3 Bulan</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/ClockCountdown.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Sisa Sesi</p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                <div
                                                                    class="bg-red fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                                                    2 Sesi</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/Clock.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Masa Berlaku
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                <div
                                                                    class="bg-blue fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                                                    Tersedia 14 Hari</div>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-nowrap">
                                                                <div class="d-flex gap-2 align-items-center">
                                                                    <img src="{{asset('assets/media/icons/Calendar2.svg')}}"
                                                                        alt="">
                                                                    <p class="text-grey2 fw-400 mb-0">Tanggal
                                                                        Pembelian
                                                                    </p>
                                                                </div>
                                                            </td>
                                                            <td class="d-flex justify-content-end">
                                                                10 Januari 2024
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!--end::Table-->
                                            </div>
                                            <!--end::Table container-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="kt_modal_create_app" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal header-->
                <div class="modal-header py-4 pe-4 ps-6">
                    <!--begin::Modal title-->
                    <h1 class="fs-4 mb-0" id="title_modal">Tambah Data</h1>
                    <!--end::Modal title-->
                    <!--begin::Close-->
                    <div class="cursor-pointer btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <img src="{{asset('assets/media/icons/close.svg')}}" alt="">
                    </div>
                    <!--end::Close-->
                </div>
                <!--end::Modal header-->
                <!--begin::Modal body-->
                <div class="modal-body px-12">
                    <form action="{{ route('personal-trainer.membership.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <label>Hari/Tanggal</label>
                        <div class="d-flex input-wrap align-items-center position-relative my-2">
                            <img class="search" src="{{asset('assets/media/icons/Calendar.svg')}}" alt="">
                            <input required class="ps-4 form-control" placeholder="Pilih tanggal" name="select_date"
                                id="select_date" />
                        </div>
                        <label class="mt-4">Berat Badan</label>
                        <div class="input-group input-wrap my-2">
                            <input type="number" name="weight" class="form-control" placeholder="Masukkan berat badan">
                            <span class="input-group-text">kg</span>
                        </div>
                        <label class="mt-4">Tinggi Badan</label>
                        <div class="input-group input-wrap my-2">
                            <input type="number" name="height" class="form-control" placeholder="Masukkan tinggi badan">
                            <span class="input-group-text">cm</span>
                        </div>
                        <label class="mt-4">Muscle Mass</label>
                        <div class="input-group input-wrap my-2">
                            <input type="number" name="muscle_mass" class="form-control"
                                placeholder="Masukkan muscle mass">
                            <span class="input-group-text">kg</span>
                        </div>
                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                        <div class="d-flex gap-2 justify-content-end mt-8">
                            <button data-bs-dismiss="modal" type="button" data-bs-dismiss="modal"
                                class="btn bg-gray border-radius-xxxl d-flex align-items-center gap-2 py-2 px-4 fw-normal fs-6">
                                <span>Batal</span>
                            </button>
                            <button type="submit"
                                class="btn btn-primary border-radius-xxxl d-flex align-items-center gap-2 py-2 px-4 fw-normal fs-6">
                                Tambah
                            </button>
                        </div>
                    </form>
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <div class="modal fade" id="kt_modal_detail_activity_pt" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal body-->
                <div class="modal-body px-8">
                    <div class="d-flex justify-content-end">
                        <!--begin::Close-->
                        <div class="cursor-pointer btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <img src="{{asset('assets/media/icons/close.svg')}}" alt="">
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--begin::Row-->
                    <div class="d-flex">
                        <div class="mb-4">
                            <!--begin::Event name-->
                            <div class="d-flex align-items-center mb-2">
                                <span class="fs-4 fw-bold me-3">Session With Trainer</span>
                                <span class="status green">PERSONAL TRAINER</span>
                            </div>
                            <!--end::Event name-->
                        </div>
                    </div>
                    <div class="d-flex gap-4 align-items-center mb-2">
                        <!--begin::Bullet-->
                        <img src="{{asset('assets/media/icons/calendar1.svg')}}" alt="">
                        <!--end::Bullet-->
                        <!--begin::Event start date/time-->
                        <div class="fs-6">
                            <div class="d-flex align-items-center gap-2">
                                <span>Senin, 20 Oktober 2023</span>
                                <span class="mb-2">.</span>
                                <span>18:00 - 19:00</span>
                            </div>
                        </div>
                        <!--end::Event start date/time-->
                    </div>
                    <div class="d-flex gap-4 align-items-center mb-2">
                        <!--begin::Bullet-->
                        <img src="{{asset('assets/media/icons/Info.svg')}}" alt="">
                        <!--end::Bullet-->
                        <!--begin::Event start date/time-->
                        <div class="bg-green fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">Selesai</div>
                        <!--end::Event start date/time-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <div class="modal fade" id="kt_modal_detail_class" tabindex="-1" aria-hidden="true">
        <!--begin::Modal dialog-->
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <!--begin::Modal content-->
            <div class="modal-content">
                <!--begin::Modal body-->
                <div class="modal-body px-8">
                    <div class="d-flex justify-content-end">
                        <!--begin::Close-->
                        <div class="cursor-pointer btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                            <img src="{{asset('assets/media/icons/close.svg')}}" alt="">
                        </div>
                        <!--end::Close-->
                    </div>
                    <!--begin::Row-->
                    <div class="d-flex">
                        <div class="mb-4">
                            <!--begin::Event name-->
                            <div class="d-flex align-items-center mb-2">
                                <span class="fs-4 fw-bold me-3">Yoga Swing</span>
                                <span class="status violet">KELAS</span>
                            </div>
                            <!--end::Event name-->
                        </div>
                    </div>
                    <div class="d-flex gap-4 align-items-center mb-2">
                        <!--begin::Bullet-->
                        <img src="{{asset('assets/media/icons/calendar1.svg')}}" alt="">
                        <!--end::Bullet-->
                        <!--begin::Event start date/time-->
                        <div class="fs-6">
                            <div class="d-flex align-items-center gap-2">
                                <span>Senin, 20 Oktober 2023</span>
                                <span class="mb-2">.</span>
                                <span>18:00 - 19:00</span>
                            </div>
                        </div>
                        <!--end::Event start date/time-->
                    </div>
                    <div class="d-flex gap-4 align-items-center mb-2">
                        <!--begin::Bullet-->
                        <img src="{{asset('assets/media/icons/Info.svg')}}" alt="">
                        <!--end::Bullet-->
                        <!--begin::Event start date/time-->
                        <div class="bg-orange fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">Dalam Proses</div>
                        <!--end::Event start date/time-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Modal body-->
            </div>
            <!--end::Modal content-->
        </div>
        <!--end::Modal dialog-->
    </div>
    <!--end::Container-->
    @endsection
    @push('js')
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
    <script src="{{asset('assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
    <script src="{{asset('assets/js/custom/apps/projects/list/list.js')}}"></script>
    <script src="{{asset('assets/js/custom/apps/projects/users/users.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"
        integrity="sha512-he8U4ic6kf3kustvJfiERUpojM8barHoz0WYpAUDWQVn61efpm3aVAD8RWL8OloaDDzMZ1gZiubF9OSdYBqHfQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    {{-- <script src="{{asset('assets/js/widgets.bundle.js')}}"></script> --}}
    {{-- <script src="{{asset('assets/js/custom/widgets.js')}}"></script> --}}
    <script>
        // date range picker
        $(function() {
            var start = moment().startOf('year');
            var end = moment().endOf('year');
            function cb(start, end) {
                $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
                var start = start.format('YYYY-MM-DD');
                var end = end.format('YYYY-MM-DD');
                $('#start_date').val(start);
                $('#end_date').val(end);
            }

            $('#dateRange').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Semua Waktu': [moment().subtract(5, 'years'), moment()],
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                    'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
                }
            }, cb);
            cb(start, end);
        });

        flatpickr(document.querySelector('#select_date'), {
                enableTime: false,
                dateFormat: 'd/m/Y',
            });



        var historyHealth = @json($historyHealth); 
        document.addEventListener('DOMContentLoaded', function () {
            // type health information
            let type = 'Berat Badan'
            let unit = 'kg'
            var e = document.getElementById("kt_charts_health");

            const health_information = [
                {
                    type: 'weight',
                    name: 'Berat Badan',
                    table: 'TOTAL BERAT BADAN',
                    name_unit: 'Berat Badan (kg)',
                    unit: 'kg'
                },
                {
                    type: 'height',
                    name: 'Tinggi Badan',
                    table: 'TOTAL TINGGI BADAN',
                    name_unit: 'Tinggi Badan (cm)',
                    unit: 'cm'
                },
                {
                    type: 'muscle_mass',
                    name: 'Muscle Mass',
                    table: 'TOTAL MUSCLE MASS',
                    name_unit: 'Muscle Mass (kg)',
                    unit: 'kg'
                }
            ];

            // Chart configuration
            let t, a, s;

            // Event listener for type change
            $(document).on('click', '.type_information', function() {
                const clickType = $(this).data('type');
                const data = health_information.find(item => item.type === clickType);

                // update chart
                t.self.updateSeries([{
                    name: data.name,
                    data: historyHealth.map(item=>item[data.type])
                }])

                t.self.updateOptions({
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: function (val) {
                                // Assuming 'unit' is a variable containing the unit information
                                return `${val} ${data.unit}`;
                            }
                        }
                    }
                });

                // update table health information
                $('#nama_riwayat').text(`TOTAL ${data.table}`)
                initDataTable(data.type)
            })

            let table = null
            // Function to initialize DataTable
            function initDataTable(type) {
                if (table !== null) {
                    table.destroy();
                }

                let tableData = []

                table = $('#table-health-information').DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    ajax: {
                        url: "{{ route('personal-trainer.membership.show', $user->id) }}",
                        type: 'GET',
                        data: {
                            type: type // Send the selected type as a parameter
                        },
                        complete:function(response){
                            tableData = response.responseJSON.data
                            console.log(tableData);
                        }
                    },
                    language: {
                        "paginate": {
                            "next": "<i class='fa fa-angle-right'>",
                            "previous": "<i class='fa fa-angle-left'>"
                        },
                        "loadingRecords": "Loading...",
                        "processing": "Processing...",
                    },
                    columns: [
                        {
                            data: 'date',
                            name: 'date',
                            responsivePriority: -2
                        },
                        {
                            data: 'item',
                            name: 'item',
                            responsivePriority: -1
                        },
                        {
                            data: 'change',
                            name: 'change',
                            responsivePriority: -1
                        }
                    ]
                });
            }

            // Initialize DataTable with default type
            initDataTable('weight');

            const itemDate = (date) => {
                const dDay = moment().format('DD-MM-YYYY')
                const data = date.split('|')
                const id = data[1]
                const item = data[0].split(' ')
                const isActive = dDay === id ? 'active' : ''

                return `<li class="nav-item me-1 date-item" data-date="${id}">
                            <a class="nav-link btn d-flex flex-column flex-center rounded-pill min-w-45px me-2 py-4 px-3 btn-active-primary ${isActive}" data-bs-toggle="tab" href="#kt_schedule_day_0">
                                <span class="day fs-6 fw-400">${item[0]}</span>
                                <span class="fs-3 fw-bold">${item[1]}</span>
                            </a>
                        </li>`
            }

            // Function to generate date list
            function generateDateList() {
                var dateList = [];
                var currentDate = moment();

                for (var i = 0; i < 8; i++) {
                    dateList.push(itemDate(currentDate.format('ddd DD|DD-MM-YYYY')));
                    currentDate.add(1, 'day');
                }

                $('#wrap_date').append(dateList);
            }

            // Generate date list on DOMContentLoaded
            generateDateList();

            

            if (e) {
                t = { self: null, rendered: false }
                a = function () {
                     // update chart
                (t.rendered = true);
                    parseInt(KTUtil.css(e, "height"));
                    var a = KTUtil.getCssVariableValue("--bs-gray-500"),
                        o = KTUtil.getCssVariableValue("--bs-gray-200"),
                        fillColor = KTThemeMode.getMode() === "dark" ? '#1C1C1C' : '#FFFFFF',
                        r = '#B18D41'
                        s = {
                            series: [
                                {
                                    name: type,
                                    data: historyHealth.map(item => item.weight),
                                },
                            ],
                            chart: {
                                fontFamily: "inherit",
                                type: "area",
                                height: 350,
                                toolbar: { show: false },
                                zoom: {
                                    enabled: false,
                                }
                            },
                            plotOptions: {},
                            legend: { show: false },
                            dataLabels: { enabled: false },
                            fill: {
                                type: "gradient",
                                gradient: {
                                    shadeIntensity: 1,
                                    opacityFrom: 0.5,
                                    opacityTo: 0.5,
                                    stops: [0, 90, 100],
                                    colorStops: [
                                        {
                                            offset: 0,
                                            color: "#B18D41", // Start color,
                                            opacity: 0.4
                                        },
                                        {
                                            offset: 100,
                                            color: fillColor, // End color (black)
                                            opacity: 0.5
                                        }
                                    ]
                                },
                            },
                            stroke: {
                                curve: "smooth",
                                show: true,
                                width: 3,
                                colors: [r],
                            },
                            xaxis: {
                                categories: historyHealth.map(item => item.date),
                                axisBorder: { show: false },
                                axisTicks: { show: false },
                                labels: {
                                    style: {
                                        colors: a,
                                        fontSize: "12px",
                                    },
                                },
                                crosshairs: {
                                    position: "front",
                                    stroke: {
                                        color: r,
                                        width: 1,
                                        dashArray: 3,
                                    },
                                    xaxis: {
                                        categories: historyHealth.map(item => item.date),
                                        axisBorder: { show: !1 },
                                        axisTicks: { show: !1 },
                                        labels: {
                                            style: {
                                                colors: a,
                                                fontSize: "12px",
                                            },
                                        },
                                        crosshairs: {
                                            position: "front",
                                            stroke: {
                                                color: r,
                                                width: 1,
                                                dashArray: 3,
                                            },
                                        },
                                        
                                    },
                                },
                            },
                            states: {
                                normal: {
                                    filter: { type: "none", value: 0 },
                                },
                                hover: {
                                    filter: { type: "none", value: 0 },
                                },
                                active: {
                                    allowMultipleDataPointsSelection: false,
                                    filter: { type: "none", value: 0 },
                                },
                            },
                            tooltip: {
                                enabled: true,
                                y: {
                                    formatter: function (val) {
                                        return `${val} ${unit}`
                                    }
                                }
                            },
                            colors: [r],
                            grid: {
                                borderColor: o,
                                strokeDashArray: 4,
                                yaxis: { lines: { show: true } },
                            },
                            markers: { strokeColor: r, strokeWidth: 3 },
                        };

                    (t.self = new ApexCharts(e, s)),
                        t.self.render(),
                        (t.rendered = true);
                };
                a(),

                // Date riwayat aktivitas
                function generateElement() {
                    var schedule = @json($schedules);

                    var listSchedules = '';
                    schedule.forEach(item => {
                        console.log(item);
                        if (item.type == 'Kelas' && item.gym_class !== null) {
                            listSchedules += `<div data-bs-toggle="modal" data-bs-target="#kt_modal_detail_activity_pt"
                                class="bg-dark cursor-pointer green card p-6 mb-6">
                                <div class="d-flex justify-content-between align-items-cente mb-3">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="type d-flex justify-content-center align-items-center bg-green100 border-radius-xxxl p-1">
                                            <img src="{{asset('assets/media/icons/PersonArmsSpread.svg')}}" alt="">
                                        </div>
                                        <p class="mb-0 fw-500">`+ item.type +`</p>
                                    </div>
                                    <div class="bg-green fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                        Selesai
                                    </div>
                                </div>
                                <p class="text-grey2 fw-400 mb-1 fs-6">${item.gym_class.start_time + ' - ' +
                                item.gym_class.end_time}</p>
                                <h1 class="fs-6">Session with ${item.gym_class.name}</h1>
                            </div>`;
                        } else if (item.type == 'Personal Trainer' && item.personal_trainer_schedule !== null) {
                            listSchedules += `<div data-bs-toggle="modal" data-bs-target="#kt_modal_detail_activity_pt"
                                class="bg-dark cursor-pointer green card p-6 mb-6">
                                <div class="d-flex justify-content-between align-items-cente mb-3">
                                    <div class="d-flex align-items-center gap-4">
                                        <div class="type d-flex justify-content-center align-items-center bg-green100 border-radius-xxxl p-1">
                                            <img src="{{asset('assets/media/icons/PersonArmsSpread.svg')}}" alt="">
                                        </div>
                                        <p class="mb-0 fw-500">`+ item.type +`</p>
                                    </div>
                                    <div class="bg-green fs-7 px-4 py-2 fw-500 text-white border-radius-xxl">
                                        Selesai
                                    </div>
                                </div>
                            <p class="text-grey2 fw-400 mb-1 fs-6">${moment(item.personal_trainer_schedule.start_time, 'HH:mm:ss').format('HH:mm')}
                                - ${moment(item.personal_trainer_schedule.end_time, 'HH:mm:ss').format('HH:mm')}</p>
                                <h1 class="fs-6">Session with ${item.name}</h1>
                            </div>`;
                        }
                    });
                    $('#list-schedules').append(listSchedules);
                    generateElement()
            }
            // Example usage
        }
        // select date
        $(document).on('click', '.date-item', function() {
            console.log($(this).data('date'))
        })
    })
    </script>
    @endpush