@extends('layouts.pt-master', ['title' => 'Member'])
@push('css')
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet"
    type="text/css" />
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

    td {
        color: white !important;
        font-weight: 400 !important;
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

    .not-table {
        height: 50vh;
        display: flex;
        align-items: center;
        justify-content: center;
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
                    <p class="text-grey"><span class="text-primary">Home</span> - Member</p>
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
                        <!--begin::Search-->
                        <div class="d-flex input-wrap align-items-center position-relative my-1">
                            <img class="search" src="{{ asset('assets/media/icons/search.svg') }}" alt="">
                            <input type="text" id="search-member" class="form-control" placeholder="Cari member" />
                        </div>
                        <!--end::Search-->
                    </div>
                    <!--end::Title-->
                    <!--begin::Controls-->
                    <form action="" method="GET" id="filter-status-form">
                        <div class="d-flex flex-wrap align-items-start gap-4 gap-sm-0">
                            <!--begin::Tab nav-->
                            <ul class="nav nav-pills me-4 d-flex align-items-center gap-2">
                                <li class="nav-item">
                                    <a class="btn type" data-bs-toggle="tab" href="#kt_project_users_card_pane">
                                        <img src="{{ asset('assets/media/icons/SquaresFour.svg') }}" alt="">
                                    </a>
                                </li>
                                <li class="nav-item m-0">
                                    <a class="btn type active" data-bs-toggle="tab" href="#kt_project_users_table_pane">
                                        <img src="{{ asset('assets/media/icons/Table.svg') }}" alt="">
                                    </a>
                                </li>
                            </ul>
                            {{-- <ul class="nav nav-pills d-flex mb-2 mb-sm-0">
                            </ul> --}}
                            <!--end::Tab nav-->
                            <!--begin::Actions-->
                            <div class="d-flex input-wrap align-items-center position-relative w-sm-200px me-4">
                                <img class="search" src="{{ asset('assets/media/icons/Calendar2.svg') }}" alt="">
                                <!--begin::Datepicker-->
                                <input class="ps-4 form-control" placeholder="Pilih rentang tanggal" name="due_date"
                                    id="date_picker" />
                                <!--end::Datepicker-->
                            </div>
                            <div class="w-sm-125px">
                                <select data-control="select2" data-hide-search="true"
                                    class="form-select cursor-pointer" id="filter-status" name="status"
                                    onchange="this.form.submit()">
                                    <option selected value="">Status</option>
                                    <option value="ACTIVE" {{ request()->status == 'ACTIVE' ? 'selected' : '' }}>Aktif
                                    </option>
                                    <option value="FINISHED" {{ request()->status == 'FINISHED' ? 'selected' : '' }}>
                                        Selesai</option>
                                </select>

                            </div>
                            <!--end::Actions-->
                        </div>
                    </form>
                    <!--end::Controls-->
                </div>
                <!--end::Toolbar-->
                <!--begin::Tab Content-->
                <div class="tab-content">
                    <!--begin::Tab pane-->
                    <div id="kt_project_users_card_pane" class="tab-pane fade">
                        <!--begin::Row-->
                        <div class="row g-6 g-xl-9" id="wrap_members">
                            <!--begin::Col-->
                        </div>
                        <!--begin::Pagination-->
                        <div class="d-flex flex-stack flex-wrap pt-10">
                            <div class="fs-6 fw-semibold text-gray-700">
                                {{-- Showing 1 to 10 of 50 entries --}}
                            </div>
                            <!--begin::Pages-->
                        </div>
                        <!--end::Pagination-->
                    </div>
                    <!--end::Tab pane-->
                    <!--begin::Tab pane-->
                    <div id="kt_project_users_table_pane" class="tab-pane show active fade">
                        <div class="card">
                            <div class="card-body">
                                <!--begin::Table container-->
                                <div class="table-responsive">
                                    <!--begin::Table-->
                                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="table-membership">
                                        <thead>
                                            <tr class="text-start text-grey fw-bold fs-7 text-uppercase gs-0">
                                                <!--<th style="width: 5%">No</th>-->
                                                <th class="min-w-200px">NAMA</th>
                                                <th class="min-w-100px">JENIS KELAMIN</th>
                                                <th class="min-w-125px">PACKAGE</th>
                                                <th class="min-w-75px">SISA SESI</th>
                                                <th class="min-w-125px">MASA BERLAKU</th>
                                                <!--<th class="min-w-75px">STATUS</th>-->
                                                <th class="text-end min-w-100px"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-600 fw-semibold">

                                        </tbody>
                                    </table>
                                    <!--end::Table-->
                                </div>
                                <!--end::Table container-->
                                <!--begin::Pagination-->

                                <!--end::Pagination-->
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
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('assets/js/custom/apps/projects/list/list.js') }}"></script>
    <script src="{{ asset('assets/js/custom/apps/projects/users/users.js') }}"></script>
    <script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
    <script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/create-app.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/new-target.js') }}"></script>
    <script>
        $(document).ready(function() {
            flatpickr(document.querySelector('[name="due_date"]'), {
                enableTime: false,
                dateFormat: 'd-m-Y',
                mode: 'range',
            });
        })
    </script>
    <script>
        $(document).ready(function() {
                var table = $('#table-membership').DataTable({
                    ordering: false,
                    processing: true,
                    serverSide: true, 
                    responsive: true,
                    ajax: {
                        url: "{{ route('personal-trainer.membership.index') }}",
                        type: 'GET',
                        data: function(d) {
                            d.search = $('#search-member').val();
                            d.status = $('#filter-status').val();
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
                        // {
                        //     "data": null,
                        //     "sortable": false,
                        //     "searchable": false,
                        //     responsivePriority: -3,
                        //     render: function(data, type, row, meta) {
                        //         return meta.row + meta.settings._iDisplayStart + 1;
                        //     }
                        // },
                        {
                            data: 'avatar_and_name',
                            name: 'avatar_and_name',
                            responsivePriority: -2,
                        },
                        {
                            data: 'gender',
                            name: 'gender',
                            render: function(data) {
                                return `<h6 class="mb-0 fw-400">${data}</h6>`
                            }
                        },
                        {
                            data: 'session_and_period',
                            name: 'session_and_period',
                            render: function(data) {
                                return `<h6 class="mb-0 fw-400">${data}</h6>`
                            }
                        },
                        {
                            data: 'session_remaining',
                            name: 'session_remaining',
                            render: function(data) {
                                if (data > 2) {
                                    return `<button class="btn-status text-white bg-blue">${data} Sesi</button>`
                                } else if (data > 0 && data < 3) {
                                    return `<button class="btn-status text-white bg-red">${data} Sesi</button>`
                                } else {
                                    return `<button class="btn-status text-white bg-grey-9">${data} Sesi</button>`
                                }
                            }
                        },
                        {
                            data: 'valid_until',
                            name: 'valid_until',
                            render: function(data) {
                                return `<h6 class="mb-0 fw-400">${data}</h6>`
                            }
                        },
                        // {
                        //     data: 'status',
                        //     name: 'status',
                        //     render: function(data, type, row) {
                        //         return data
                        //     },
                        // },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            responsivePriority: -1,
                        },
                    ]
                });
                $('#search-member').on('keyup', function() {
                    table.search(this.value).draw();
                });
            });

            // get date range

            $('#date_picker').on('change', function() {
                console.log($(this).val());
            })

            // get data member untuk tampilan card
            const loader = `<div id="loader" class="text-center not-table mb-3 d-flex flex-column justify-content-center align-items-center gap-2">
                                <span class="spinner-border text-primary" role="status"></span>
                                <p>Loading...</p>
                            </div>`
            const not_found = `<div id="loader" class="text-center not-table mb-3 d-flex flex-column justify-content-center align-items-center gap-2">
                                <p>Member tidak ditemukan.</p>
                            </div>`

            const wrap = $('#wrap_members') 
            
            // get query params
            const urlParams = new URLSearchParams(window.location.search);
            let status = urlParams.get('status');
            
            function getDataMember() {
                wrap.empty()
                wrap.append(loader)
                $.ajax({
                    url: "{{ route('personal-trainer.membership.index') }}",
                    type: "GET",
                    data: { 
                        search: $('#search-member').val(),
                        status: status
                    },
                    success: function({data}) {
                        $('#loader').remove()

                        if(data.length===0) {
                            wrap.empty()
                            wrap.append(not_found)
                        } else {
                            const result = data.map(item=>{
                                console.log(item);
                                return `<div class="col-md-6 col-lg-4">
                                        <div class="card border-radius-xxl">
                                            <div class="card-body d-flex flex-center flex-column pt-12 p-9">
                                                <div class="symbol symbol-65px symbol-circle mb-5">
                                                    <img src="${item.avatar || 'assets/media/avatars/blank.png'}"
                                                        alt="image" />
                                                </div>
                                                <a href="#" class="fs-4 fw-500 text-primary mb-0">${item.name || ''}</a>
                                                <div class="d-flex gap-2 align-items-center">
                                                    <div class="text-grey ${item.phone ? '' : 'd-none'}">
                                                        ${item.phone}
                                                    </div>
                                                    <span class="mb-2 ${item.phone ? '' : 'd-none'}">.</span>
                                                    <div class="text-grey">
                                                        ${item.gender}
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 mt-4">
                                                    ${item.status}
                                                    <button class="btn-status fw-bold bg-gray text-orange">
                                                        ${item.personal_trainer_packet_session_histories[0].start_active_date || ''}
                                                        s/d
                                                        ${item.personal_trainer_packet_session_histories[0].expiry_date || ''}
                                                    </button>
                                                </div>
                                                <div class="d-flex align-items-center gap-2 wrap-btn mt-6">
                                                    <a
                                                        href="/trainer/chat?user_id=${item.id}">
                                                        <button
                                                            class="bg-primary text-white px-4 py-2 border-radius-xxl">Chat</button>
                                                    </a>
                                                    <a href="/trainer/member/${item.id}"
                                                        class="input-wrap text-white-10 px-4 py-2 fw-500 border-radius-xxl d-flex gap-2 align-items-center">
                                                        Lihat Detail
                                                        <img class="detail"
                                                            src="{{ asset('assets/media/icons/ArrowSquareIn.svg') }}" alt="">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>`
                            }).join('')
    
                            wrap.empty()
                            wrap.append(result)
                        }
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            }

            getDataMember()

            $('#search-member').on('keyup', function() {
                getDataMember()
            });
           
    </script>
    @endpush