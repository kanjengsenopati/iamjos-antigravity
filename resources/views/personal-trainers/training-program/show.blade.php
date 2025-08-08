@extends('layouts.pt-master', ['title' => 'Detail Program Latihan'])
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card card-flush">
                    <div class="card-header mt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <a href="{{ route('trainer.training-program.index') }}" class="btn btn-icon btn-active-color-primary me-3">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                            <span class="card-label fw-bold fs-3 mb-1">Detail Program Latihan: {{ $program->name ?? '' }}</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <table id="datatable-program-details" class="table table-hover align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama Gerakan</th>
                                    <th>Number Set</th>
                                    <th>Weight</th>
                                    <th>Reps Set</th>
                                    <th>Waktu Set</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark fw-semibold"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        $('#datatable-program-details').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('trainer.training-program.show', $program->id) }}",
                type: 'GET'
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
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'movement_name',
                    name: 'movement_name',
                },
                {
                    data: 'set_number',
                    name: 'set_number',
                },
                {
                    data: 'weight',
                    name: 'weight',
                },
                {
                    data: 'reps',
                    name: 'reps',
                },
                {
                    data: 'time',
                    name: 'time',
                }
            ]
        });
    });
</script>
@endpush

