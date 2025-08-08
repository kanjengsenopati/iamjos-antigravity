@extends('layouts.master', ['title' => 'Data Perubahan Coach', 'main' => 'Data Perubahan Coach'])
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
                            <span class="card-label fw-bold fs-3 mb-1">Data Perubahan Coach</span>
                        </h3>
                        <!--end::Card title-->
                        <div class="d-flex gap-4">
                            @if(Auth::user()->is_show_all_gymplace)
                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    <div>
                                        <select name="gym_place_id" id="gym_place_id" class="form-select form-select-sm w-170px" data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status">
                                            <option value="">Semua Gym Place</option>
                                            @foreach ($gym_places as $gym_place)
                                                <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    <div>
                                        <select name="gym_place_id" id="gym_place_id" class="form-select form-select-sm w-170px" disabled>
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
                                </div>
                            @endif
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <!--begin::Button-->
                                <div>
                                    <select name="status" id="status" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Status Activity" data-kt-table-widget-4="filter_status">
                                        <option value=" ">Semua</option>
                                        <option value="PENDING">Pending</option>
                                        <option value="APPROVED">Approved</option>
                                        <option value="REJECTED">Rejected</option>
                                    </select>
                                </div>
                                <!--end::Button-->
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Table-->
                        <table id="table-coach-change-history"
                            class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" style="width:100%">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>User</th>
                                    <th>Coach Lama</th>
                                    <th>Coach Baru</th>
                                    <th>Alasan</th>
                                    <th>Status</th>
                                    <th>Staff</th>
                                    <th style="width: 10%" class="text-center min-w-100px">Aksi</th>
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
    $('select').on('change', function() {
        table();
    })

    function table() {
        var table = $('#table-coach-change-history').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('coach-change-history.index') }}",
                type: 'GET',
                data: {
                    status: $('#status').val(),
                    gym_place_id: $('#gym_place_id').val()
                }
            },
            language: {
                paginate: {
                    next: "<i class='fa fa-angle-right'>",
                    previous: "<i class='fa fa-angle-left'>"
                },
                loadingRecords: "Loading...",
                processing: "Processing...",
            },
            lengthMenu: [
                [10, 25, 50, 100, -1],
                [10, 25, 50, 100, "All"]
            ],
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
                    data: 'user',
                    name: 'user',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'old_coach',
                    name: 'old_coach',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'new_coach',
                    name: 'new_coach',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'reason',
                    name: 'reason',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
                {
                    data: 'staff',
                    name: 'staff',
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -2,
                }
            ]
        });
    }

    $(document).ready(function() {
        $(document).on('click', '.btn-reject-reason', function() {
            var button = $(this); // Store reference to 'this'
            var id = button.data('id');
            var status = button.data('status');

            Swal.fire({
                title: 'Alasan Penolakan',
                input: "textarea",
                inputPlaceholder: "Masukkan alasan penolakan...",
                inputAttributes: {
                    "aria-label": "Masukkan alasan penolakan"
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-sm fw-semibold btn-primary',
                    cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('coach-change-history.update', ':id') }}".replace(':id', id),
                        type: 'PUT',
                        data: {
                            id: id,
                            status: status,
                            reject_reason: result.value
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                table(); // Use the reference to reload the table
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'Tutup',
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'Tutup',
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan pada server.',
                                icon: 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    });
                }
            });
        });

        // Handle approve and reject actions
        $(document).on('click', '.btn-approve', function() {
            var button = $(this); // Store reference to 'this'
            var id = button.data('id');
            var status = button.data('status');
            
            Swal.fire({
                title: `Apakah anda yakin ${status} data ini?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('coach-change-history.update', ':id') }}".replace(':id', id),
                        type: 'PUT',
                        data: {
                            id: id,
                            status: status
                        },
                        success: function(response) {
                            if (response.status === 'success') {
                                table(); // Use the reference to reload the table

                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonText: 'Tutup',
                                });
                            } else {
                                Swal.fire({
                                    title: 'Gagal!',
                                    text: response.message,
                                    icon: 'error',
                                    confirmButtonText: 'Tutup',
                                });
                            }
                        },
                        error: function() {
                            Swal.fire({
                                title: 'Error!',
                                text: 'Terjadi kesalahan pada server.',
                                icon: 'error',
                                confirmButtonText: 'Tutup',
                            });
                        }
                    });
                }
            });
        });

        table();
    })
</script>
@endpush