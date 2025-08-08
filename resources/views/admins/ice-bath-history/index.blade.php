@extends('layouts.master', ['title' => 'Riwayat Ice Bath', 'main' => 'Dashboard'])
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
                            <span class="card-label fw-bold fs-3 mb-1">Data Riwayat Ice Bath</span>
                        </h3>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Button-->
                            {{-- <a type="button" class="btn btn-primary btn-sm btn-create"
                                href="{{ route('sauna-history.create') }}">
                                <i class="fa fa-plus"></i>
                                sauna-history</a> --}}
                            <!--end::Button-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-6">
                        <div
                            class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <div class="d-flex flex-wrap gap-4 align-items-center">

                                <div>
                                    <x-form.daily-date-range-filter />
                                    <input type="text" id="start_date" hidden>
                                    <input type="text" id="end_date" hidden>
                                </div>
                            </div>
                        </div>
                        <!--begin::Table-->
                        <div class="row">
                            <div class="col-sm-4">
                                <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5">
                                    <!--begin::Symbol-->
                                    <div class="symbol symbol-30px me-5 mb-8">
                                        <span class="symbol-label">
                                            <i class="ki-duotone ki-chart fs-1 text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </span>
                                    </div>
                                    <!--end::Symbol-->
                                    <!--begin::Stats-->
                                    <div class="m-0">
                                        <!--begin::Number-->
                                        <span class="text-gray-700 fw-bolder d-block fs-2qx lh-1 ls-n1 mb-1"
                                            id="Total">0</span>
                                        <!--end::Number-->
                                        <!--begin::Desc-->
                                        <span class="text-gray-500 fw-semibold fs-6">Total Penggunaan</span>
                                        <!--end::Desc-->
                                    </div>
                                    <!--end::Stats-->
                                </div>
                            </div>
                        </div>
                        <table id="table-ice-bath-history"
                            class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" style="width:100%">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Tanggal</th>
                                    <th>Nama</th>
                                    <th>Status</th>
                                    {{-- <th style="width: 10%" class="text-center min-w-100px">Aksi</th> --}}
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-dark">
                            </tbody>
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
    $(document).ready(() => {
        // Initialize DataTable function
        // Call functions
        table();  // Corrected the function name to initTable()
        getRevenue();
    });
</script>
<script>
    function table() {
        $('#table-ice-bath-history').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        responsive: true,
        destroy: true,
        ajax: {
        url: "{{ route('ice-bath-history.index') }}",
        data: function(d) {
        d.start_date = $("#start_date").val();
        d.end_date = $("#end_date").val();
        d.type = 'data';
        },
        },
        language: {
        paginate: {
        next: "<i class='fa fa-angle-right'></i>",
        previous: "<i class='fa fa-angle-left'></i>"
        },
        loadingRecords: "Loading...",
        processing: "Processing...",
        },
        lengthMenu: [
        [10, 25, 50, 100, -1],
        [10, 25, 50, 100, "All"]
        ],
        columns: [
        {
        data: null,
        sortable: false,
        searchable: false,
        render: function(data, type, row, meta) {
        return meta.row + meta.settings._iDisplayStart + 1;
        }
        },
        { data: 'date', name: 'date' },
        { data: 'user', name: 'user', responsivePriority: -1 },
        { data: 'status', name: 'status', responsivePriority: -1 },
        ]
        });
        }
        
        // Load revenue data function
        function getRevenue() {
        $.ajax({
        url: "{{ route('ice-bath-history.index') }}",
        data: {
        start_date: $("#start_date").val(),
        end_date: $("#end_date").val(),
        type: "total-usage",
        },
        success: function(data) {
        $('#Total').text(data.total);
        },
        error: function(err) {
        console.error('Error fetching revenue data:', err);
        }
        });
        }

        // if date range filter value changed then reload table and revenue data
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
        getRevenue();
        });
</script>
@endpush