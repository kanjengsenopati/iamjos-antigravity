@extends('layouts.master', ['title' => 'Gate Activity', 'main' => 'Dashboard'])
@push('css')
<style>
    .modal-cs-lg {
        --bs-modal-width: 940px;
    }
</style>
@endpush
@section('content')
<!--begin::Content-->
<div id="kt_app_content" class="app-content flex-column-fluid pt-6">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <div class="row g-5 g-xl-10">
            <div class="col-xl-4 mb-xl-10" style="max-height: 500px !important">
                <!--begin::Lists Widget 19-->
                <div class="card card-flush h-xl-100">
                    <!--begin::Heading-->
                    <div class="card-header rounded bgi-no-repeat bgi-size-cover bgi-position-y-top bgi-position-x-center align-items-start h-250px" style="background: url(assets/media/svg/shapes/top1.png), #B18D41; background-size: cover; background-position: bottom right" data-bs-theme="light">
                        <!--begin::Title-->
                        <h3 class="card-title align-items-start flex-column text-white pt-15">
                            <span class="fw-bold fs-2x mb-3">All Activity</span>
                            <div class="fs-4 text-white">
                                <span class="opacity-75">With</span>
                                {{-- <span class="position-relative d-inline-block">
                                    <a href="../../demo1/dist/pages/user-profile/projects.html" class="link-white opacity-75-hover fw-bold d-block mb-1">4 Status</a>
                                    <!--begin::Separator-->
                                    <span class="position-absolute opacity-50 bottom-0 start-0 border-2 border-body border-bottom w-100"></span>
                                    <!--end::Separator-->
                                </span> --}}
                                <span class="opacity-75">4 Status</span>
                            </div>
                        </h3>
                        <!--end::Title-->
                    </div>
                    <!--end::Heading-->
                    <!--begin::Body-->
                    <div class="card-body mt-n20">
                        <!--begin::Stats-->
                        <div class="mt-n20 position-relative">
                            <!--begin::Row-->
                            <div class="row g-3 g-lg-6">
                                <!--begin::Col-->
                                <div class="col-6">
                                    <!--begin::Items-->
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label">
                                                <i class="ki-duotone ki-entrance-left fs-1 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Stats-->
                                        <div class="m-0">
                                            <!--begin::Number-->
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1" id="total_checkin"></span>
                                            <!--end::Number-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-500 fw-semibold fs-6">Check In</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Items-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-6">
                                    <!--begin::Items-->
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label">
                                                <i class="ki-duotone ki-exit-left fs-1 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Stats-->
                                        <div class="m-0">
                                            <!--begin::Number-->
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1" id="total_checkout"></span>
                                            <!--end::Number-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-500 fw-semibold fs-6">Check Out</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Items-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-6">
                                    <!--begin::Items-->
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label">
                                                <i class="ki-duotone ki-chart fs-1 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Stats-->
                                        <div class="m-0">
                                            <!--begin::Number-->
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1" id="total_activity"></span>
                                            <!--end::Number-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-500 fw-semibold fs-6">Total Activity</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Items-->
                                </div>
                                <!--end::Col-->
                                <!--begin::Col-->
                                <div class="col-6">
                                    <!--begin::Items-->
                                    <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                        <!--begin::Symbol-->
                                        <div class="symbol symbol-30px me-5 mb-8">
                                            <span class="symbol-label">
                                                <i class="ki-duotone ki-shield-slash fs-1 text-primary">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                        </div>
                                        <!--end::Symbol-->
                                        <!--begin::Stats-->
                                        <div class="m-0">
                                            <!--begin::Number-->
                                            <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1" id="total_failed"></span>
                                            <!--end::Number-->
                                            <!--begin::Desc-->
                                            <span class="text-gray-500 fw-semibold fs-6">Total Failed</span>
                                            <!--end::Desc-->
                                        </div>
                                        <!--end::Stats-->
                                    </div>
                                    <!--end::Items-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <!--end::Row-->
                        </div>
                        <!--end::Stats-->
                    </div>
                    <!--end::Body-->
                </div>
                <!--end::Lists Widget 19-->
            </div>
            <div class="col-xl-8 mb-xl-10">
                <div class="card card-flush">
                    <div class="card-body pt-0">
                        <div class="mb-2 d-flex align-items-center flex-wrap justify-content-between gap-3 border-0 pt-6">
                            <!--begin::Card title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">List Gate Activity</span>
                            </h3>
                            <!--end::Card title-->
                            <div class="d-flex flex-wrap gap-3">
                                {{-- <div>
                                    <a type="button" class="btn btn-sm btn-primary text-nowrap"
                                        onclick="importCheckinCheckout()">
                                        <i class="ki-duotone ki-exit-down fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Export
                                    </a>
                                </div> --}}
                                <div>
                                    <select name="status" id="status" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Status Activity" data-kt-table-widget-4="filter_status">
                                        <option value=" ">Semua</option>
                                        <option value="IN">Check In</option>
                                        <option value="OUT">Check Out</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="dateRange"
                                        class="btn btn-sm btn-light text-dark fw-600 d-flex align-items-center px-4">
                                        <input placeholder="Pick date rage"
                                            class="bg-transparent text-dark fw-600 cursor-pointer" id="dateRange" />
                                        <i class="ki-duotone ki-calendar fs-1 ms-0 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                            <span class="path6"></span>
                                        </i>
                                    </label>
                                    <input type="text" id="start_date" hidden>
                                    <input type="text" id="end_date" hidden>
                                </div>
                            </div>
                        </div>
                        <!--begin::Table-->
                        <table id="datatable" class="table table-hover align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 2%">No</th>
                                    <th>Gate</th>
                                    <th style="width: 15%">Waktu</th>
                                    <th>Aktifitas</th>
                                    <th>Status Membership</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-60px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark fw-semibold"></tbody>
                        </table>
                        <!--end::Table-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Content container-->
</div>
<!--end::Content-->

@include('admins.gate-activity.modal')

@endsection
@include('admins.gate-activity.script')