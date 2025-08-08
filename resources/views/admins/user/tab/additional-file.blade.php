<div class="d-flex gap2 align-items-center justify-content-end">
    <div class="mb-4">
        <a type="button" class="btn btn-primary btn-sm"
            href="{{ route('additional-file.create', ['user_id' => $user->id]) }}">
            <i class="fa fa-plus"></i> Additional File
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-additional-file" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th class="text-center">Nama File</th>
                <th class="text-center" style="width: 10%">Aksi</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
@push('js')
    <script>
        $(document).ready(function() {
            var tableAdditionalFile = $('#datatable-additional-file').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('additional-file.index') }}",
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
                        data: 'name',
                        name: 'name',
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
