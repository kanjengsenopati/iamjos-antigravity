@push('css')
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/start/jquery-ui.css"/>
<style>
    #myTab.nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        font-weight: 500;
    }

    .swal2-overflow {
        overflow-x: visible;
        overflow-y: visible;
    }
</style>
@endpush
<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark active" id="internal-tab" data-bs-toggle="tab"
            data-bs-target="#membership-tab-pane" type="button" role="tab" aria-controls="membership-tab-pane"
            aria-selected="false">
            Membership
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="coach-plus-tab" data-bs-toggle="tab"
            data-bs-target="#coach-plus-tab-pane" type="button" role="tab" aria-controls="coach-plus-tab-pane"
            aria-selected="true">
            Paket Coach Plus
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="coach-tab" data-bs-toggle="tab" data-bs-target="#coach-tab-pane"
            type="button" role="tab" aria-controls="coach-tab-pane" aria-selected="true">
            Coach
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="sauna-tab" data-bs-toggle="tab" data-bs-target="#sauna-tab-pane"
            type="button" role="tab" aria-controls="coach-tab-pane" aria-selected="true">
            Sauna
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link text-dark" id="ice-bath-tab" data-bs-toggle="tab" data-bs-target="#ice-bath-tab-pane"
            type="button" role="tab" aria-controls="coach-tab-pane" aria-selected="true">
            Ice Bath
        </button>
    </li>
</ul>
<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active" id="membership-tab-pane" role="tabpanel" aria-labelledby="membership-tab"
        tabindex="0">
        <div class="d-flex gap2 align-items-center justify-content-end">
            <div class="mb-4">

            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-membership" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Nama Membership</th>
                        <th>Tanggal Mulai Membership</th>
                        <th>Tanggal Selesai Membership</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade " id="coach-plus-tab-pane" role="tabpanel" aria-labelledby="coach-plus-tab" tabindex="0">
        <div class="d-flex gap2 align-items-center justify-content-end">
            <div class="mb-4">

            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-coach-plus" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Nama Coach Plus</th>
                        <th>Tanggal Mulai Coach Plus</th>
                        <th>Tanggal Selesai Coach Plus</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="coach-tab-pane" role="tabpanel" aria-labelledby="coach-tab" tabindex="0">
        <div class="d-flex gap2 align-items-center justify-content-end">
            <div class="mb-4">

            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-coach" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th>No</th>
                        <th>Nama Paket</th>
                        <th>Coach</th>
                        <th>Tanggal Mulai Paket</th>
                        <th>Tanggal Selesai Paket</th>
                        <th>Sesi</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="sauna-tab-pane" role="tabpanel" aria-labelledby="sauna-tab" tabindex="0">
        <div class="d-flex gap2 align-items-center justify-content-end">
            <div class="mb-4">

            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-sauna-history" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
    <div class="tab-pane fade" id="ice-bath-tab-pane" role="tabpanel" aria-labelledby="ice-bath-tab" tabindex="0">
        <div class="d-flex gap2 align-items-center justify-content-end">
            <div class="mb-4">

            </div>
        </div>
        <div class="table-responsive">
            <table id="datatable-ice-bath-history" class="table table-striped border rounded gy-5 gs-7">
                <thead>
                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                        <th style="width: 5%">No</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
{{-- {{ dd(url('user/membership-coach-history') . "/" . $user->id . '?tab=membership' ) }} --}}
@push('js')
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<script>
    $(document).ready(function() {
        var tableMembership = $('#datatable-membership').DataTable({
            // ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: "{{ url('user/membership-coach-history') }}"+"/{{ $user->id . '?tab=membership' }}",
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
                    data: 'start_active_date',
                    name: 'start_active_date',
                },
                {
                    data: 'expiry_date',
                    name: 'expiry_date',
                },
                {
                    data: 'is_active',
                    name: 'is_active',
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

        var tableCoachPlus = $('#datatable-coach-plus').DataTable({
            // ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: "{{ url('user/membership-coach-history') }}"+"/{{ $user->id . '?tab=coach-plus' }}",
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
                    data: 'start_active_date',
                    name: 'start_active_date',
                },
                {
                    data: 'expiry_date',
                    name: 'expiry_date',
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

        var tableCoach = $('#datatable-coach').DataTable({
            // ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: "{{ url('user/membership-coach-history') }}"+"/{{ $user->id . '?tab=coach' }}",
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
                    data: 'coach_name',
                    name: 'coach_name',
                },
                {
                    data: 'start_active_date',
                    name: 'start_active_date',
                },
                {
                    data: 'expiry_date',
                    name: 'expiry_date',
                },
                {
                    data: 'session',
                    name: 'session',
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

        var tableSauna = $('#datatable-sauna-history').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('user.show', $user->id) }}",
                data: function(d) {
                    d.type = 'sauna'
                },
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
                data: 'status',
                name: 'status',
                responsivePriority: -1
            },
            ]}
        );

        var tableIceBath = $('#datatable-ice-bath-history').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('user.show', $user->id) }}",
                data: function(d) {
                    d.type = 'ice-bath'
                },
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
                    data: 'status',
                    name: 'status',
                    responsivePriority: -1
                },
            ]
        });

        $(document).on('click', '.btn-update-expiry-date', function(e) {
            var form = $("#" + e.target.dataset.id);
            Swal.fire({
                title: 'Anda yakin akan mengubah tanggal expiry date?',
                icon: "warning",
                html: '<input id="datepicker" name="datepicker" class="swal2-input" required>',
                customClass: 'swal2-overflow',
                didOpen: function() {
                    $('#datepicker').datepicker({
                        dateFormat: 'yy-mm-dd'
                    });
                },
                preConfirm: () => {
                    const datepicker = Swal.getPopup().querySelector('#datepicker').value;
                    if (!datepicker) {
                        Swal.showValidationMessage('Tanggal expiry date harus diisi');
                    }
                    return datepicker;
                },
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-sm fw-semibold btn-primary',
                    cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'expiry_date',
                        value: $('#datepicker').val()
                    }).appendTo(form);
                    
                    $.ajax({
                        type: form.attr('method'),
                        url: form.attr('action'),
                        data: form.serialize(),
                        success: function(response) {
                            if (response.type == 'membership') {
                                tableMembership.ajax.reload();
                            } else if (response.type == 'coach-plus') {
                                tableCoachPlus.ajax.reload();
                            } else if (response.type == 'coach') {
                                tableCoach.ajax.reload();
                            }
                            Swal.fire({
                                title: 'Success',
                                text: response.message,
                                icon: 'success',
                                confirmButtonText: 'OK'
                            });
                        },
                        error: function(response) {
                            Swal.fire({
                                title: 'Error',
                                text: response.message,
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    });
                } else {
                    return false;
                }
            });
            return false;
        });
    })

</script>
@endpush