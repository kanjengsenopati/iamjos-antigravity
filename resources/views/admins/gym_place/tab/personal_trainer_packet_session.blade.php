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
        <button class="nav-link text-dark active" id="level-tab" data-bs-toggle="tab" data-bs-target="#level-tab-pane"
            type="button" role="tab" aria-controls="level-tab-pane" aria-selected="false">
            Level Coach</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="packet-tab" data-bs-toggle="tab" data-bs-target="#packet-tab-pane"
            type="button" role="tab" aria-controls="packet-tab-pane" aria-selected="true">
            Paket Coach
        </button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="level-tab-pane" role="tabpanel" aria-labelledby="level-tab" tabindex="0">
        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
            <div>
                <h4>List Level Coach</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a type="button" class="btn btn-primary btn-sm btn-create"
                    href="{{ route('personal-trainer-level.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Level Coach</a>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-personal-trainer-level"
                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Level</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-dark fw-semibold"></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="packet-tab-pane" role="tabpanel" aria-labelledby="packet-tab" tabindex="0">
        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
            <div>
                <h4>List Paket Coach</h4>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a type="button" class="btn btn-primary btn-sm btn-create"
                    href="{{ route('personal-trainer-packet-session.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Paket Coach</a>
            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-personal-trainer-packet-session"
                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th class="w-125px">Thumbnail</th>
                        <th>Nama Paket</th>
                        <th>Level</th>
                        <th>Harga</th>
                        <th>Total Sesi</th>
                        <th>Periode Berlangganan</th>
                        <th>Status</th>
                        <th>Status Publish</th>
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
    $(document).ready(function() {
        personalTrainerLevel();
        });

        $('#nav_tab_personal_trainer_packet_session').on('click', function() {
            personalTrainerLevel();
        })
        $('#level-tab').on('click', function() {
            personalTrainerLevel();
        })

        $('#packet-tab').on('click', function() {
            personalTrainerPacketSession();
        })

        function personalTrainerPacketSession() {
            var table = $('#datatable-personal-trainer-packet-session').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('personal-trainer-packet-session.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-personal-trainer-packet-session tbody').empty();
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
                        data: 'thumbnail',
                        name: 'thumbnail',
                        render: function(data, type, row) {
                            if (data == null) {
                                return `<span class="symbol-label fs-2x fw-bold text-primary bg-light-primary">
                                    ${row.name.charAt(0)}</span>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px w-50px img-thumnail" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        render: function(data, type, row, meta) {
                            return `<p>${data} <br> <span class="text-italic en-feature text-primary">${row.name_en}</span></p>`;
                        }
                    },
                    {
                        data: 'personal_trainer_level.name',
                        name: 'personal_trainer_level.name'
                    },
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data, type, row, meta) {
                            return row.discount_price > 0 ? `<p>Rp
                        <s>${data}</s> ${row.discount_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")} <br>
                        <small><i>Periode Diskon: ${row.start_date_discount} ${row.start_time_discount} s/d ${row.end_date_discount} ${row.end_time_discount}</i>
                        </p></small>` : 'Rp' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                        }
                    },
                    {
                        data: 'total_session',
                        name: 'total_session',
                        render: function(data, type, row, meta) {
                            return data ? `<p>${data} Sesi</p>` : 'Tidak Dibatasi';
                        }
                    },
                    {
                        data: 'training_period',
                        name: 'training_period',
                        render: function(data, type, row, meta) {
                            return data ? `<p>${data} Hari</p>` : 'Aktif Selamanya';
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
                    data: 'is_published',
                    name: 'is_published',
                    render: function(data, type, row) {
                    if (data) {
                    return `<span class="badge badge-light-success">Paket PT di Tampilkan Publik/ di Aplikasi</span>`;
                    } else {
                    return `<span class="badge badge-light-warning">Paket PT di Sembunyikan</span>`;
                    }
                    },
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Menyembunyikan tabel selama proses loading
            table.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-personal-trainer-packet-session tbody').empty();
            });

            // Menampilkan tabel setelah data selesai dimuat
            table.on('draw.dt', function() {
                $('#datatable-personal-trainer-packet-session').fadeIn();
            });
        }

        function personalTrainerLevel() {
            var table = $('#datatable-personal-trainer-level').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('personal-trainer-level.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-personal-trainer-level tbody').empty();
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
                        name: 'name'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ]
            });

            // Menyembunyikan tabel selama proses loading
            table.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-personal-trainer-level tbody').empty();
            });

            // Menampilkan tabel setelah data selesai dimuat
            table.on('draw.dt', function() {
                $('#datatable-personal-trainer-level').fadeIn();
            });
        }
</script>
@endpush