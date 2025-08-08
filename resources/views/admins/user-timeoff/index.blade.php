@extends('layouts.master', ['title' => 'Cuti Membership', 'main' => 'Dashboard'])

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
                    <div class="card-header mt-6 align-items-center">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Cuti Membership</span>
                        </h3>
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
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="table-user-timeoff" class="table table-hover align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Tanggal Pengajuan</th>
                                    <th>Periode Cuti</th>
                                    <th>Tanggal Mulai</th>
                                    <th>Tanggal Selesai</th>
                                    <th>Status Cuti</th>
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
    // $('select').on('change', function() {
    //         table();
    //     })
    $(document).ready(() => {
        table()
    })
    const table = () => {
        var gymPlaceId = $('#gym_place_id').val(); // Ambil nilai dari dropdown
        var table = $('#table-user-timeoff').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('user-timeoff.index') }}",
                data: function(d) {
                    d.gym_place_id = gymPlaceId; // Kirim nilai filter ke server
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
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1
                    }
                },
                {
                    data: 'user.name',
                    name: 'user.name',
                    responsivePriority: -2,
                },
                {
                    data: 'created_at',
                    name: 'created_at',
                    responsivePriority: -2,
                },
                {
                    data: 'duration',
                    name: 'duration',
                },
                {
                    data: 'start_date',
                    name: 'start_date',
                },
                {
                    data: 'end_date',
                    name: 'end_date',
                },
                {
                    data: 'status',
                    name: 'status',
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

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#table-user-timeoff tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#table-user-timeoff').fadeIn();
        });
    }

    // Panggil fungsi table saat dropdown berubah
    $('#gym_place_id').on('change', function() {
        table();
    });
</script>
@endpush