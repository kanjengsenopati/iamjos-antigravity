@extends('layouts.master', ['title' => 'Data User', 'main' => 'Dashboard'])
@push('css')
<style>
    /* td.dtr-control:before {
                            margin-top: 1px !important;
                        }
                        td.dtr-control {
                            display: flex;
                            align-items: center;
                        } */
</style>
@endpush
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header mt-6">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Data User</span>
                        </h3>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Button-->
                            <a type="button" class="btn btn-primary btn-superadmin btn-sm"
                                href="{{ route('user.create') }}">
                                <i class="fa fa-plus"></i>
                                User</a>
                            <!--end::Button-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <form action="{{ route('user.export-excel') }}" method="get">
                            <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4
                                 justify-content-start justify-content-sm-end align-items-center">
                                @if(Auth::user()->is_show_all_gymplace)
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                        <option value="">Semua Gym Place</option>
                                        @foreach ($gym_places as $gym_place)
                                        <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" disabled>
                                        @php
                                            $userGymPlace = Auth::user()->gym_place;
                                        @endphp
                                        @if($userGymPlace)
                                            <option value="{{ $userGymPlace->id }}" selected>{{ $userGymPlace->name }}</option>
                                        @else
                                            <option value="">Tidak ada Gym Place</option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="gym_place_id" value="{{ $userGymPlace->id ?? '' }}">
                                </div>
                                @endif
                                <div>
                                    <select name="is_active" id="is_active" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-250px"
                                        data-kt-table-widget-4="filter_status" onchange="table()">
                                        <option value="">Semua Status</option>
                                        <option value="membership_true">Memiliki Membership</option>
                                        <option value="membership_expired">Status Membership Expired</option>
                                        <option value="membership_extended">Membership yang di Perpanjang</option>
                                        <option value="gender_male">Member Laki-laki</option>
                                        <option value="gender_female">Member Perempuan</option>
                                        <option value="buy_coach">Member yang Membeli Coach</option>
                                        <option value="not_buy_coach">Member yang tidak Membeli Coach</option>
                                        <option value="not_buy_membership">Member yang tidak Membeli Membership</option>
                                        <option value="membership_complimentary">Membership Complimentary</option>
                                    </select>
                                </div>
                                <div>
                                    <x-form.date-range-filter />
                                    <input type="text" id="start_date" name="start_date" hidden>
                                    <input type="text" id="end_date" name="end_date" hidden>
                                </div>
                                <div>
                                    <button class="btn btn-primary btn-sm text-nowrap">
                                        <i class="ki-duotone ki-exit-up fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Export Excel
                                    </button>
                                </div>
                            </div>
                        </form>
                        <!--begin::Table-->
                        <table id="table-user" class="table table-hover align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th class="width-100px">Avatar</th>
                                    <th>Nama</th>
                                    <!--<th>Email</th>-->
                                    <th>No Hp</th>
                                    <th>NIK</th>
                                    <th>Membership ID</th>
                                    <th>Tanggal Bergabung</th>
                                    <th>Status</th>
                                    <th>Avatar Gate Uploaded</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Berat (Kg)</th>
                                    <th>Tinggi (Cm)</th>
                                    <th>Goal</th>
                                    <th>Rutinitas</th>
                                    <th>Durasi</th>
                                    <th>PIN</th>
                                    <th>Avatar Update</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="fw-semibold text-dark">
                            </tbody>
                        </table>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->

</div>
@endsection
@push('js')
<script>
    function table() {
        var tableUser = $('#table-user').DataTable({
            // ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('user.index') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    is_active: $("#is_active").val(),
                    gym_place_id: $("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#table-user tbody').empty();
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
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            order: [[6, 'desc']],
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1
                    }
                },
                {
                    data: 'avatar',
                    name: 'avatar',
                    orderable: false,
                    render: function(data, type, row) {
                        if (data == null) {
                            return `
                        <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary
                         d-flex justify-content-center align-items-center">
                            <span class="fs-2x fw-bold text-primary text-capitalize">
                                ${row.name.charAt(0)}</span>
                        </div>`
                        } else {
                            return `<img src="${data}" alt="image" class="h-50px w-50px rounded-circle" />`;
                        }
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    responsivePriority: -2,
                },
                {
                    data: 'phone',
                    name: 'phone',
                    render: function(data, type, row) {
                        return data ?? `<span>-</span>`;
                    }
                },
                {
                    data: 'nik',
                    name: 'nik',
                    render: function(data, type, row) {
                        return data ? data.substr(0, 5) + "********" : `<span>-</span>`;
                    }
                },
                {
                    data: 'membership_user.member_id',
                    name: 'membership_user.member_id',
                    orderable: false,
                    render: function(data) {
                        return data ?? `belum ada`
                    }
                },
                {
                    data: 'created_at', 
                    name: 'created_at'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        // Untuk sorting, kembalikan nilai numerik (1 untuk true, 0 untuk false)
                        if (type === 'sort') {
                            return data ? 1 : 0;
                        }
                        
                        // Untuk tampilan, kembalikan badge dengan label
                        const badgeClass = data ? 'badge-light-success' : 'badge-light-danger';
                        const label = data ? 'Aktif' : 'Nonaktif';
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    },
                    type: 'num' // Tentukan tipe data sebagai numerik untuk sorting yang tepat
                },
                {
                    data: 'is_gate_avatar_been_uploaded',
                    name: 'is_gate_avatar_been_uploaded',
                    render: function(data, type, row) {
                        // Untuk sorting, kembalikan nilai numerik (1 untuk true, 0 untuk false)
                        if (type === 'sort') {
                            return data ? 1 : 0;
                        }
                        
                        // Untuk tampilan, kembalikan badge dengan label
                        const badgeClass = data ? 'badge-light-success' : 'badge-light-danger';
                        const label = data ? 'SUCCESS' : 'FAILED';
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    },
                    type: 'num' // Tentukan tipe data sebagai numerik untuk sorting yang tepat
                },
                {
                    data: 'gender',
                    name: 'gender',
                    render: function(data) {
                        return data ? (data == "MALE" ? "Laki-laki" : "Perempuan") : "-";
                    }
                },
                {
                    data: 'birth_date',
                    name: 'birth_date',
                    render: function(data) {
                        return `<p class="mb-0 text-nowrap">${data}</p>`
                    }
                },
                {
                    data: 'weight',
                    name: 'weight',
                },
                {
                    data: 'height',
                    name: 'height',
                },
                {
                    data: 'goal_translated',
                    name: 'goal_translated',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'routine_translated',
                    name: 'routine_translated',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'duration_translated',
                    name: 'duration_translated',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'pin_enabled',
                    name: 'pin_enabled',
                    render: function(data) {
                        const badgeClass = data ? 'badge-success' : 'badge-danger';
                        const label = data ? 'Aktif' : 'Tidak Aktif';
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    }
                },
                {
                    data: 'is_avatar_update',
                    name: 'is_avatar_update',
                    render: function(data) {
                        const badgeClass = data ? 'badge-success' : 'badge-danger';
                        const label = data ? 'Aktif' : 'NonAktif';
                        return `<span class="badge ${badgeClass}">${label}</span>`;
                    }
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

        tableUser.on('preXhr.dt', function(e, settings, data) {
            $('#table-user tbody').empty();
        });

        tableUser.on('draw.dt', function() {
            $('#table-user').fadeIn();
        });

        // tableUser.ajax.reload();
    }

</script>
@endpush