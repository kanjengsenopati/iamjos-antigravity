@extends('layouts.master', ['title' => 'Data BPD', 'main' => 'Dashboard'])
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
                                <span class="card-label fw-bold fs-3 mb-1">Data BPD</span>
                            </h3>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar gap-3">
                                <div class="text-muted small me-3">
                                    Terakhir update:
                                    {{ $last ? \Carbon\Carbon::parse($last)->locale('id')->translatedFormat('l, d F Y \p\u\k\u\l H.i') . ' WIB' : '—' }}
                                </div>
                                <button id="btn-refresh-cache" class="btn btn-light-primary btn-sm">
                                    <i class="fa fa-rotate"></i> Refresh Cache
                                </button>
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
                                        <th style="width:5%">No</th>
                                        <th>Nama</th>
                                        <th>Alamat</th>
                                        <th>No Telp</th>
                                        <th>Email</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Data saka controller
            const items = @json($items);

            $('#datatable').DataTable({
                ordering: true,
                processing: false,
                serverSide: false, // penting: client-side wae
                responsive: true,
                data: items,
                language: {
                    paginate: {
                        next: "<i class='fa fa-angle-right'>",
                        previous: "<i class='fa fa-angle-left'>"
                    },
                    loadingRecords: "Loading...",
                    processing: "Processing...",
                    search: "Cari:"
                },
                columns: [{
                        data: null,
                        searchable: false,
                        orderable: false,
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'alamat',
                        name: 'alamat'
                    },
                    {
                        data: 'telp',
                        name: 'telp'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'is_active',
                        name: 'is_active',
                        orderable: true,
                        searchable: false,
                        render: (val) => val === '1' ?
                            '<span class="badge badge-light-success">Aktif</span>' :
                            '<span class="badge badge-light-danger">Nonaktif</span>'
                    },
                ],
                order: [
                    [1, 'asc']
                ]
            });

            $('#btn-refresh-cache').on('click', function(e) {
                e.preventDefault();
                const btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Refreshing...');

                $.ajax({
                    url: "{{ route('bpd.refresh') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        Swal.fire({
                            title: 'Berhasil',
                            text: res?.message || 'Cache diperbarui.',
                            icon: 'success',
                            timer: 1300,
                            showConfirmButton: false
                        });
                        window.location
                            .reload(); // <- cukup ini, jangan panggil dt.ajax.reload()
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Gagal',
                            text: xhr.responseJSON?.message ||
                                'Tidak bisa menyegarkan cache.',
                            icon: 'error'
                        });
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(
                            '<i class="fa fa-rotate"></i> Refresh Cache');
                    }
                });
            });
        });
    </script>
@endpush
