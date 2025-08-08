@push('css')
<style>
    #myTab .nav-link.active {
        color: var(--bs-primary) !important;
        font-weight: 500;
    }
</style>
@endpush

<div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
    <div>
        <h4>List Paket Fisioterapi</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a type="button" class="btn btn-primary btn-sm btn-create"
            href="{{ route('physiotherapy-packet-session.create', ['gym_place_id' => $gymPlace->id]) }}">
            <i class="ki-duotone ki-plus fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i> Paket Fisioterapi
        </a>
    </div>
</div>

<div class="table-responsive">
    <table id="datatable-physiotherapy-packet-session"
        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th class="w-125px">Thumbnail</th>
                <th>Nama Paket</th>
                <th>Level</th>
                <th>Harga</th>
                <th>Harga Non Member</th>
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

@push('js')
<script>
    $(document).ready(function() {
        phisiotherapyPacketSession();
    });

    function phisiotherapyPacketSession() {
        var table = $('#datatable-physiotherapy-packet-session').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('physiotherapy-packet-session.index') }}",
                type: 'GET',
                data: {
                    gym_place_id: '{{ $gymPlace->id }}'
                },
                beforeSend: function() {
                    $('#datatable-physiotherapy-packet-session tbody').empty();
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'></i>",
                    previous: "<i class='fa fa-angle-left'></i>"
                },
                loadingRecords: "Loading...",
                processing: "Processing...",
            },
            columns: [
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'thumbnail',
                    name: 'thumbnail',
                    render: function(data, type, row) {
                        return data 
                            ? `<img src="${data}" alt="image" class="h-50px w-50px img-thumbnail" />`
                            : `<span class="symbol-label fs-2x fw-bold text-primary bg-light-primary">${row.name.charAt(0)}</span>`;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data, type, row) {
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
                },
                {
                    data: 'non_member_price',
                    name: 'non_member_price',
                },
                {
                    data: 'total_session',
                    name: 'total_session',
                    render: function(data) {
                        return data ? `<p>${data} Sesi</p>` : 'Tidak Dibatasi';
                    }
                },
                {
                    data: 'training_period',
                    name: 'training_period',
                    render: function(data) {
                        return data ? `<p>${data} Hari</p>` : 'Aktif Selamanya';
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data) {
                        return data 
                            ? `<span class="badge badge-light-success">Aktif</span>` 
                            : `<span class="badge badge-light-warning">Non Aktif</span>`;
                    }
                },
                {
                    data: 'is_published',
                    name: 'is_published',
                    render: function(data) {
                        return data 
                            ? `<span class="badge badge-light-success">Paket di Tampilkan Publik/ di Aplikasi</span>` 
                            : `<span class="badge badge-light-warning">Paket di Sembunyikan</span>`;
                    }
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

        table.on('preXhr.dt', function() {
            $('#datatable-physiotherapy-packet-session tbody').empty();
        });

        table.on('draw.dt', function() {
            $('#datatable-physiotherapy-packet-session').fadeIn();
        });
    }
</script>
@endpush