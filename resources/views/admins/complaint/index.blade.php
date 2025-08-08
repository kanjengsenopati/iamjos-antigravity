@extends('layouts.master', ['title' => 'Data Komplain', 'main' => 'Dashboard'])
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card card-flush">
                    <div class="card-header mt-6">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Data Komplain</span>
                        </h3>
                    </div>
                    <div class="card-body pt-0">
                        <!-- Form Filter -->
                        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4
                                     justify-content-start justify-content-sm-end align-items-center">

                            <!-- Dropdown Status Filter -->
                            <div>
                                <select name="statusFilter" id="statusFilter" class="form-select w-170px"
                                    data-control="select2" data-hide-search="true"
                                    data-dropdown-css-class="w-150px" data-placeholder="Pilih Status">
                                    <option value="">Semua Status</option>
                                    <option value="aktif">Aktif</option>
                                    <option value="diproses">Diproses</option>
                                    <option value="ditutup">Ditutup</option>
                                </select>
                            </div>

                        </div>
                        <!-- DataTable -->
                        <table id="table-complaint" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" style="width:100%">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th>Subjek</th>
                                    <th>Pengirim</th>
                                    <th>Kategori</th>
                                    <th>Terakhir Diupdate</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-dark">
                            </tbody>
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
        // Inisialisasi DataTables
        var table = $('#table-complaint').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('complaint.index') }}",
                data: function(d) {
                    // Ambil data dari dropdown filter dan date range
                    d.status = $('#statusFilter').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            columns: [{
                    data: null,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: null,
                    name: 'ticket_subject',
                    render: function(data, type, row) {
                        return `<strong>${row.ticket}</strong> - <span>${row.subject}</span>`;
                    }
                },
                {
                    data: 'user.name',
                    name: 'user.name'
                },
                {
                    data: 'category',
                    name: 'category'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at'
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data) {
                        let badgeClass = '';
                        if (data === 'aktif') {
                            badgeClass = 'badge-success';
                        } else if (data === 'diproses') {
                            badgeClass = 'badge-warning';
                        } else {
                            badgeClass = 'badge-secondary';
                        }
                        return `<span class="badge ${badgeClass}">${data.charAt(0).toUpperCase() + data.slice(1)}</span>`;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ],
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                loadingRecords: "Loading...",
                processing: "Processing..."
            },
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ]
        });

        // Update table ketika filter status atau date range diubah
        $('#statusFilter').change(function() {
            table.draw();
        });

        $('#start_date, #end_date').change(function() {
            table.draw();
        });
    });
</script>
@endpush