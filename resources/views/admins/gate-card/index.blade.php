@extends('layouts.master', ['title' => 'Gate Card', 'main' => 'Dashboard'])
@section('content')
<div id="kt_app_content" class="app-content flex-column-fluid pt-6">
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Card-->
        <div class="card card-flush">
            <div class="card-body pt-0">
                <div class="mb-2 d-flex align-items-center flex-wrap justify-content-between gap-3 border-0 pt-6">
                    <!--begin::Card title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1">List Gate Card</span>
                    </h3>
                    <!--end::Card title-->
                    <div class="d-flex flex-wrap gap-3">
                        <div>
                            <x-form.date-range-filter />
                            <input type="text" id="start_date" hidden>
                            <input type="text" id="end_date" hidden>
                        </div>
                        <div>
                            <a type="button" class="btn btn-primary btn-sm" href="{{ route('gate-card.create') }}">
                            <i class="fa fa-plus"></i> Gate Card</a>
                        </div>
                        {{-- <div>
                            <a type="button" class="btn btn-sm btn-primary text-nowrap"
                                onclick="importCheckinCheckout()">
                                <i class="ki-duotone ki-exit-down fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                Import
                            </a>
                        </div> --}}
                    </div>
                </div>
                <!--begin::Table-->
                <table id="table-gate-card" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0"
                    style="width:100%">
                    <thead>
                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                            <th style="width: 5%">No</th>
                            <th>Nama Pemilik kartu</th>
                            <th>Nomor Kartu</th>
                            <th>Total Activity</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="fw-semibold text-dark">
                    </tbody>
                </table>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
</div>
@endsection
@push('js')
<script>
    function table() {
        var table = $('#table-gate-card').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('gate-card.index') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
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
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'card_owner',
                    name: 'card_owner',
                    orderable: true,
                    searchable: true,
                },
                {
                    data: 'card_number',
                    name: 'card_number',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -2,
                },
                {
                    data: 'activity',
                    name: 'activity',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -2,
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
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                }
            ]
        })
    }
</script>
@endpush