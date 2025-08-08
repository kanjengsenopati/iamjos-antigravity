@extends('layouts.master', ['title' => 'Laporan Komentar', 'main' => 'Dashboard'])
@push('css')
    <style>
        .w-110px {
            width: 90px !important;
        }
        /* .btn-secondary {
            background: var(--color-gray-10, #262626) !important;
            color: white !important;
        } */
        .btn-sm {
            padding-left: 0 !important;
            padding-right: 0 !important;
        }
    </style>
@endpush

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
                            <span class="card-label fw-bold fs-3 mb-1">Laporan Komentar</span>
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
                                    <th>Nama</th>
                                    <th>Komentar</th>
                                    <th>Artikel</th>
                                    <th>Total Laporan</th>
                                    <th>Status</th>
                                    <th style="width: 20%" class="text-center min-w-100px">Aksi</th>
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
                url: "{{ route('comment-report.index') }}",
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
                    data: 'user.name',
                    name: 'user.name',
                    responsivePriority: -2,
                },
                {
                    data: 'comment',
                    name: 'comment'
                },
                {
                    data: 'article_title',
                    name: 'article_title'
                },
                {
                    data: 'total_report',
                    name: 'total_report'
                },
                {
                    data: 'status',
                    name: 'status'
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
<script>
    $(document).on('click', '.btn-block', function(e) {
    var form = $("#" + e.target.dataset.id);
    Swal.fire({
    title: 'Blokir Komentar ini ?',
    text: 'Anda yakin akan memblokir komentar ini ?',
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: 'success',
    cancelButtonColor: 'primary',
    confirmButtonText: 'Blokir',
    cancelButtonText: 'Batal',
    }).then((res) => {
    if (res.isConfirmed) {
    form.submit();
    } else {
    return false;
    }
    });
    return false;
    })
</script>
@endpush