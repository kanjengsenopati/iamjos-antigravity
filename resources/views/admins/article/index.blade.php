@extends('layouts.master', ['title' => 'Tips & Artikel', 'main' => 'Dashboard'])
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
                            <span class="card-label fw-bold fs-3 mb-1">Tips
                                & Artikel</span>
                        </h3>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Button-->
                            <a type="button" class="btn btn-primary btn-sm" href="{{ route('article.create') }}">
                                <i class="fa fa-plus"></i>
                                Tips & Artikel</a>
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
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th class="w-100px">Thumbnail</th>
                                    <th>Kategori</th>
                                    <th>Title</th>
                                    <th>Tags</th>
                                    <th>Dibuat Pada</th>
                                    <th class="text-center min-w-100px">Aksi</th>
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
                url: "{{ route('article.index') }}",
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
                    data: 'thumbnail',
                    name: 'thumbnail',
                    render: function(data, type, row) {
                        if (data == null) {
                            return `
                            <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center">
                                <span class="fs-2x fw-bold text-primary text-capitalize">
                                    ${row.title.charAt(0)}</span>
                            </div>`;
                        } else {
                            return `<img src="${data}" alt="image" class="w-75px h-50px img-thumnail object-fit-cover" />`;
                        }
                    }
                },
                {
                    data: 'article_category.name',
                    name: 'article_category.name'
                },
                {
                    data: 'title',
                    name: 'title',
                    responsivePriority: -2,
                },
               
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        let badge = '';
                        for (tag of row?.article_tags ?? []) {
                            badge += `<span class="badge badge-secondary me-1 mb-1"><i>${tag.name}</i></span>`;
                        }
                        let enBadge = '';
                        for (enTag of row?.en_article_tags ?? []) {
                            enBadge += `<span class="badge badge-primary en-feature me-1 mb-1"><i>${enTag.name}</i></span>`;
                        }
                        return badge + '<br>' + enBadge;
                    }
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });
    });
</script>
@endpush