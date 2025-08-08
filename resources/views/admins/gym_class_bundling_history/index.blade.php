@extends('layouts.master', ['title' => 'Riwayat Coach Plus', 'main' => 'Dashboard'])
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
                                <span class="card-label fw-bold fs-3 mb-1">Riwayat Coach Plus</span>
                            </h3>
                            <!--end::Card title-->
                            <!--begin::Card toolbar-->
                            <div class="card-toolbar">
                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    @if(Auth::user()->is_show_all_gymplace)
                                    <div class="">
                                        <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                            @foreach ($gymPlaces as $gym_place)
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
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <table id="datatable-gym-class-bundling-history" class="table align-middle table-row-dashed">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th>Nama User</th>
                                        <th>Paket Bundling</th>
                                        <th>Tanggal Berlaku</th>
                                        <th>Coach</th>
                                        <th>Status</th>
                                        <th class="text-center min-w-100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark fw-semibold"></tbody>
                            </table>
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
    <form id="form-asign-personal-trainer" method="post" enctype="multipart/form-data">
        @csrf
        <div class="modal fade" id="modal-asign-personal-trainer" tabindex="-1" data-bs-backdrop="static"
            data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel">Assign Coach</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <select name="personal_trainer_id" class="form-control" id="">
                            <option value="">--Pilih Coach--</option>
                            @foreach ($personalTrainers as $personalTrainer)
                                <option value="{{ $personalTrainer->id }}">{{ $personalTrainer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                            <i class="ki-duotone ki-exit-up fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            Asign Sekarang</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#datatable-gym-class-bundling-history').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('gym-class-bundling-history.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.gym_place_id = $('#gym_place_id').val();
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
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'user.name',
                        name: 'user.name',
                        responsivePriority: -2
                    },
                    {
                        data: 'gym_class_bundling.name',
                        name: 'gym_class_bundling.name'
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return `${row.start_active_date} ~ ${row.expiry_date}`;
                        }
                    },
                    {
                        data: 'pt',
                        name: 'pt',
                        orderable: false,
                        searchable: false,
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
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -1
                    },
                ]
            });

            $('#gym_place_id').on('change', function() {
                table.ajax.reload();
            })
        });

        const body = document.body;

        function asignPersonalTrainer(action) {
            $('#form-asign-personal-trainer').attr('action', action);
            $("#modal-asign-personal-trainer").modal("show")
            if (body.classList.contains('modal-open')) {
                body.style.zoom = 1
            }
        }
    </script>
@endpush
