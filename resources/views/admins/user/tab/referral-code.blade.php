<div class="d-flex gap2 align-items-center">
    <div class="mb-4">
        <h2 class="text-capitalize mb-4 ms-1">Referral Code</h2>
        <p class="text-capitalize mb-1 ms-1">My Referral Code : {{ $user->membership_user?->member_id ?? "-" }}</p>
        <p class="text-capitalize mb-1 ms-1">Status Referral Code : 
            <?= $user->active_memberships()->exists() ? '<span class="badge badge-success">Aktif</span>' : '<span class="badge badge-warning">Tidak Aktif</span>'; ?>
        </p>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-referral-code" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th class="min-w-30px">No</th>
                <th>Tanggal Register</th>
                <th class="min-w-100px">Referral Kode</th>
                <th class="min-w-100px">Nama User Pengguna</th>
                <th class="min-w-100px">Nama User Pemilik</th>
                <th class="min-w-50px">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@push('js')
<script>
    $(document).ready(function() {
        
        var tableAdditionalFile = $('#datatable-referral-code').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('referral-code.user', $user->id) }}"
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
                { data: 'created_at', name: 'created_at', sortable: true },
                { data: 'referral_code', name: 'referral_code', sortable: false },
                { data: 'user.name', name: 'user.name', orderable: true, searchable: true },
                { data: 'owner.name', name: 'owner.name', orderable: true, searchable: true },
                {
                    data: 'action',
                    name: 'action',
                    sortable: false,
                    searchable: false,
                    responsivePriority: -1
                },
            ]
        });
    })
</script>
@endpush