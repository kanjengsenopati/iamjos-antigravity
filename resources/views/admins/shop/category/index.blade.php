@extends('layouts.master', ['title' => 'Kategori Produk', 'main' => 'Dashboard'])

@section('content')
    <div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Post-->
            <div class="app-content flex-column-fluid" id="kt_app_content">
                <!--begin::Container-->
                <div id="kt_content_container" class="app-container container-xxl">
                    <x-alert.alert-validation />
                    <!--begin::Card-->
                    <div class="card card-flush">
                        <div class="card-header mt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Daftar Kategori Produk</span>
                            </h3>
                            <div class="card-toolbar">
                                <div class="me-2">
                                    @if(Auth::user()->is_show_all_gymplace)
                                    <select name="gym_place_id" id="gym_place_id" 
                                        class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status" onchange="table()">
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
                                <a type="button" class="btn btn-primary btn-sm btn-create"
                                    href="{{ route('shop-category.create') }}">
                                    <i class="fa fa-plus"></i>
                                    Kategori Produk</a>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <table id="datatable-category"
                                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th style="width: 10%">No</th>
                                        <th>Nama Kategori</th>
                                        <th class="">Nama Kategori (English)</th>
                                        <th class="">Nama Kategori (Chinese)</th>
                                        <th style="width: 10%; text-align:center">Aksi</th>
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
@endsection
@push('js')
    <script>
        $(document).ready(function() {
            var tableCategory = $('#datatable-category').DataTable({
                ordering: true,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: '{{ route('shop-category.index') }}',
                    type: 'GET',
                    data: function(d) {
                        d.gym_place_id = $('#gym_place_id').val();
                    },
                    beforeSend: function() {
                        $('#datatable-category tbody').empty();
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
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'name_en',
                        name: 'name_en'
                    },
                    {
                        data: 'name_cn',
                        name: 'name_cn'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ]
            });

            // Menyembunyikan tabel selama proses loading
            tableCategory.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-category tbody').empty();
            });
            // Menampilkan tabel setelah data selesai dimuat
            tableCategory.on('draw.dt', function() {
                $('#datatable-category').fadeIn();
            });

            $('#gym_place_id').on('change', function() {
                tableCategory.ajax.reload();
            });
        });
    </script>
@endpush
