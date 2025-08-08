@extends('layouts.master', ['title' => 'Detail Membership Recap', 'main' => 'Coach'])
@section('content')
    <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <x-alert.alert-validation />
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body--> 
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center justify-content-between mb-2">
                            <h3 class="text-capitalize mb-0">Membership Recap {{ $membership->name }}</h3>
                            <div class="text-end">
                                <form action="{{ route('membership.recap.export', $membership->gym_place_id) }}" id="membership_export" method="GET" enctype="multipart/form-data">
                                    @method('GET')
                                    <input type="hidden" name="membership_id" value="{{ $membership->id }}">
                                    <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                        <i class="ki-duotone ki-exit-up fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Export Excel
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <div class="card mt-6">
                    <div class="card-body v2">
                        <div class="mb-5 hover-scroll-x">
                            <div class="d-grid">
                                <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0 active"
                                            data-bs-toggle="tab" href="#kt_tab_membership_active">
                                            Membership Active
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary rounded-bottom-0"
                                            data-bs-toggle="tab" href="#kt_tab_membership_inactive">
                                            Membership Kadaluarsa
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="tab-content text-capitalize" id="myTabContent">
                            <div class="tab-pane fade show active" id="kt_tab_membership_active" role="tabpanel">
                                <div class="d-flex gap2 align-items-center justify-content-end">
                                    <div class="mb-4">

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="datatable-membership-active" class="table table-striped border rounded gy-5 gs-7">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                <th>No</th>
                                                <th>Nama User</th>
                                                <th>Telepon</th>
                                                <th>Harga Pembelian</th>
                                                <th>Membership ID</th>
                                                <th>Tanggal Mulai</th>
                                                <th>Tanggal Selesai</th>
                                                <th>Sisa Waktu Membership</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="kt_tab_membership_inactive" role="tabpanel">
                                <div class="d-flex gap2 align-items-center justify-content-end">
                                    <div class="mb-4">

                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="datatable-membership-inactive" class="table table-striped border rounded gy-5 gs-7">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                <th>No</th>
                                                <th>Nama User</th>
                                                <th>Telepon</th>
                                                <th>Harga Pembelian</th>
                                                <th>Membership ID</th>
                                                <th>Tanggal Mulai</th>
                                                <th>Tanggal Selesai</th>
                                                <th>Sisa Waktu Membership</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                            <!--end::Deactivate Account-->
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Post-->
                </div>
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        var tableMembership = $('#datatable-membership-active').DataTable({
            // ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: "{{ url('membership-recap') }}"+"/{{ $membership->id . '?tab=active' }}",
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
                // { 
                //     data: null,name: null, render: function(data, type, row) {
                //         return "<td><a href='{{ route('user.show', ':id') }}' target='_blank'>".replace(':id', row.user_id) + row.user?.name + "</a></td>";
                //     }
                // },
                { data: 'user.name', name: 'user.name' },
                { data: 'phone', name: 'phone' },
                { data: 'price', name: 'price' },
                { data: 'member_id', name: 'member_id' },
                { data: 'start_active_date', name: 'start_active_date' },
                { data: 'expiry_date', name: 'expiry_date' },
                { data: 'expiry_remaining', name: 'expiry_remaining' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });

        var tableCoachPlus = $('#datatable-membership-inactive').DataTable({
            // ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: "{{ url('membership-recap') }}"+"/{{ $membership->id . '?tab=inactive' }}",
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
                // { 
                //     data: null,name: null, render: function(data, type, row) {
                //         return "<td><a href='{{ route('user.show', ':id') }}' target='_blank'>".replace(':id', row.user_id) + row.user?.name + "</a></td>";
                //     }
                // },
                { data: 'user.name', name: 'user.name' },
                { data: 'phone', name: 'phone' },
                { data: 'price', name: 'price' },
                { data: 'member_id', name: 'member_id' },
                { data: 'start_active_date', name: 'start_active_date' },
                { data: 'expiry_date', name: 'expiry_date' },
                { data: 'expiry_remaining', name: 'expiry_remaining' },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });
    })
</script>
@endpush