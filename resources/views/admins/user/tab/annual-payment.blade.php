
<div class="table-responsive">
    <table id="table-annual-payment" class="table table-hover align-middle table-row-dashed fs-6 gy-5">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th>No</th>
                <th>Nama</th>
                <th>Annual Type</th>
                <th>Periode Annual</th>
                <th>Tanggal Annual</th>
                <th>Tanggal Non Aktif</th>
                <th>Annual Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody class="fw-semibold text-dark">
        </tbody>
    </table>
</div>
@push('js')
<script>
    $(document).ready(() => {
        tableAnnualPayment()
    })
    const tableAnnualPayment = () => {
        var table = $('#table-annual-payment').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('annual-payment.index', ['user_id' => $user->id]) }}"
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1
                    }
                },
                {
                    data: 'user.name',
                    name: 'user.name',
                    responsivePriority: -2,
                    render: function(data, type, row) {
                        // Cek apakah user memiliki deleted_at (telah dihapus)
                        if (row.user.deleted_at) {
                            return data + ' (user telah dihapus)';
                        }
                        return data;
                    }
                },
                {
                    data: 'annual_type',
                    name: 'annual_type',
                },
                {
                    data: 'period_lifetime',
                    name: 'period_lifetime',
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                },
                {
                    data: 'date_off_at',
                    name: 'date_off_at',
                },
                {
                    data: 'status',
                    name: 'status',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1,
                }
            ]
        })

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#table-annual-payment tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#table-annual-payment').fadeIn();
        });
    }
</script>
@endpush
