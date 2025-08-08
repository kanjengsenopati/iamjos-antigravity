<div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
    <div>
        <h4>List Personal Trainer External</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
            href="{{ route('personal-trainer-external.create', ['gym_place_id' => $gymPlace->id]) }}">
            <i class="ki-duotone ki-plus fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>Personal Trainer External
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-personal-trainer-external"
        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th style="width: 15%">Avatar Image</th>
                <th style="width: 20%">Nama</th>
                <th style="width: 40%">Bio</th>
                <th class="text-center min-w-100px">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#datatable-personal-trainer-external').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('personal-trainer-external.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-personal-trainer-external tbody').empty();
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
                        data: 'avatar',
                        name: 'avatar'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'bio',
                        name: 'bio'
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
@endpush
