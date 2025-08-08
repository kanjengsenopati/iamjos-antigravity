@extends('layouts.master', ['title' => 'Detail Promo', 'main' => 'Promo'])

@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
    crossorigin="" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>
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
                            <a href="{{ route('promo.index') }}" class="mt-1">
                                <span class="menu-icon back pt-1">
                                    <i class="ki-duotone ki-arrow-left">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </a>
                            <h1 class="text-capitalize mb-0">{{ $promo->name }}</h1>
                            <a href="{{ route('promo.edit', $promo->id) }}">
                                <i class="ki-duotone ki-notepad-edit fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>
                        </div>
                        <div class="hover-scroll-x mt-5">
                            <div class="d-grid">
                                <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                    <li class="nav-item">
                                        <a class="nav-link active btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_information" data-bs-toggle="tab" href="#tab_information">
                                            Informasi Promo
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_participation" data-bs-toggle="tab" href="#tab_participation">
                                            Riwayat Claim Promo
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <div class="mt-6">
                    <div class="card-body v2">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade active show" id="tab_information" role="tabpanel">
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
                                                            <h2>Promo Thumbnail</h2>
                                                        </div>
                                                        <!--end::Card title-->
                                                    </div>
                                                    <div class="card-body pt-10">
                                                        <!--begin::Summary-->
                                                        <div class="d-flex flex-center flex-column mb-5">
                                                            <!--begin::Avatar-->
                                                            <div class="symbol symbol-200px mb-7">
                                                                <img src="{{ asset($promo->image) }}" alt="image">
                                                                {{-- <img src="assets/media/avatars/300-1.jpg" alt="image" /> --}}
                                                            </div>
                                                            <!--end::Avatar-->
                                                            <!--begin::Name-->
                                                            <a href="#" class="fs-3 text-gray-800 text-hover-primary fw-bold mb-1">{{ $promo->name }}</a>
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
                                                            <h2>Promo Detail</h2>
                                                        </div>
                                                        <!--end::Card title-->
                                                    </div>
                                                    <!--end::Card header-->
                                                    <!--begin::Card body-->
                                                    <div class="card-body pt-0 pb-5">
                                                        <div class="row">
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Nama Promo</label>
                                                                <p class="text-label">{{ $promo->name }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Sub Nama Promo</label>
                                                                <p class="text-label">{{ $promo->subname }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Kode Promo</label>
                                                                <p class="text-label">{{ $promo->code }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Tipe Member</label>
                                                                <p class="text-label">{{ $promo->translated_member_type }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Tipe Promo</label>
                                                                <p class="text-label">{{ $promo->translated_type }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Kuota</label>
                                                                <p class="text-label">{{ $promo->quota }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Max. Penggunaan / User</label>
                                                                <p class="text-label">{{ $promo->max_use }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Tipe Diskon</label>
                                                                <p class="text-label">{{ $promo->discount_type == "PERCENT" ? "Persen" : "Nominal Tetap" }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2 {{ $promo->discount_type == 'FIXED' ? 'd-none' : '' }}">
                                                                <label class="text-label text-muted">Diskon (%)</label>
                                                                <p class="text-label">{{ $promo->discount_percent }} %</p>
                                                            </div>
                                                            <div class="col-6 mb-2 {{ $promo->discount_type == 'FIXED' ? '' : 'd-none' }}">
                                                                <label class="text-label text-muted">Diskon (Rp)</label>
                                                                <p class="text-label">Rp{{ number_format($promo->discount_fixed,0,',','.') }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Min. Pembelian</label>
                                                                <p class="text-label">Rp{{ number_format($promo->min_purchase,0,',','.') }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Max. Diskon</label>
                                                                <p class="text-label">Rp{{ number_format($promo->max_discount,0,',','.') }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Tanggal Mulai Berlaku</label>
                                                                <p class="text-label">{{ date('d F Y H:i', strtotime($promo->start_date . $promo->start_time )) }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Tanggal Expired</label>
                                                                <p class="text-label">{{ date('d F Y H:i', strtotime($promo->expiry_date . $promo->expiry_time )) }}</p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Status Promo</label>
                                                                <p class="text-label">
                                                                    @if ($promo->is_active)
                                                                    <span class='badge text-white bg-success'>Aktif</span>
                                                                    @else
                                                                    <span class='badge text-white bg-danger'>Non Aktif</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Status Publish</label>
                                                                <p class="text-label">
                                                                    @if ($promo->is_publish)
                                                                    <span class='badge text-white bg-success'>Publish</span>
                                                                    @else
                                                                    <span class='badge text-white bg-danger'>Non Publish</span>
                                                                    @endif
                                                                </p>
                                                            </div>
                                                            <div class="col-6 mb-2">
                                                                <label class="text-label text-muted">Syarat & Ketentuan</label>
                                                                <p class="text-label"><?= $promo->term_and_condition; ?></p>
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
                            <div class="tab-pane fade" id="tab_participation" role="tabpanel">
                                <div class="card card-flush">
                                    <!--begin::Content-->
                                    <div class="card-header border-0">
                                        <!--begin::Card title-->
                                        <div class="card-title">
                                            <h2>List User Claim Promo</h2>
                                        </div>
                                        <!--end::Card title-->
                                    </div>
                                    <div class="card-body pb-5">
                                        <table id="datatable-claimed-promo" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th style="width: 5%">No</th>
                                                    <th>Nama User</th>
                                                    <th>Kode Transaksi</th>
                                                    <th>Tanggal Claim</th>
                                                    <th>Total Potongan</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-dark fw-semibold"></tbody>
                                        </table>
                                    </div>
                                    <!--end::Content-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
    @endsection

    @push('js')
    <script>
        $(document).ready(function() {
            var table = $('#datatable-claimed-promo').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('promo.show', $promo->id) }}"
                },
                language: {
                    "paginate": {
                        "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                    },
                    "loadingRecords": "Loading...",
                    "processing": "Processing...",
                },
                columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'transaction.user.name',
                        name: 'transaction.user.name',
                        responsivePriority: -2,
                    },
                    {
                        data: 'transaction_detail',
                        name: 'transaction_detail',
                        responsivePriority: -1,
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        responsivePriority: -1,
                    },
                    {
                        data: 'discount_promo',
                        name: 'discount_promo',
                        responsivePriority: -1,
                    },
                ]
            });
        });
    </script>
    @endpush