<div class="d-flex gap2 align-items-center justify-content-end">
    <div class="mb-4">
        <a type="button" class="btn btn-primary btn-sm" onclick="createSession()">
            <i class="fa fa-plus"></i> Buat Sesi
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-session-user" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Nama Kelas</th>
                <th>Coach</th>
                <th>Status</th>
                <th>Keterangan</th>
                <th>Program</th>
                <th class="text-center" style="width: 10%">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
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
        tableSession();
    });

    function tableSession() {
        $('#datatable-session-user').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('session-user') }}",
                type: 'GET',
                data: {
                    user_id: "{{ $user->id }}"
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
                    data: 'time',
                    name: 'time',
                },
                {
                    data: 'name',
                    name: 'name',
                    searchable: false,
                },
                {
                    data: 'coach',
                    name: 'coach',
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
                    data: 'programs',
                    name: 'programs',
                    searchable: false,
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

    function rejectReason(id) {
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
                    url: "{{ route('personal-trainer-schedule-packet-session.cancel-schedule.user', ':id') }}".replace(':id', id),
                    type: 'PUT',
                    data: {
                        cancel_reason: result.value
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            tableSession(); // Use the reference to reload the table
                            Swal.fire({
                                title: 'Berhasil!',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'Tutup',
                            });
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