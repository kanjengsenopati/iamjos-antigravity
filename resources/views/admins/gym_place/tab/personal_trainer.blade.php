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
        <button class="nav-link text-dark active" id="internal-tab" data-bs-toggle="tab" data-bs-target="#internal-tab-pane"
            type="button" role="tab" aria-controls="internal-tab-pane" aria-selected="false">
            Coach Internal
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="external-tab" data-bs-toggle="tab" data-bs-target="#external-tab-pane"
            type="button" role="tab" aria-controls="external-tab-pane" aria-selected="true">
            Coach External
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="timeoff-tab" data-bs-toggle="tab" data-bs-target="#timeoff-tab-pane"
            type="button" role="tab" aria-controls="timeoff-tab-pane" aria-selected="true">
            Cuti Coach
        </button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="internal-tab-pane" role="tabpanel" aria-labelledby="internal-tab"
        tabindex="0">
        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
            <div>
                <h4>List Coach</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
                    href="{{ route('personal-trainer.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Coach
                </a>
                <a type="button" class="btn btn-sm btn-primary text-nowrap" onclick="importPersonalTrainer()">
                    <i class="ki-duotone ki-exit-down fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Import
                </a>
                <a href="{{ route('personal-trainer.export-excel', $gymPlace->id) }}"
                    class="btn btn-primary btn-sm text-nowrap">
                    <i class="ki-duotone ki-exit-up fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Export Excel</a>
            </div>
        </div>
        <div>
            <table id="datatable-personal-trainer"
                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Avatar</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>Max Member</th>
                        <th>Pengalaman</th>
                        <th>Benefit</th>
                        <th>Status</th>
                        <th class="text-center min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-dark fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade " id="external-tab-pane" role="tabpanel" aria-labelledby="external-tab" tabindex="0">
        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
            <div>
                <h4>List Coach External</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
                    href="{{ route('personal-trainer-external.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Coach External
                </a>
            </div>
        </div>
        <div>
            <table id="datatable_personal_trainer_external"
                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th style="width: 15%">Avatar</th>
                        <th style="width: 20%">Nama</th>
                        <th style="width: 40%">Bio</th>
                        <th class="text-center min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-dark fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="timeoff-tab-pane" role="tabpanel" aria-labelledby="timeoff-tab" tabindex="0">
        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
            <div>
                <h4>Cuti Coach</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
                    href="{{ route('personal-trainer-timeoff.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Cuti PT
                </a>
            </div>
        </div>
        <div>
            <table id="datatable_personal_trainer_timeoff"
                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th style="width: 15%">Coach</th>
                        <th style="width: 20%">Tanggal Mulai</th>
                        <th style="width: 20%">Tanggal Selesai</th>
                        <th style="width: 40%">Keterangan</th>
                        <th class="text-center min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-dark fw-semibold"></tbody>
            </table>
        </div>
    </div>
</div>

