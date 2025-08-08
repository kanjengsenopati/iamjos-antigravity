@extends('layouts.master', ['title' => 'Detail Gate Card', 'main' => 'Gate Card'])
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="d-flex gap-2 align-items-center mb-3">
                        <a href="{{ route('gate-card.index') }}" class="mt-1">
                            <span class="menu-icon back pt-1">
                                <i class="ki-duotone ki-arrow-left">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </a>
                        <h3 class="text-capitalize mb-0">{{ $gateCard->card_owner }}</h3>
                        <a href="{{route('gate-card.edit', $gateCard->id)}}">
                            <i class="ki-duotone ki-notepad-edit fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </a>
                    </div>
                    <hr class="mt-8 mb-3">
                    <div class="row">
                        <div class="col-sm-4">
                            <label class="text-label text-muted">Nama Pemilik Kartu</label>
                            <p>{{ $gateCard->card_owner }}</p>
                        </div>
                        <div class="col-sm-4">
                            <label class="text-label text-muted">Nomor Kartu</label>
                            <p>{{ $gateCard->card_number }}</p>
                        </div>
                        <div class="col-sm-4">
                            <label class="text-label text-muted">Status</label>
                            <p>
                            @if ($gateCard->is_active)
                                <span class="badge badge-light-success">Aktif</span>
                            @else
                                <span class="badge badge-light-warning">Non Aktif</span>
                            @endif
                            </p>
                        </div>
                    </div>

                </div>
                <!--end::Card body-->
            </div>
            <!--begin::Card-->
            <div class="card mt-6">
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="mb-2 d-flex align-items-center flex-wrap justify-content-between gap-3 border-0 pt-6">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Detail Gate Activity</span>
                        </h3>
                        <!--end::Card title-->
                        <div class="d-flex flex-wrap gap-3">
                            <div>
                                <x-form.date-range-filter />
                                <input type="text" id="start_date" hidden>
                                <input type="text" id="end_date" hidden>
                            </div>
                        </div>
                    </div>
                    <table id="table-gate-activity" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0"
                        style="width:100%">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th style="width: 5%">No</th>
                                <th>Gate</th>
                                <th>Waktu</th>
                                <th>Aktifitas</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-dark">
                        </tbody>
                    </table>
                </div>
                <!--end::Card body-->
            </div>
        </div>
    </div>
</div>
<!--end::Wrapper-->
@include('admins.gate-activity.modal')
@endsection

@include('admins.gate-activity.script')
@push('js')
<script>
    function table() {
        var table = $('#table-gate-activity').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('gate-card.show', $gateCard->id) }}",
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
                    data: 'gate_number',
                    name: 'gate_number',
                    render: function(data, type, row) {
                        return data ? data : '-';
                    },
                },
                {
                    data: 'activity_at',
                    name: 'activity_at',
                    responsivePriority: -1
                },
                {
                    data: 'activity',
                    name: 'activity',
                },
                {
                    data: 'status',
                    name: 'status',
                    responsivePriority: -1
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1
                },
            ]
        })
    }
</script>
@endpush