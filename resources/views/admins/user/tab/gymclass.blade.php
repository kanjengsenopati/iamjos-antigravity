<div class="d-flex gap2 align-items-center justify-content-end">
    <div class="mb-4">

    </div>
</div>
<div class="table-responsive">
    <table id="datatable-gymclass-user" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Keterangan</th>
                {{-- <th class="text-center" style="width: 10%">Aksi</th> --}}
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@push('js')
<script>
    $(document).ready(function() {
        var tableAdditionalFile = $('#datatable-gymclass-user').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('gymclass-user') }}",
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
                    data: 'class_name',
                    name: 'class_name',
                },
                {
                    data: 'translated_status',
                    name: 'translated_status',
                    searchable: false,
                },
                {
                    data: 'cancel_reason',
                    name: 'cancel_reason',
                }
                // {
                //     data: 'action',
                //     name: 'action',
                //     orderable: false,
                //     searchable: false,
                //     responsivePriority: -1,
                // }
            ]
        });
    })
</script>
@endpush