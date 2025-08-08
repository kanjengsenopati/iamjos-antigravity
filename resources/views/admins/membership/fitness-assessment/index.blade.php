@extends('layouts.master', ['title' => 'Membership Free Fitness Assessment', 'main' => 'Dashboard'])

@push('css')
<style>
    .w-170px {

        width: 170px;
    }
</style>
@endpush
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <x-alert.alert-validation />
                <!--begin::Card-->
                <div class="card card-flush mt-6">
                    <div class="card-body">
                        <div class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Daftar Membership Free Fitness Assessment</span>
                            </h3>
                            <div class="d-flex flex-wrap gap-4 align-items-center">
                                @if(Auth::user()->is_show_all_gymplace)
                                    <div class="">
                                        <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                            <option value="">Semua Gym Place</option>
                                            @foreach ($gymPlaces as $gym_place)
                                                <option value="{{ $gym_place->id }}">{{ $gym_place->name }}</option>
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
                                    <select name="status" id="status" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Select an option" data-kt-table-widget-4="filter_status">
                                        <option></option>
                                        <option value=" " selected="selected">SEMUA STATUS</option>
                                        <option value="unclaimed">BELUM CLAIM</option>
                                        <option value="active">SEDANG BERLANGSUNG</option>
                                        <option value="done">SELESAI</option>
                                        <option value="expired">EXPIRED</option>
                                    </select>
                                </div>
                                <div>
                                    <x-form.date-range-filter />
                                    <input type="text" id="start_date" hidden>
                                    <input type="text" id="end_date" hidden>
                                </div>
                            </div>
                        </div>
                        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-start justify-content-sm-end align-items-center">
                            <div class="">
                                <form action="{{ route('membership.free.fitness-assessment.export') }}" method="GET"
                                    enctype="multipart/form-data">
                                    @method('GET')
                                    <input type="text" id="filter_excel_start_date" name="start_date" hidden>
                                    <input type="text" id="filter_excel_end_date" name="end_date" hidden>
                                    <input type="text" id="filter_excel_status" name="status" hidden>
                                    <input type="text" id="filter_excel_gym_place_id" name="gym_place_id" hidden>
        
                                    <button class="btn btn-success btn-sm text-nowrap" type="submit">
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
                        <div class="table-responsive">
                            <table id="datatable-membership-fitness-assessment" class="table table-hover align-start table-row-dashed fs-6 gy-5 mb-0">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th>Nama User</th>
                                        <th>Pembelian Membership</th>
                                        <th>Free Fitness Assessment</th>
                                        <th>Personal Trainer</th>
                                        <th>Status</th>
                                        <th>Aktif Sampai</th>
                                        <th style="width: 10%" class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark fw-semibold"></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        $('#status').on('change', function() {
            table()
        })
    });

    function table() {
        var table = $('#datatable-membership-fitness-assessment').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            columnDefs: [
                {"targets": 4}
            ],
            ajax: {
                url: '{{ route('membership.free.fitness-assessment.index') }}',
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id: $('#gym_place_id').val(),
                    status: $('#status').val()
                },
                beforeSend: function() {
                    $('#datatable-membership-fitness-assessment tbody').empty();
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
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'user.name',
                    name: 'user.name'
                },
                {
                    data: 'transaction_detail.parent.name',
                    name: 'transaction_detail.parent.name',
                },
                {
                    data: 'session',
                    name: 'session',
                    render: function(data, type, row) {
                        return data + ' Sesi';
                    },
                },
                {
                    data: null,
                    name: null,
                    render: function(data, type, row) {
                        return data.personal_trainer ? data.personal_trainer.name : '-';
                    },
                },
                {
                    data: null,
                    name: null,
                    render: function(data, type, row) {
                        return data.translated_status;
                    },
                },
                {
                    data: null,
                    name: null,
                    render: function(data, type, row) {
                        return data.expired_at;
                    },
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ]
        });

        document.getElementById('filter_excel_start_date').value = $("#start_date").val();
        document.getElementById('filter_excel_end_date').value = $("#end_date").val();
        document.getElementById('filter_excel_status').value = $("#status").val();
        document.getElementById('filter_excel_gym_place_id').value = $("#gym_place_id").val();
        
        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-event tbody').empty();
        });
        
        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable-event').fadeIn();
        });
    };

   
</script>
@endpush