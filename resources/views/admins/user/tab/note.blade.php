<div class="d-flex gap2 align-items-center justify-content-end">
    <div class="mb-4">
        <a type="button" class="btn btn-primary btn-sm" onclick="createNotes()">
            <i class="fa fa-plus"></i> Notes
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-notes" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th class="text-center" style="width: 15%">Tanggal</th>
                <th class="text-center">Catatan</th>
                <th class="text-center" style="width: 10%">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@push('js')
    <script>
        $(document).ready(function() {
            var tableNotes = $('#datatable-notes').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('notes.index') }}",
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
                        data: 'description',
                        name: 'description',
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
        })
    </script>
@endpush
