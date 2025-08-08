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
        <button class="nav-link text-dark active" id="class-tab" data-bs-toggle="tab" data-bs-target="#class-tab-pane"
            type="button" role="tab" aria-controls="class-tab-pane" aria-selected="false">List Kelas</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="category-tab" data-bs-toggle="tab" data-bs-target="#category-tab-pane"
            type="button" role="tab" aria-controls="category-tab-pane" aria-selected="true">List Kategori
            Kelas</button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade " id="category-tab-pane" role="tabpanel" aria-labelledby="category-tab" tabindex="0">
        <div class="border-0 pt-6 gap-4 d-flex mb-3 flex-wrap justify-content-between align-items-center">
            <h4>List Kategori Kelas</h4>
            <div class="d-flex gap-2">
                <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
                    href="{{ route('gym-class-category.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Kategori Kelas</a>
            </div>
        </div>
        <div>
            <table id="datatable-gym-class-category"
                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th class="min-w-125px">Kategori</th>
                        <th class="min-w-125px">Kategori (English)</th>
                        <th class="min-w-125px">Dibuat Pada</th>
                        <th class="text-center min-w-100px">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-dark fw-semibold"></tbody>
                </tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade show active" id="class-tab-pane" role="tabpanel" aria-labelledby="class-tab" tabindex="0">
        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
            <h4>List Kelas</h4>
            <div class="d-flex flex-wrap gap-2">
                <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
                    href="{{ route('gym-class.create', ['gym_place_id' => $gymPlace->id]) }}">
                    <i class="ki-duotone ki-plus fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>Kelas
                </a>
                <a type="button" class="btn btn-sm btn-primary text-nowrap" onclick="importGymClass()">
                    <i class="ki-duotone ki-exit-down fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Import
                </a>
                <a href="{{ route('gym-class.export-excel', $gymPlace->id) }}"
                    class="btn btn-primary btn-sm text-nowrap">
                    <i class="ki-duotone ki-exit-up fs-3">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    Export Excel</a>
            </div>
        </div>
        <!--begin::Table-->
        <div>
            <table id="datatable-gym-class" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                <thead>
                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Thumbnail</th>
                        <th class="min-w-100px">Nama Kelas</th>
                        {{-- <th>Harga</th> --}}
                        <th>Trainer</th>
                        <th>Level</th>
                        <th>Kategori</th>
                        <th>Kuota Peserta</th>
                        <th>Hari</th>
                        <th>Masa Berlaku</th>
                        <th>Status</th>
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
    // Panggil fungsi tableClass saat halaman pertama kali dimuat
        $(document).ready(function() {
            tableClass();
        });

        $('#nav_tab_gym_class').on('click', function() {
            tableClass();
        });

        $('#class-tab').on('click', function() {
            tableClass();
        });

        $('#category-tab').on('click', function() {
            tableX();
        });

        var tableClassInstance;
        var tableXInstance;

        function tableClass() {
            if (tableClassInstance) {
                tableClassInstance.ajax.reload();
                return;
            }

            tableClassInstance = $('#datatable-gym-class').DataTable({
                // ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('gym-class.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-gym-class tbody').empty();
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
                columns: [
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'thumbnail',
                        name: 'thumbnail',
                        render: function(data, type, row) {
                            if (data == null) {
                                return `
                                <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center">
                                    <span class="fs-2x fw-bold text-primary text-capitalize">
                                        ${row.name.charAt(0)}</span>
                                </div>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px w-75px object-fit-cover img-thumbnail" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        responsivePriority: -2,
                        render: function(data, type, row, meta) {
                            return `<p>${data} <br> <span class="text-italic en-feature text-primary">${row.name_en}</span></p>`;
                        }
                    },
                    // {
                    //     data: 'price',
                    //     name: 'price',
                    //     render: function(data, type, row, meta) {
                    //         if (row.type == 'PAID') {
                    //             return row.discount_price > 0 ? `<p>Rp
                    //             <s>${data}</s> ${row.discount_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")}` : 'Rp' +
                    //                 data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    //         } else {
                    //             return '<span class="badge badge-success">Gratis</span>';
                    //         }
                    //     }
                    // },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return row.personal_trainer_name;
                        }
                    },
                    {
                        data: 'level',
                        name: 'level',
                    },
                    {
                        data: 'gym_class_category.name',
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return `<p>${data} <br> <span class="text-italic en-feature text-primary">${row.gym_class_category.name_en}</span></p>`;
                        }
                    },
                    {
                        data: 'quota',
                        name: 'quota',
                        render: function(data, type, row, meta) {
                            return `<p>${data}</p>`;
                        }
                    },
                    {
                        data: 'day',
                        name: 'day',
                        render: function(data, type, row, meta) {
                            return `<p>${data} <br> ${row.start_time} - ${row.end_time}</p>`;
                        }
                    },
                    {
                        data: 'date',
                        name: 'date',
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

            tableClassInstance.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-gym-class tbody').empty();
            });

            tableClassInstance.on('draw.dt', function() {
                $('#datatable-gym-class').fadeIn();
            });
        }

        function tableX() {
            if (tableXInstance) {
                tableXInstance.ajax.reload();
                return;
            }

            tableXInstance = $('#datatable-gym-class-category').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('gym-class-category.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-gym-class-category tbody').empty();
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
                columns: [
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        responsivePriority: -2,
                    },
                    {
                        data: 'name_en',
                        name: 'name_en',
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true,
                        responsivePriority: -1,
                    },
                ]
            });

            tableXInstance.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-gym-class-category tbody').empty();
            });

            tableXInstance.on('draw.dt', function() {
                $('#datatable-gym-class-category').fadeIn();
            });
        }
</script>
@endpush