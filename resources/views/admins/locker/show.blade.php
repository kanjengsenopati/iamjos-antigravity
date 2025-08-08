@extends('layouts.master', ['title' => 'Riwayat Loker', 'main' => 'Data Loker'])
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
                            {{-- add icon action back --}}
                            <a href="{{ route('locker.index') }}" class="btn btn-icon btn-active-color-primary me-3">
                                <i class="fa fa-arrow-left"></i>
                            </a>
                            <span class="card-label fw-bold fs-3 mb-1">Riwayat Loker {{ $locker->name ?? '' }}</span>
                        </h3>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <div class="d-flex flex-wrap gap-4 align-items-center">
                                <div>
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Select an option" data-kt-table-widget-4="filter_status">
                                        @foreach ($gymPlaces as $gym_place)
                                        <option value="{{ $gym_place->id }}">{{ $gym_place->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <div class="card-body pt-0">
                        <table id="datatable-history-locker" class="table table-hover align-middle table-row-dashed">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Waktu</th>
                                    <th>Nama User</th>
                                    <th>Status</th>
                                    {{-- <th class="text-center min-w-100px">Aksi</th> --}}
                                </tr>
                            </thead>
                            <tbody class="text-dark fw-semibold"></tbody>
                        </table>
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
            var table = $('#datatable-history-locker').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('locker.show', ['locker' => $locker->id]) }}",
                    type: 'GET',
                    data: {
                        gym_place_id: $('#gym_place_id').val()
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
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'start_date',
                        name: 'start_date',
                    },
                    {
                        data: 'user.name',
                        name: 'user.name',
                        responsivePriority: -2
                    },
                    {
                        data: 'status',
                        name: 'status',
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     responsivePriority: -1
                    // },
                ]
            });

            $('#gym_place_id').on('change', function() {
                table.ajax.url(`history-locker?gym_place_id=${$('#gym_place_id').val()}`).load();
            })
        });
</script>
@endpush