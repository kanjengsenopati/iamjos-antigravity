@push('css')
<style>
    #myTab.nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        font-weight: 500;
    }
</style>
@endpush

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark active" id="list-tab" data-bs-toggle="tab"
            data-bs-target="#list-tab-pane" type="button" role="tab" aria-controls="list-tab-pane"
            aria-selected="true">
            Daftar Physiotherapy
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="session-tab" data-bs-toggle="tab"
            data-bs-target="#session-tab-pane" type="button" role="tab" aria-controls="session-tab-pane"
            aria-selected="false">
            Sesi Physiotherapy
        </button>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <!-- Tab Daftar Physiotherapy -->
    <div class="tab-pane fade show active" id="list-tab-pane" role="tabpanel" aria-labelledby="list-tab" tabindex="0">
        <div class="table-responsive">
            <table id="datatable-physiotherapy-list" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Nama Program</th>
                        <th>Tanggal Aktif</th>
                        <th>Tanggal Expired</th>
                        <th>Sisa Sesi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <!-- Tab Sesi Physiotherapy -->
    <div class="tab-pane fade" id="session-tab-pane" role="tabpanel" aria-labelledby="session-tab" tabindex="0">
        <div class="d-flex gap2 align-items-center justify-content-end mt-4">
            <div class="mb-4">
                <a type="button" class="btn btn-primary btn-sm" onclick="createPhisiotherapySession()">
                    <i class="fa fa-plus"></i> Buat Sesi
                </a>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-physiotherapy-history" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Nama Paket</th>
                        <th>Physioterapist</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                        <th class="text-center" style="width: 10%">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailModalLabel">Detail Data</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="modal-id"></span></p>
                <p><strong>Nama:</strong> <span id="modal-name"></span></p>
                <p><strong>Status:</strong> <span id="modal-status"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function() {    
        tablePhysiotherapyList();
        tablePhysiotherapySession();
    });

    function tablePhysiotherapyList() {
        $('#datatable-physiotherapy-list').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('physiotherapy-history.index') }}",
                type: 'GET',
                data: {
                    user_id: "{{ $user->id }}",
                    type: 'PHYSIOTHERAPY-HISTORY'
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
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'physiotherapy_packet_session.name',
                    name: 'physiotherapy_packet_session.name',
                },
                {
                    data: 'start_active_date',
                    name: 'start_active_date',
                    render: function(data) {
                        return moment(data).format('DD/MM/YYYY');
                    }
                },
                {
                    data: 'expiry_date',
                    name: 'expiry_date',
                    render: function(data) {
                        return moment(data).format('DD/MM/YYYY');
                    }
                },
                {
                    data: 'remaining_session',
                    name: 'remaining_session',
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        const now = moment();
                        const expiryDate = moment(row.expiry_date);
                        
                        if (expiryDate.isBefore(now)) {
                            return '<span class="badge badge-danger">Expired</span>';
                        }
                        
                        if (row.remaining_session == 0) {
                            return '<span class="badge badge-success">Selesai</span>';
                        }
                        
                        if (!row.is_active) {
                            return '<span class="badge badge-secondary">Belum Aktif</span>';
                        }
                        
                        return '<span class="badge badge-primary">Aktif</span>';
                    }
                },
            ]
        });
    }

    function tablePhysiotherapySession() {
        $('#datatable-physiotherapy-history').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('physiotherapy-history.index') }}",
                type: 'GET',
                data: {
                    user_id: "{{ $user->id }}",
                    type: 'SESSION'
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
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
                },
                {
                    data: 'start_time',
                    name: 'start_time',
                },
                {
                    data: 'package',
                    name: 'package',
                    searchable: false,
                },
                {
                    data: 'physiotherapist',
                    name: 'physiotherapist',
                    searchable: false,
                },
                {
                    data: 'translated_status',
                    name: 'translated_status',
                    searchable: false,
                },
                {
                    data: 'cancel_reason',
                    name: 'cancel_reason',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                }
            ]
        });
    }

    function physiotherapyRejectReason(id) {
        Swal.fire({
            title: 'Alasan Pembatalan',
            input: "textarea",
            inputPlaceholder: "Masukkan alasan pembatalan...",
            inputAttributes: {
                "aria-label": "Masukkan alasan pembatalan",
                required: true
            },
            showCancelButton: true,
            confirmButtonText: 'Simpan',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-sm fw-semibold btn-primary',
                cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('physiotherapy-schedule.cancel-schedule.user', ':id') }}".replace(':id', id),
                    type: 'PUT',
                    data: {
                        cancel_reason: result.value
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            tableSession();
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'Tutup',
                            });
                            tablePhysiotherapySession();
                        } else {
                            Swal.fire({
                                title: 'Gagal!',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Terjadi kesalahan pada server.',
                            icon: 'error',
                            confirmButtonText: 'Tutup',
                        });
                    }
                });
            }
        });
    };
</script>

<script>
    $(document).on("click", ".btn-detail", function () {
        var id = $(this).data("id");
        var name = $(this).data("name");
        var status = $(this).data("status");

        $("#modal-id").text(id);
        $("#modal-name").text(name);
        $("#modal-status").text(status);
    });
</script>
@endpush