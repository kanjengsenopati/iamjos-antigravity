{{-- <div class="border-0 pt-6 d-flex mb-3 justify-content-between align-items-center">
    <div>
        <h4>List Riwayat Membership</h4>
    </div>
</div>

<!--begin::Table-->
<div>
    <table id="datatable-membership-history" class="table table-hover align-middle table-row-dashed">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Nama User</th>
                <th>Membership</th>
                <th>Tanggal Berlaku</th>
                <th>Status</th>
                <th class="text-center min-w-100px">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>
<!--end::Table-->
@push('js')
<script>
    $(document).ready(function() {
        var table = $('#datatable-membership-history').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('membership-history.index') }}",
                type: 'GET',
                data: {
                    gym_place_id: '{{$gymPlace->id}}'
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
                    data: 'user.name',
                    name: 'user.name',
                    responsivePriority: -2
                },
                {
                    data: 'membership.name',
                    name: 'membership.name'
                },
                {
                    data: null,
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return `${row.start_active_date} ~ ${row.expiry_date}`;
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        if (data) {
                            return `<span class="badge badge-light-success">Aktif</span>`;
                        } else {
                            return `<span class="badge badge-light-warning">Non Aktif</span>`;
                        }
                    },
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1
                },
            ]
        });
    });
</script>
@endpush --}}
