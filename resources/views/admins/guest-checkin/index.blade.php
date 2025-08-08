@extends('layouts.master', ['title' => 'Data Guest CheckIn', 'main' => 'Dashboard'])
@push('css')
<style>
    .gray-row {
        background-color: #e0e0e0;
        /* Light gray color */
    }
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
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Data Guest CheckIn</span>
                        </h3>
                        <div class="d-flex flex-wrap gap-4 align-items-center">
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
                                <label for="dateRange"
                                    class="btn btn-sm btn-light text-dark fw-600 d-flex align-items-center px-4">
                                    <input placeholder="Pick date rage"
                                        class="bg-transparent text-dark fw-600 cursor-pointer" id="dateRange" />
                                    <i class="ki-duotone ki-calendar fs-1 ms-0 me-0">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                        <span class="path6"></span>
                                    </i>
                                </label>
                                <input type="text" id="start_date" hidden>
                                <input type="text" id="end_date" hidden>
                            </div>
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <div
                            class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                            <div>
                                <h4>&nbsp;</h4>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                
                                {{-- <form action="{{ route('guest.export-excel') }}" method="GET"
                                    enctype="multipart/form-data">
                                    @method('GET')
                                    <input type="text" id="filter_excel_gym_place_id" name="gym_place_id" hidden>
                                    <input type="text" id="filter_excel_start_date" name="start_date" hidden>
                                    <input type="text" id="filter_excel_end_date" name="end_date" hidden>

                                    <button class="btn btn-success btn-sm text-nowrap" type="submit">
                                        <i class="ki-duotone ki-exit-up fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Export Excel
                                    </button>
                                </form> --}}
                            </div>
                        </div>
                        <!--begin::Table-->
                        <table id="table-guest"
                            class="table table-hover align-middle table-row-dashed fs-6 gy-5">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th>No</th>
                                    <th>Check In</th>
                                    <th>Loker</th>
                                    <th>Nama</th>
                                    <th>Phone</th>
                                    <th>Gender</th>
                                    <th>Fasilitas Tambahan</th>
                                    <th>Transaction</th>
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

{{-- start modal locker --}}
<form id="form-edit-locker" method="post">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-edit-locker" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-cs-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="check_in_type">Edit Loker Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Detail Member</h5>
                            <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr>
                                        <td>Tempat Gym</td>
                                        <td id="gym_place_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Avatar</td>
                                        <td id="avatar_user"></td>
                                    </tr>
                                    <tr>
                                        <td>Nama User</td>
                                        <td id="user_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Membership ID</td>
                                        <td id="user_member_id"></td>
                                    </tr>
                                    <tr>
                                        <td>Membership</td>
                                        <td id="name_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Personal trainer</td>
                                        <td id="name_personal_trainer"></td>
                                    </tr>
                                    <tr>
                                        <td>Sisa Sesi</td>
                                        <td id="remaining_session_personal_trainer"></td>
                                    </tr>
                                    <tr>
                                        <td>Loker</td>
                                        <td id="locker">
                                            <select name="locker_id" id="locker_id" class="form-select">
                                                <option value="">--Pilih Loker--</option>
                                            </select>
                                            <div id="info-locker"></div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import">
                        Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(document).ready(function() {

        $('select').on('change', function() {
            table();
        });
        
        $('#dateRange').on('apply.daterangepicker', function(ev, picker) {
            table(); // Reload the table when date range changes
        });
        
        table();
    });

    function table() {
        var table = $('#table-guest').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('guest.index') }}",
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#table-guest tbody').empty();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'></i>",
                    "previous": "<i class='fa fa-angle-left'></i>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing..."
            },
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'check_in', name: 'check_in', responsivePriority: -2, orderable: true, searchable: true },
                { data: 'locker', name: 'locker', responsivePriority: -2, orderable: false, searchable: false },
                { data: 'name', name: 'name', responsivePriority: -2 },
                { data: 'phone', name: 'phone', responsivePriority: -2 },
                { data: 'gender', name: 'gender', responsivePriority: -2 },
                { data: 'facility', name: 'facility', responsivePriority: -2, orderable: false, searchable: false },
                { data: 'transaction', name: 'transaction', responsivePriority: -2 },
                { data: 'action', name: 'action', orderable: false, searchable: false,
                    responsivePriority: -1
                }
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.status !== 'check_in') {
                    $(row).addClass('gray-row');
                }
            }
        });


        table.on('preXhr.dt', function(e, settings, data) {
            $('#table-guest tbody').empty();
        });

        table.on('draw.dt', function() {
            $('#table-guest').fadeIn();
        });
    }

    $(function() {
        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
            var start = start.format('YYYY-MM-DD');
            var end = end.format('YYYY-MM-DD');
            $('#start_date').val(start);
            $('#end_date').val(end);
            $('#filter_excel_start_date').val(start);
            $('#filter_excel_end_date').val(end);
            $('#filter_excel_gym_place_id').val($("#gym_place_id").val());
        }

        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            }
        }, cb);
        cb(start, end);
    });
</script>
<script>
    $(document).on('click', '.btn-checkout', function(e) {
        var form = $("#" + e.target.dataset.id);
        Swal.fire({
            title: 'Checkout Member',
            text: 'Anda yakin akan checkout member ini?',
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Checkout',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: "btn fw-bold btn-primary",
                cancelButton: "btn fw-bold btn-active-light-primary"
            }
        }).then((res) => {
            if (res.isConfirmed) {
                console.log(form);
                form.submit();
            } else {
                return false;
            }
        });
        return false;
    })
</script>
<script>
    // get data list locker with request user_id
    function getLocker(user_id, locker_id = null) {
        axios.get("{{ route('locker.search') }}", {
            params: {
                user_id: user_id
            }
        })
        .then(function(response) {
            if (response.data.length > 0) {
                let lockers = response.data;
                let options = '<option value="">Tanpa Loker</option>';
                for (locker of lockers) {
                    options +=
                    `<option value="${locker.id}" ${locker_id==locker.id ? 'selected' : '' }>${locker.name}</option>`;
                }
                $('#locker_id').html(options);
            } else {
                toastr.error(response.data)
            }
        })
        .catch(function(error) {
            console.log(error);
            toastr.error(error.message)
        });
    }

    
</script>
@endpush