<div class="border-0 pt-6 d-flex mb-3 justify-content-between align-items-center">
    <div>
        <h4>List Sesi Coach</h4>
    </div>
    <div class="d-flex gap-2">
        <a type="button" class="btn btn-primary btn-sm btn-create"
            href="{{ route('personal-trainer-packet-session.create', ['personal_trainer_id' => $personalTrainer->id]) }}">
            <i class="ki-duotone ki-plus fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>Sesi Coach</a>
    </div>
</div>

<table id="datatable-personal-trainer-packet-session"
    class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
    <thead>
        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
            <th style="width: 5%">No</th>
            <th class="w-125px">Thumbnail</th>
            <th>Nama Paket</th>
            <th>Harga</th>
            <th>Total Sesi</th>
            <th>Periode Berlangganan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody class="text-dark fw-semibold"></tbody>
</table>

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#datatable-personal-trainer-packet-session').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('personal-trainer-packet-session.get-by-level') }}",
                    type: 'GET',
                    data: {
                        personal_trainer_level_id: '{{ $personalTrainer->personal_trainer_level_id }}'
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
                    }
                ]
            });
        });
    </script>
@endpush
