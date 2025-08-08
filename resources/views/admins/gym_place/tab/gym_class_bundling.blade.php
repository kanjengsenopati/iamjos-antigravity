<div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
    <div>
        <h4>List Coach Plus</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
            href="{{ route('gym-class-bundling.create', ['gym_place_id' => $gymPlace->id]) }}">
            <i class="ki-duotone ki-plus fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>Coach Plus
        </a>
        <a type="button" class="btn btn-sm btn-primary text-nowrap" onclick="importGymClassBundling()">
            <i class="ki-duotone ki-exit-down fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Import
        </a>
        <a href="{{ route('gym-class-bundling.export-excel', $gymPlace->id) }}"
            class="btn btn-primary text-nowrap btn-sm">
            <i class="ki-duotone ki-exit-up fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Export Excel</a>
    </div>
</div>
<div>
    <table id="datatable-gym-class-bundling" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th class="w-125px">Thumbnail</th>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Total Sesi</th>
                <th>Periode Berlangganan</th>
                <th class="min-w-100px">Benefit</th>
                <th>Status</th>
                <th>Status Publish</th>
                <th class="text-center min-w-100px">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>
@push('js')
<script>
    $(document).ready(function() {
    tableBundling();
    });
    $('#nav_tab_gym_class_bundling').on('click', function() {
            // Cek jika DataTable sudah ada dan hancurkan jika perlu
            if ($.fn.DataTable.isDataTable('#datatable-gym-class-bundling')) {
                $('#datatable-gym-class-bundling').DataTable().destroy();
                $('#datatable-gym-class-bundling tbody').empty();
            }
            tableBundling();
        });

        function tableBundling() {
            var table = $('#datatable-gym-class-bundling').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('gym-class-bundling.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-gym-class-bundling tbody').empty();
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
                                return `<span class="symbol-label fs-2x fw-bold text-primary bg-light-primary">${row.name.charAt(0)}</span>`;
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
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data, type, row, meta) {
                            return row.discount_price > 0 ? `<p>Rp <s>${data}</s> ${row.discount_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")} <br> <small><i>Periode Diskon: ${row.start_date_discount} ${row.start_time_discount} s/d ${row.end_date_discount} ${row.end_time_discount}</i></p></small>` : 'Rp' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
                        data: 'period',
                        name: 'period',
                        render: function(data, type, row, meta) {
                            return '<small>Membership: ' + (row.period ? row.period + ' Hari' : 'Aktif Selamanya') + '<br>Coach: ' + row.period_personal_trainer + ' Hari</small></i>';
                        }
                    },
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            let li = '';
                            for (let benefit of row?.gym_class_bundling_benefits ?? []) {
                                li += `<li><small>${benefit.name}</small></li>`;
                            }
                            let liEn = '';
                            for (let benefit of row?.en_gym_class_bundling_benefits ?? []) {
                                liEn += `<li class="text-primary en-feature"><small><i>${benefit.name}</i></small></li>`;
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
                    data: 'is_published',
                    name: 'is_published',
                    render: function(data, type, row) {
                    if (data) {
                    return `<span class="badge badge-light-success">Coach Plus di Tampilkan Publik/ di Aplikasi</span>`;
                    } else {
                    return `<span class="badge badge-light-warning">Coach Plus di Sembunyikan</span>`;
                    }
                    },
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -1,
                    },
                ]
            });
        }
</script>
@endpush