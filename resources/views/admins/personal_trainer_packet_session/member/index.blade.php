@extends('layouts.master', ['title' => 'Riwayat Sesi Coach', 'main' => 'Dashboard'])

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
                            <span class="card-label fw-bold fs-3 mb-1">Riwayat Sesi Coach</span>
                        </h3>
                        <div
                            class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-start justify-content-sm-end align-items-center">
                            <div>
                                @if(Auth::user()->is_show_all_gymplace)
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
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
                            </div>
                            <div>
                                <x-form.daily-date-range-filter />
                                <input type="text" id="start_date" name="start_date" hidden>
                                <input type="text" id="end_date" name="end_date" hidden>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-6">
                        <table id="datatable-session-user" class="table table-striped border rounded gy-5 gs-7">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>User</th>
                                    <th>Tanggal</th>
                                    <th>Waktu</th>
                                    <th>Nama Kelas</th>
                                    <th>Coach</th>
                                    <th>Status</th>
                                    <th>Keterangan</th>
                                    <th>Program</th>
                                    <th class="text-center" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    function table() {
        $('#datatable-session-user').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            searchDelay: 500,
            ajax: {
                url: "{{ route('personal-trainer-packet-session-member') }}",
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id: $('#gym_place_id').val()
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
                    data: 'user',           // Mengacu pada kolom user dalam data yang diterima dari server. Data ini akan ditampilkan di tabel.
                    name: 'user_name',      // Digunakan oleh server untuk mengetahui kolom mana yang digunakan dalam pencarian atau pengurutan.
                    // searchable: false,
                },
                {
                    data: 'date',
                    name: 'date',
                },
                {
                    data: 'time',
                    name: 'time',
                },
                {
                    data: 'name',
                    name: 'name',
                    // searchable: false,
                },
                {
                    data: 'coach',
                    name: 'coach_name',
                    // searchable: false,
                },
                {
                    data: 'translated_status',
                    name: 'translated_status',
                    searchable: false,
                },
                {
                    data: 'cancel_reason',
                    name: 'cancel_reason',
                    orderable: false,
                    searchable: false,
                },
                {
                    data: 'programs',
                    name: 'programs',
                    searchable: false,
                    orderable: false,
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
    }

    function rejectReason(id) {
        Swal.fire({
            title: 'Alasan Pembatalan',
            input: "textarea",
            inputPlaceholder: "Masukkan alasan pembatalan...",
            inputAttributes: {
                "aria-label": "Masukkan alasan pembatalan",
                required: true
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
                    url: "{{ route('personal-trainer-schedule-packet-session.cancel-schedule.user', ':id') }}".replace(':id', id),
                    type: 'PUT',
                    data: {
                        cancel_reason: result.value
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
    };

</script>
@endpush