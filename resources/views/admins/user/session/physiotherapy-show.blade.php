@extends('layouts.master', ['title' => 'Detail Sesi', 'main' => 'Sesi'])

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
    crossorigin="" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
    integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
    crossorigin=""></script>
@endpush

@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
            <x-alert.alert-validation />
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="d-flex gap-2 align-items-center mb-2">
                        <a href="{{ route('user.show', $physiotherapy_schedule_member->user_id) }}">
                            <span class="menu-icon back pt-1">
                                <i class="ki-duotone ki-arrow-left">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </a>
                        <h1 class="text-capitalize mb-0">Session With {{ $physiotherapy_schedule_member->employee?->name ?? '' }}</h1>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <div class="mt-6">
                <div class="card-body v2">
                    <!--begin::Content-->
                    <div id="kt_app_content" class="app-content flex-column-fluid">
                        <!--begin::Content container-->
                        <div id="kt_app_content_container" class="container-xxl">
                            <!--begin::Layout-->
                            <div class="d-flex flex-column flex-xl-row">
                                <!--begin::Sidebar-->
                                <div class="flex-column flex-lg-row-auto w-100 w-xl-350px mb-10">
                                    <!--begin::Card-->
                                    <div class="card pt-4 mb-5 mb-xl-8">
                                        <!--begin::Card body-->
                                        <div class="card-header border-0">
                                            <!--begin::Card title-->
                                            <div class="card-title">
                                                <h2>Informasi Customer</h2>
                                            </div>
                                            <!--end::Card title-->
                                        </div>
                                        <div class="card-body pt-10">
                                            <!--begin::Summary-->
                                            <div class="d-flex flex-center flex-column mb-5">
                                                <!--begin::Avatar-->
                                                <div class="symbol symbol-200px mb-7">
                                                    <img src="{{ asset($physiotherapy_schedule_member->employee?->avatar) }}" alt="image">
                                                </div>
                                                <!--end::Avatar-->
                                                <!--begin::Name-->
                                                <a href="{{ route('user.show', $physiotherapy_schedule_member->user_id) }}"
                                                    class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ $physiotherapy_schedule_member->employee?->name ?? '' }}</a>
                                                <!--end::Name-->
                                            </div>
                                            <!--end::Summary-->
                                        </div>
                                        <!--end::Card body-->
                                    </div>
                                    <!--end::Card-->
                                </div>
                                <!--end::Sidebar-->
                                <!--begin::Content-->
                                <div class="flex-lg-row-fluid ms-lg-15">
                                    <div class="card pt-4 mb-6 mb-xl-9">
                                        <!--begin::Card header-->
                                        <div class="card-header border-0">
                                            <!--begin::Card title-->
                                            <div class="card-title">
                                                <h2>Informasi Sesi</h2>
                                            </div>
                                            <!--end::Card title-->
                                        </div>
                                        <!--end::Card header-->
                                        <!--begin::Card body-->
                                        <div class="card-body pt-0 pb-5">
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="text-label text-muted">Tanggal</label>
                                                    <p class="text-label">{{ $physiotherapy_schedule_member->date ?? '' }}</p>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-label text-muted">Waktu</label>
                                                    <p class="text-label">
                                                        {{ ($physiotherapy_schedule_member->physiotherapy_schedule?->start_time ?? 'N/A') . ' - ' . 
                                                           ($physiotherapy_schedule_member->physiotherapy_schedule?->end_time ?? 'N/A') }}
                                                    </p>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-label text-muted">Durasi</label>
                                                    <p class="text-label">
                                                        @php
                                                            $startTime = $physiotherapy_schedule_member->physiotherapy_schedule?->start_time;
                                                            $endTime = $physiotherapy_schedule_member->physiotherapy_schedule?->end_time;
                                                            $duration = null;

                                                            if ($startTime && $endTime) {
                                                                $start = \Carbon\Carbon::parse($startTime);
                                                                $end = \Carbon\Carbon::parse($endTime);
                                                                $duration = $start->diffInMinutes($end);
                                                            }
                                                        @endphp
                                                        {{ $duration ?? 'N/A' }} Menit
                                                    </p>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-label text-muted">Catatan Sebelum Latihan</label>
                                                    <p class="text-label">{{ $physiotherapy_schedule_member->physiotherapy_schedule_member_notes?->where('type', 'NOTE')->first()?->note ?? 'N/A' }}</p>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="text-label text-muted">Catatan Setelah Latihan</label>
                                                    <p class="text-label">{{ $physiotherapy_schedule_member->physiotherapy_schedule_member_notes?->where('type', 'EVALUATION')->first()?->note ?? 'N/A' }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Card-->
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Layout-->
                        </div>
                        <!--end::Content container-->
                    </div>
                    <!--end::Content-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Wrapper-->
</div>
@endsection