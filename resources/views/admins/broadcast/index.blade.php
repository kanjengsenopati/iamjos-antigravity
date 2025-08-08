@extends('layouts.master', ['title' => 'Data Broadcast', 'main' => 'Broadcast'])
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
                        <div class="card-header mt-4">
                            <!--begin::Card title-->
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Data Broadcast</span>
                            </h3>
                            <div class="card-toolbar">
                                <div class="d-flex flex-row justify-content-between gap-4 align-items-center">
                                    <a href="{{ route('broadcast.create') }}" class="btn btn-primary btn-sm" style="min-width: 120px;">
                                        <i class="fa fa-plus"></i>
                                        Broadcast
                                    </a>
                                    @if(Auth::user()->is_show_all_gymplace)
                                    <select name="gym_place_id" id="gym_place_id" 
                                        class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-kt-table-widget-4="filter_status">
                                        <option value="">Semua Gym Place</option>
                                        @foreach ($gym_places as $gym_place)
                                        <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                    @else
                                    @php
                                        $userGymPlace = Auth::user()->gym_place;
                                    @endphp
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" disabled>
                                        @if($userGymPlace)
                                            <option value="{{ $userGymPlace->id }}" selected>{{ $userGymPlace->name }}</option>
                                        @else
                                            <option value="">Tidak ada Gym Place</option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="gym_place_id" value="{{ $userGymPlace->id ?? '' }}">
                                    @endif
                                </div>
                            </div>
                            <!--end::Card toolbar-->
                        </div>
                        <!--end::Card header-->
                        <!--begin::Card body-->
                        <div class="card-body pt-0">
                            <!--begin::Table-->
                            <table id="table-broadcast"
                                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th class="min-w-125px">Foto</th>
                                        <th class="min-w-125px">Tipe</th>
                                        <th class="min-w-125px">Broadcast Gym Place</th>
                                        <th class="min-w-125px">Target User</th>
                                        <th class="min-w-125px">Judul</th>
                                        <th class="text-center min-w-100px">Aksi</th>
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
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#table-broadcast').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                searchDelay: 500,
                ajax: {
                    url: "{{ route('broadcast.index') }}",
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
                        data: 'image',
                        name: 'image',
                        responsivePriority: -1,
                    },
                    {
                        data: 'type',
                        name: 'type',
                        responsivePriority: -1,
                    },
                    {
                        data: 'gym_place.name',
                        name: 'gym_place.name',
                        responsivePriority: -1,
                        render: function(data, type, row) {
                            return data ? data : 'Semua Gym Place';
                        }
                    },
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -1,
                        render: function(data, type, row, meta) {
                            if (row.user?.name) {
                                return row.user?.name;
                            } else if (row.user_list) {
                                return "User Tertentu"
                            } else {
                                return "All User";
                            }
                        }
                    },
                    {
                        data: 'title',
                        name: 'title',
                        responsivePriority: -1,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true,
                        responsivePriority: -1,
                    },
                ]
            });

            // Gym place filter change event
            $('#gym_place_id').on('change', function() {
                table.ajax.reload(); // Reload table data when gym place changes
            });

            $(document).on('click', '.btn-send', function(e) {
                e.preventDefault();
                var formId = $(this).data('id');
                var form = $("#" + formId);
                Swal.fire({
                    title: 'Kirim Broadcast?',
                    text: 'Anda yakin akan mengirim broadcast ini?',
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: 'Ya, Kirim',
                    cancelButtonText: 'Batal',
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    },
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
                return false;
            });
        });
    </script>
@endpush