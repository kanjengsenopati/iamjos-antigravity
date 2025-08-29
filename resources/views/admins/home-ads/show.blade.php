@extends('layouts.master', ['title' => 'Detail Statistik Iklan', 'main' => 'Dashboard'])
@section('content')
    <div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">

                    <!--begin::Navbar-->
                    <div class="card mb-5 mb-xxl-8">
                        <div class="card-body pt-9 pb-0">
                            <!--begin::Details-->
                            <div class="d-flex flex-wrap flex-sm-nowrap">
                                <!--begin::Image-->
                                <div class="me-7 mb-4">
                                    <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                        @if ($homeAds->media_type === 'video')
                                            <video class="w-100 h-100 rounded" controls>
                                                <source src="{{ asset($homeAds->media_url) }}" type="video/mp4">
                                                Video tidak didukung
                                            </video>
                                        @else
                                            <img src="{{ asset($homeAds->media_url) }}" alt="Iklan"
                                                class="w-100 h-100 rounded object-cover" />
                                        @endif
                                        <div
                                            class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px">
                                            @if ($statistics['is_currently_active'])
                                                <span
                                                    class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-success rounded-circle border border-4 border-body h-20px w-20px"></span>
                                            @else
                                                <span
                                                    class="position-absolute translate-middle bottom-0 start-100 mb-6 bg-danger rounded-circle border border-4 border-body h-20px w-20px"></span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <!--end::Image-->

                                <!--begin::Info-->
                                <div class="flex-grow-1">
                                    <!--begin::Title-->
                                    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                        <div class="d-flex flex-column">
                                            <div class="d-flex align-items-center mb-2">
                                                <span class="text-gray-900 text-hover-primary fs-2 fw-bold me-1">
                                                    Detail Statistik Iklan
                                                </span>
                                                <span
                                                    class="badge badge-light-{{ $statistics['is_currently_active'] ? 'success' : 'danger' }} fs-8 fw-semibold ms-2">
                                                    {{ $statistics['is_currently_active'] ? 'Aktif' : 'Tidak Aktif' }}
                                                </span>
                                            </div>

                                            <div class="d-flex flex-wrap fw-semibold fs-6 mb-4 pe-2">
                                                <span
                                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                    <i class="ki-duotone ki-profile-circle fs-4 me-1">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    Tipe Media: {{ ucfirst($homeAds->media_type) }}
                                                </span>

                                                <span
                                                    class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                    <i class="ki-duotone ki-geolocation fs-4 me-1">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Urutan: {{ $homeAds->order }}
                                                </span>

                                                @if ($homeAds->link)
                                                    <span
                                                        class="d-flex align-items-center text-gray-400 text-hover-primary mb-2">
                                                        <i class="ki-duotone ki-sms fs-4 me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        <a href="{{ $homeAds->link }}" target="_blank"
                                                            class="text-primary">{{ Str::limit($homeAds->link, 50) }}</a>
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="d-flex my-4">
                                            <a href="{{ route('home-ads.edit', $homeAds->id) }}"
                                                class="btn btn-sm btn-primary me-3">Edit Iklan</a>
                                            <a href="{{ route('home-ads.index') }}" class="btn btn-sm btn-light">Kembali</a>
                                        </div>
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Info-->
                            </div>
                            <!--end::Details-->

                            <!--begin::Navs-->
                            <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                                <li class="nav-item mt-2">
                                    <a class="nav-link text-active-primary ms-0 me-10 py-5 active" href="#statistik">
                                        Statistik & Analytics
                                    </a>
                                </li>
                            </ul>
                            <!--end::Navs-->
                        </div>
                    </div>
                    <!--end::Navbar-->

                    <!--begin::Row-->
                    <div class="row g-5 g-xxl-8">
                        <!--begin::Col-->
                        <div class="col-xxl-6">
                            <!--begin::Engage widget 10-->
                            <div class="card card-flush h-md-50 mb-5 mb-xxl-8">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <!--begin::Title-->
                                    <div class="card-title d-flex flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Overview</span>
                                        <span class="text-muted fw-semibold fs-7">Ringkasan performa iklan</span>
                                    </div>
                                    <!--end::Title-->
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body d-flex flex-column justify-content-end pe-0">
                                    <!--begin::Stats-->
                                    <div class="row g-0">
                                        <div class="col bg-light-warning px-6 py-8 rounded-2 me-7 mb-7">
                                            <i class="ki-duotone ki-eye fs-3x text-warning d-block my-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            <span class="text-warning fw-semibold fs-6">Total Views</span>
                                            <span
                                                class="text-warning fw-bold fs-2 d-block">{{ number_format($statistics['total_views']) }}</span>
                                        </div>

                                        <div class="col bg-light-primary px-6 py-8 rounded-2 mb-7">
                                            <i class="ki-duotone ki-click fs-3x text-primary d-block my-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <span class="text-primary fw-semibold fs-6">Total Clicks</span>
                                            <span
                                                class="text-primary fw-bold fs-2 d-block">{{ number_format($statistics['total_clicks']) }}</span>
                                        </div>
                                    </div>

                                    <div class="row g-0">
                                        <div class="col bg-light-info px-6 py-8 rounded-2 me-7">
                                            <i class="ki-duotone ki-chart-simple fs-3x text-info d-block my-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                            </i>
                                            <span class="text-info fw-semibold fs-6">CTR</span>
                                            <span
                                                class="text-info fw-bold fs-2 d-block">{{ $statistics['ctr_percentage'] }}%</span>
                                        </div>

                                        <div class="col bg-light-success px-6 py-8 rounded-2">
                                            <i class="ki-duotone ki-calendar fs-3x text-success d-block my-2">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            <span class="text-success fw-semibold fs-6">Progress</span>
                                            <span
                                                class="text-success fw-bold fs-2 d-block">{{ $statistics['campaign_progress'] }}%</span>
                                        </div>
                                    </div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Engage widget 10-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-xxl-6">
                            <!--begin::Timeline-->
                            <div class="card card-flush h-md-50 mb-5 mb-xxl-8">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Periode Kampanye</span>
                                        <span class="text-muted fw-semibold fs-7">Timeline dan durasi iklan</span>
                                    </h3>
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body pt-5">
                                    <!--begin::Timeline-->
                                    <div class="timeline-label">
                                        <!--begin::Item-->
                                        <div class="timeline-item">
                                            <div class="timeline-label fw-bold text-gray-800 fs-6">
                                                Mulai
                                            </div>
                                            <div class="timeline-badge">
                                                <i class="ki-duotone ki-abstract-8 text-success fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <div class="fw-muted text-muted ps-3">
                                                {{ $homeAds->start_date
                                                    ? \Carbon\Carbon::parse($homeAds->start_date)->locale('id')->isoFormat('dddd, D MMMM YYYY')
                                                    : 'Tidak ditentukan' }}
                                            </div>

                                        </div>
                                        <!--end::Item-->

                                        <!--begin::Item-->
                                        <div class="timeline-item">
                                            <div class="timeline-label fw-bold text-gray-800 fs-6">
                                                Berakhir
                                            </div>
                                            <div class="timeline-badge">
                                                <i class="ki-duotone ki-abstract-8 text-danger fs-1">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </div>
                                            <div class="fw-muted text-muted ps-3">
                                                {{ $homeAds->start_date
                                                    ? \Carbon\Carbon::parse($homeAds->start_date)->locale('id')->isoFormat('dddd, D MMMM YYYY')
                                                    : 'Tidak ditentukan' }}
                                            </div>

                                        </div>
                                        <!--end::Item-->
                                    </div>
                                    <!--end::Timeline-->

                                    <!--begin::Stats-->
                                    <div class="d-flex flex-stack mt-8">
                                        <div class="me-5">
                                            <div class="fs-6 text-gray-700">Total Hari Kampanye</div>
                                            <div class="fs-2 fw-bold text-gray-800">
                                                {{ $statistics['total_campaign_days'] }} hari</div>
                                        </div>
                                        <div class="me-5">
                                            <div class="fs-6 text-gray-700">Hari Berlalu</div>
                                            <div class="fs-2 fw-bold text-gray-800">{{ $statistics['days_elapsed'] }} hari
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fs-6 text-gray-700">Sisa Hari</div>
                                            <div
                                                class="fs-2 fw-bold text-{{ $statistics['days_remaining'] < 0 ? 'danger' : 'primary' }}">
                                                {{ $statistics['days_remaining'] ?? 0 }} hari
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Stats-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Timeline-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->

                    <!--begin::Row-->
                    <div class="row g-5 g-xxl-8">
                        <!--begin::Col-->
                        <div class="col-xxl-4">
                            <!--begin::Performance-->
                            <div class="card card-flush h-xxl-100">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Performa Harian</span>
                                        <span class="text-muted fw-semibold fs-7">Rata-rata aktivitas per hari</span>
                                    </h3>
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body d-flex flex-column justify-content-between pt-5 pb-2">
                                    <!--begin::Item-->
                                    <div class="d-flex flex-stack">
                                        <div class="text-gray-700 fw-semibold fs-6 me-2">Views/Hari</div>
                                        <div class="d-flex align-items-senter">
                                            <span
                                                class="text-gray-900 fw-bolder fs-6">{{ number_format($statistics['avg_views_per_day'], 1) }}</span>
                                        </div>
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Separator-->
                                    <div class="separator separator-dashed my-3"></div>
                                    <!--end::Separator-->
                                    <!--begin::Item-->
                                    <div class="d-flex flex-stack">
                                        <div class="text-gray-700 fw-semibold fs-6 me-2">Clicks/Hari</div>
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="text-gray-900 fw-bolder fs-6">{{ number_format($statistics['avg_clicks_per_day'], 1) }}</span>
                                        </div>
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Performance-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-xxl-4">
                            <!--begin::Projections-->
                            <div class="card card-flush h-xxl-100">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Proyeksi Total</span>
                                        <span class="text-muted fw-semibold fs-7">Estimasi akhir kampanye</span>
                                    </h3>
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body d-flex flex-column justify-content-between pt-5 pb-2">
                                    <!--begin::Item-->
                                    <div class="d-flex flex-stack">
                                        <div class="text-gray-700 fw-semibold fs-6 me-2">Proyeksi Views</div>
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="text-gray-900 fw-bolder fs-6">{{ number_format($statistics['projected_total_views']) }}</span>
                                        </div>
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Separator-->
                                    <div class="separator separator-dashed my-3"></div>
                                    <!--end::Separator-->
                                    <!--begin::Item-->
                                    <div class="d-flex flex-stack">
                                        <div class="text-gray-700 fw-semibold fs-6 me-2">Proyeksi Clicks</div>
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="text-gray-900 fw-bolder fs-6">{{ number_format($statistics['projected_total_clicks']) }}</span>
                                        </div>
                                    </div>
                                    <!--end::Item-->
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Projections-->
                        </div>
                        <!--end::Col-->

                        <!--begin::Col-->
                        <div class="col-xxl-4">
                            <!--begin::Real-time-->
                            <div class="card card-flush h-xxl-100">
                                <!--begin::Header-->
                                <div class="card-header pt-5">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Data Real-time</span>
                                        <span class="text-muted fw-semibold fs-7">Pending di cache Redis</span>
                                    </h3>
                                </div>
                                <!--end::Header-->
                                <!--begin::Card body-->
                                <div class="card-body d-flex flex-column justify-content-between pt-5 pb-2">
                                    <!--begin::Item-->
                                    <div class="d-flex flex-stack">
                                        <div class="text-gray-700 fw-semibold fs-6 me-2">Pending Views</div>
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="text-warning fw-bolder fs-6">{{ number_format($statistics['pending_views']) }}</span>
                                        </div>
                                    </div>
                                    <!--end::Item-->
                                    <!--begin::Separator-->
                                    <div class="separator separator-dashed my-3"></div>
                                    <!--end::Separator-->
                                    <!--begin::Item-->
                                    <div class="d-flex flex-stack">
                                        <div class="text-gray-700 fw-semibold fs-6 me-2">Pending Clicks</div>
                                        <div class="d-flex align-items-center">
                                            <span
                                                class="text-warning fw-bolder fs-6">{{ number_format($statistics['pending_clicks']) }}</span>
                                        </div>
                                    </div>
                                    <!--end::Item-->

                                    @if ($statistics['pending_views'] > 0 || $statistics['pending_clicks'] > 0)
                                        <!--begin::Separator-->
                                        <div class="separator separator-dashed my-3"></div>
                                        <!--end::Separator-->
                                        <!--begin::Note-->
                                        <div
                                            class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-3">
                                            <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            <div class="d-flex flex-stack flex-grow-1">
                                                <div class="fw-semibold">
                                                    <div class="fs-6 text-gray-700">Data belum tersinkronisasi</div>
                                                    <div class="fs-7 text-gray-400">Akan otomatis tersinkron dalam beberapa
                                                        menit</div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--end::Note-->
                                    @endif
                                </div>
                                <!--end::Card body-->
                            </div>
                            <!--end::Real-time-->
                        </div>
                        <!--end::Col-->
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->
    </div>
@endsection
