@extends('layouts.pt-master', ['title' => 'Booking'])
@push('css')
    <!--end::Fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        [data-bs-theme="light"] {
            --color-gray-10: #F1F1F2;
            --color-white: #000000;
            --color-white-10: #808080;
        }

        [data-bs-theme="dark"] {
            --color-gray-10: #262626;
            --color-white: #FFFFFF;
            --color-white-10: #FFFFFF;

        }

        span.dtr-title {
            color: var(--color-white) !important;
        }

        span.dtr-data {
            padding-left: 10px;
        }

        .text-white-10 {
            color: var(--color-white-10) !important;
        }

        .btn.type img {
            filter: invert(0.3);
        }

        .page-item .page-link {
            border-radius: 50%;
            height: 2.625rem;
            min-width: 2.625rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .input-wrap,
        .btn.input-wrap:hover,
        .btn.input-wrap:focus {
            box-shadow: none;
        }

        img.detail {
            filter: invert(0.5)
        }
        }

        [data-bs-theme="dark"] {
            --color-gray-10: #262626;
        }

        .page-item .page-link {
            border-radius: 50%;
            height: 2.625rem;
            min-width: 2.625rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .input-wrap,
        .btn.input-wrap:hover,
        .btn.input-wrap:focus {
            color: #8C8C8C;
            border-radius: 0.625rem !important;
            background: var(--color-gray-10);
            box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.12), 0px 0px 2px 0px rgba(0, 0, 0, 0.12);
        }


        .form-select {
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
        }

        .input-wrap input::placeholder {
            color: #8C8C8C;
        }

        .btn.type {
            border-radius: 50%;
            width: 2.5rem;
            height: 3.1rem;
            background: var(--color-gray-10);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn.type.active,
        .bg-grey-9 {
            background-color: #434343;
        }

        .text-grey {
            color: #BFBFBF !important;
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

        .bg-red {
            background-color: #D83C15;
        }

        .bg-blue {
            background-color: #1C7EFF;
        }

        .bg-gray {
            background-color: var(--color-gray-10);
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

        .border-radius-xxl {
            border-radius: 1.25rem !important;
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
                        <h2 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Booking </h2>
                        <p class="text-grey"><span class="text-primary">Home</span> - Booking</p>
                        <!--end::Title-->
                    </div>
                    <!--end::Page title-->
                </div>
                <!--end::Container-->
            </div>
            {{-- <div class="card">
            <div class="card-body"> --}}
            <!--begin::Toolbar-->
            <div class="d-flex flex-wrap flex-column-reverse flex-sm-row justify-content-between pb-7 gap-4">
                <!--begin::Title-->
                <div class="d-flex flex-wrap align-items-center my-1">
                </div>
                <!--end::Title-->
                <!--begin::Controls-->
                <div class="d-flex flex-wrap align-items-start gap-2">
                    <!--begin::Actions-->
                    <div>
                        <x-form.date-range-filter />
                        <input type="text" id="start_date" hidden>
                        <input type="text" id="end_date" hidden>
                    </div>
                    <div class="min-w-100px">
                        <select id="status" data-control="select2" data-hide-search="true"
                            class="form-select cursor-pointer">
                            <option selected value="">Status</option>
                            <option value="PENDING">Pending</option>
                            <option value="ONGOING">Sedang Berlangsung</option>
                            <option value="FINISH">Selesai</option>
                            <option value="CANCELED">Dibatalkan</option>
                        </select>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Controls-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Tab Content-->
            <div class="tab-content">
                <!--begin::Tab pane-->
                <div id="kt_project_users_table_pane" class="tab-pane show active fade">
                    <div class="card">
                        <div class="card-body">
                            <!--begin::Table container-->
                            <div class="table-responsive">
                                <!--begin::Table-->
                                <table class="table align-middle table-row-dashed text-dark fs-6 gy-5" id="table-booking">
                                    <thead>
                                        <tr class="text-start text-grey fw-bold fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>NAMA USER</th>
                                            <th>TANGGAL</th>
                                            <th>JAM MULAI</th>
                                            <th>JAM SELESAI</th>
                                            {{-- <th>TOTAL PESERTA HADIR</th> --}}
                                            <th style="width: 5%">STATUS</th>
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
                <!--end::Tab pane-->
            </div>
            {{--
                <!--end::Tab Content-->
            </div>
        </div> --}}
        </div>
        <!--end::Container-->
    @endsection
    @push('js')
        <!--begin::Global Javascript Bundle(mandatory for all pages)-->
        <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
        <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
        <!--end::Global Javascript Bundle-->
        <!--begin::Vendors Javascript(used for this page only)-->
        <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
        <!--end::Vendors Javascript-->
        <script>
            $(document).ready(function() {
                table()
            });

            $('#status').on('change', function() {
                table()
            })

            function table() {
                var table = $('#table-booking').DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    destroy: true,
                    ajax: {
                        url: "{{ route('personal-trainer.booking.index') }}",
                        type: 'GET',
                        data: {
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
                            status: $('#status').val()
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
                    columns: [{
                            "data": null,
                            "sortable": false,
                            "searchable": false,
                            render: function(data, type, row, meta) {
                                return meta.row + meta.settings._iDisplayStart + 1;
                            }
                        },
                        {
                            data: 'user.name',
                            name: 'user.name'
                        },
                        {
                            data: 'date',
                            name: 'date'
                        },
                        {
                            data: 'personal_trainer_schedule.start_time',
                            name: 'personal_trainer_schedule.start_time',
                        },
                        {
                            data: 'personal_trainer_schedule.end_time',
                            name: 'personal_trainer_schedule.end_time',
                        },
                        // {
                        //     data: 'attendance_total',
                        //     name: 'attendance_total'
                        // },
                        {
                            data: null,
                            sortable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                const currentTime = new Date();
                                const startTime = new Date(row.date + ' ' + row.personal_trainer_schedule
                                        .start_time);
                                const endTime = new Date(row.date + ' ' + row.personal_trainer_schedule
                                        .end_time);
                                        
                                if (row.attendance_total > 0) {
                                    

                                    if (startTime > currentTime) {
                                        return `<button class="btn-status fw-bold bg-gray text-blue">Pending</button>`;
                                    } else if (startTime <= currentTime && endTime >= currentTime) {
                                        return `<button class="btn-status fw-bold bg-gray text-blue">Sedang Berlangsung</button>`;
                                    } else {
                                        return `<button class="btn-status fw-bold bg-gray text-green">Selesai</button>`;
                                    }
                                } else {
                                    if (startTime > currentTime) {
                                        return `<button class="btn-status fw-bold bg-gray text-blue">Pending</button>`;
                                    } else if (startTime <= currentTime && endTime >= currentTime) {
                                        return `<button class="btn-status fw-bold bg-gray text-blue">Sedang Berlangsung  (No Checkin)</button>`;
                                    } else {
                                        return `<button class="btn-status fw-bold bg-gray text-blue">Selesai (No Checkin)</button>`;
                                    }

                                    // return `<button class="btn-status fw-bold bg-gray text-danger">Dibatalkan</button>`;
                                }
                            },
                        }

                    ]
                });
            }
        </script>
    @endpush
