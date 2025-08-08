<div class="border-0 pt-6 d-flex mb-3 gap-4 flex-wrap justify-content-between align-items-center">
    <div>
        <h4>List Membership</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
            href="{{ route('membership.create', ['gym_place_id' => $gymPlace->id]) }}">
            <i class="ki-duotone ki-plus fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>Membership
        </a>
        <a type="button" class="btn btn-sm btn-primary text-nowrap" onclick="importMembership()">
            <i class="ki-duotone ki-exit-down fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Import
        </a>
        <a href="{{ route('membership.export-excel', $gymPlace->id) }}" class="btn btn-primary btn-sm text-nowrap">
            <i class="ki-duotone ki-exit-up fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            Export Excel</a>
    </div>
</div>
<div>
    <table id="datatable-membership" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th class="w-125px">Thumbnail</th>
                <th>Nama Paket</th>
                <th>Harga</th>
                <th>Total Sesi</th>
                <th>Periode Berlangganan</th>
                <th>Status</th>
                <th>Status Publish</th>
                <th>Tempat Gym</th>
                <th>Benefit</th>
                <th class="text-center min-w-100px">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>
@push('js')
<script>
    // Panggil fungsi tableMembership saat halaman pertama kali dimuat
        $(document).ready(function() {
            tableMembership();
        });

        $('#nav_tab_membership').on('click', function() {
            tableMembership();
        });

        var tableMembershipInstance;

        function tableMembership() {
            // Cek apakah instance DataTable sudah ada
            if (tableMembershipInstance) {
                tableMembershipInstance.ajax.reload();
                return;
            }

            // Inisialisasi DataTable jika belum ada
            tableMembershipInstance = $('#datatable-membership').DataTable({
                // ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('membership.index') }}",
                    type: 'GET',
                    data: {
                        gym_place_id: '{{ $gymPlace->id }}'
                    },
                    beforeSend: function() {
                        $('#datatable-membership tbody').empty();
                    }
                },
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
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
                    {
                        data: 'price',
                        name: 'price',
                        render: function(data, type, row, meta) {
                            return row.discount_price > 0 ? `<div class="w-100px">
                            <p>Rp
                            <s>${data}</s> Rp${row.discount_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")} <br>
                            <small><i>Periode Diskon: ${row.start_date_discount} ${row.start_time_discount} s/d ${row.end_date_discount} ${row.end_time_discount}</i>
                            </small>
                            </p>
                        </div>` : 'Rp' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
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
                                return `<span class="badge badge-light-success">Membership di Tampilkan Publik/ di Aplikasi</span>`;
                            } else {
                                return `<span class="badge badge-light-warning">Membership di Sembunyikan</span>`;
                            }
                        },
                    },
                    {
                        data: 'gym_place.name',
                        name: 'gym_place.name',
                    },
                    {
                        data: null,
                        render: function(data, type, row, meta) {
                            let li = '';
                            for (benefit of row?.membership_benefits ?? []) {
                                li += `<li><small>${benefit.name}</small></li>`;
                            }
                            let liEn = '';
                            for (benefit of row?.en_membership_benefits ?? []) {
                                liEn +=
                                    `<li class="text-primary en-feature"><small><i>${benefit.name}</i></small></li>`;
                            }
                            return `<ul class="w-100px">${li} ${liEn}</ul>`;
                        }
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
        }
</script>
@endpush