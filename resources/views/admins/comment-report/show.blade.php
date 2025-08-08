@extends('layouts.master', ['main' => 'Laporan Komentar','title' => 'List Laporan'])
@section('content')
<div class="app-main flex-column pt-6 flex-row-fluid" id="kt_app_main">
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
                            <span class="card-label fw-bold fs-3 mb-1">List Laporan</span>
                        </h3>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Button-->

                            <!--end::Button-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="datatable" class="table  table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Avatar</th>
                                    <th>Nama</th>
                                    <th>Alasan</th>
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
    $(document).ready(function() {
        var table = $('#datatable').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('comment-report.show', $comment->id) }}",
                type: 'GET',
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'></i>",
                    "previous": "<i class='fa fa-angle-left'></i>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    responsivePriority: -2,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                  data: 'avatar',
                    name: 'avatar',
                },
                {
                    data: 'user_name',
                    name: 'user_name',
                    responsivePriority: -1,
                },
                {
                    data: 'reason',
                    name: 'reason'
                },
            ]
        });
    });
</script>
@endpush