@push('js')
    <script>
        function doImportPersonalTrainer() {
            const fileInput = document.getElementById('file_import_personal_trainer');

            let loading = $('#loading-import-pt');
            let btn = $('#btn-import-pt');

            loading.removeClass('d-none');
            btn.attr('disabled', 'disabled')
            // Check if a file is selected
            if (!fileInput.files.length) {
                Swal.fire('Error', 'Please select a file to import', 'error');
                return;
            }

            const formData = new FormData();

            // Append the selected file to the FormData
            formData.append('file', fileInput.files[0]);
            formData.append('is_force_import', $('#is_force_import').val())

            // Potentially add the gym_place_id if necessary
            const gymPlaceId = "{{ $gymPlace->id }}"; // Access the gymPlace ID from your Blade template
            if (gymPlaceId) {
                formData.append('gym_place_id', gymPlaceId);
            }

            $.ajax({
                url: "{{ route('personal-trainer.import') }}",
                type: 'POST',
                data: formData,
                processData: false, // Prevent jQuery from automatically transforming the data into a query string
                contentType: false, // Tell jQuery not to set contentType
                success: function(data) {
                    Swal.fire('Success', data.message, 'success');
                    $('#is_force_import').val(0);
                    window.location.href =
                        "{{ route('gym-place.show', $gymPlace->id . '?tab=personal_trainer') }}"
                },
                error: function(error) {
                    loading.addClass('d-none');
                    btn.removeAttr('disabled')
                    console.log(error)
                    if (error.status == 400) {
                        Swal.fire('Warning', error.responseJSON.message, 'warning');
                        $('#is_force_import').val(1);
                        $('#title-import-personal-trainer').text(error.responseJSON.message);
                    } else {
                        Swal.fire('Error', error.responseJSON.message, 'error');
                    }
                }
            });
        }


        $(document).ready(function() {
            table();
        });

        $('#internal-tab').on('click', function() {
            table();
        });
        $('#nav_tab_personal_trainer').on('click', function() {
            table();
        });

        function table() {
            var table = $('#datatable-personal-trainer').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('personal-trainer.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-personal-trainer tbody').empty();
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
                        name: 'avatar',
                        render: function(data, type, row) {
                            if (data == null) {
                                return `
                            <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center">
                                <span class="fs-2x fw-bold text-primary text-capitalize">
                                    ${row.name.charAt(0)}</span>
                            </div>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px w-75px object-fit-cover img-thumnail" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        responsivePriority: -3
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'personal_trainer_level.name',
                        name: 'personal_trainer_level.name'
                    },
                    {
                        data: 'max_member',
                        name: 'max_member'
                    },
                    {
                        data: 'start_experience_year',
                        name: 'start_experience_year',
                        render: function(data, type, row) {
                            return `Menjadi PT Sejak ${data} (${row.experience_year} Tahun)`;
                        }
                    },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            let li = '';
                            for (benefit of row?.personal_trainer_benefits ?? []) {
                                li += `<li><small>${benefit.name}</small></li>`;
                            }
                            let liEn = '';
                            for (benefit of row?.personal_trainer_en_benefits ?? []) {
                                liEn +=
                                    `<li class="text-primary en-feature"><small><i>${benefit.name}</i></small></li>`;
                            }
                            return `<ul>${li} ${liEn}</ul>`;
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

            // Menyembunyikan tabel selama proses loading
            table.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-personal-trainer tbody').empty();
            });

            // Menampilkan tabel setelah data selesai dimuat
            table.on('draw.dt', function() {
                $('#datatable-personal-trainer').fadeIn();
            });
        }
    </script>
    <script>
        $('#external-tab').on('click', function() {
            tableExternal();
        });

        function tableExternal() {
            var tableExternal = $('#datatable_personal_trainer_external').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('personal-trainer-external.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable_personal_trainer_external tbody').empty();
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

            // Menyembunyikan tabel selama proses loading
            tableExternal.on('preXhr.dt', function(e, settings, data) {
                $('#datatable_personal_trainer_external tbody').empty();
            });

            // Menampilkan tabel setelah data selesai dimuat
            tableExternal.on('draw.dt', function() {
                $('#datatable_personal_trainer_external').fadeIn();
            });
        }
    </script>

    <script>
        $('#timeoff-tab').on('click', function() {
            tableTimeoff();
        });

        function tableTimeoff() {
            var tableTimeoff = $('#datatable_personal_trainer_timeoff').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('personal-trainer-timeoff.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable_personal_trainer_timeoff tbody').empty();
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
                        data: 'personal_trainer.name',
                        name: 'personal_trainer.name'
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'description',
                        name: 'description'
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

            // Menyembunyikan tabel selama proses loading
            tableTimeoff.on('preXhr.dt', function(e, settings, data) {
                $('#datatable_personal_trainer_timeoff tbody').empty();
            });

            // Menampilkan tabel setelah data selesai dimuat
            tableTimeoff.on('draw.dt', function() {
                $('#datatable_personal_trainer_timeoff').fadeIn();
            });
        }
    </script>
@endpush
