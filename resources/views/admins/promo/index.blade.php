@extends('layouts.master', ['title' => 'Promo', 'main' => 'Dashboard'])
@section('content')
    <div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
        <!--begin::Content wrapper-->
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <!--begin::Card header-->
                        <div class="card-header mt-6">
                            <!--begin::Card title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Promo</span>
                            </h3>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Button-->
                                <a type="button" class="btn btn-sm btn-primary btn-create"
                                    href="{{ route('promo.create') }}">
                                    <i class="fa fa-plus"></i>
                                    Promo</a>
                                <!--end::Button-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <table id="datatable" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th class="w-125px">Thumbnail</th>
                                        <th>Nama Promo</th>
                                        <th>Nilai Promo</th>
                                        <th>Tanggal Berlaku</th>
                                        <th class="min-w-150px">Quota</th>
                                        <th>Tipe</th>
                                        <th>Status</th>
                                        <th>Status Publish</th>
                                        <th>Global/Spesial Gym</th>
                                        <th class="text-center min-w-100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark fw-semibold"></tbody>
                            </table>
                            <!--end::Table-->
                        </div>
                        <!--end::Card body-->
                    </div>
                    <!--end::Card-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
        <!--end::Content wrapper-->

    </div>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('promo.index') }}",
                    type: 'GET',
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
                        data: 'image',
                        name: 'image',
                        render: function(data, type, row) {
                            if (data == null) {
                                return `
                            <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center">
                                <span class="fs-2x fw-bold text-primary text-capitalize">
                                    ${row.name.charAt(0)}</span>
                            </div>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px object-fit-cover w-75px img-thumnail" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        responsivePriority: -2,
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return (row.discount_type == 'FIXED' ? '<i><small>Nilai Promo: Rp.' +
                                    row.discount_fixed.toString().replace(/\B(?=(\d{3})+(?!\d))/g,
                                        ",") : '<i><small>Nilai Promo:' + row.discount_percent + '%'
                                    ) +
                                ' <hr>Min.Pembelian: Rp' + row.min_purchase.toString().replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                ' <hr>Max.Diskon: Rp' + row.max_discount.toString().replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '</small></i>';
                        }
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                        render: function(data, type, row, meta) {
                            return `<p>${data} ${row.start_time} ~ ${row.expiry_date}, ${row.expiry_time}</p>`;
                        }
                    },
                    {
                        data: 'quota',
                        name: 'quota',
                        render: function(data, type, row, meta) {
                            return `<small><i>Kuota Awal: ${data}
                        <hr>
                        Kuota Terpakai: ${row.used_quota}
                        <hr>
                        Sisa Kuota: ${row.remaining_quota}
                        <hr>
                        Max.Penggunaan / User: ${row.max_use}
                        </i></small>`;
                        }
                    },
                    {
                        data: 'translated_type',
                        name: 'translated_type'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        render: function(data, type, row) {
                            if (data) {
                                return `<span class="badge badge-light-success">Aktif</span>`;
                            } else {
                                return `<span class="badge badge-light-warning">Non Aktif</span>`;
                            }
                        },
                    },
                    {
                        data: 'is_published',
                        name: 'is_published',
                        render: function(data, type, row) {
                            if (data) {
                                return `<span class="badge badge-light-success">Promo di Tampilkan Publik/ di Aplikasi</span>`;
                            } else {
                                return `<span class="badge badge-light-warning">Promo di Sembunyikan</span>`;
                            }
                        },
                    },
                    {
                        data: 'is_global',
                        name: 'is_global',
                        render: function(data, type, row, meta) {
                            return data ? 'Semua Gym' : 'Tempat Gym Tertentu';
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -1,
                    },
                ]
            });
        });
    </script>
@endpush
