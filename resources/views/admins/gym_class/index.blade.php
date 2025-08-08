@extends('layouts.master', ['title' => 'Kelas & Coach', 'main' => 'Dashboard'])
@push('css')
<style>
    #myTab.nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        font-weight: 500;
    }
</style>
@endpush
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <div id="kt_content_container" class="app-container container-xxl">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3 mb-1 online-only">Kelas Gym</span>
                    </h3>
                    <div class="ms-2">
                        @if(Auth::user()->is_show_all_gymplace)
                        <select name="gym_place_id" id="gym_place_id" 
                            class="form-select w-170px"
                            data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                            data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status">
                            @foreach ($gym_places as $gym_place)
                            <option value="{{ $gym_place->id }}">{{$gym_place->name}}</option>
                            @endforeach
                        </select>
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
                <div class="hover-scroll-x mt-4">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-dark active" id="class-tab" data-bs-toggle="tab" data-bs-target="#class-tab-pane"
                                type="button" role="tab" aria-controls="class-tab-pane" aria-selected="false">List Kelas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-dark" id="category-tab" data-bs-toggle="tab" data-bs-target="#category-tab-pane"
                                type="button" role="tab" aria-controls="category-tab-pane" aria-selected="true">List Kategori Kelas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link text-dark" id="coach-external-tab" data-bs-toggle="tab" data-bs-target="#coach-external-tab-pane"
                                type="button" role="tab" aria-controls="coach-external-tab-pane" aria-selected="true">Coach Eksternal</button>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="class-tab-pane" role="tabpanel" aria-labelledby="class-tab" tabindex="0">
                            <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                <h4>List Kelas</h4>
                                <div class="d-flex flex-wrap gap-2">
                                    <a class="btn btn-primary btn-sm text-nowrap btn-create" href="#">
                                        <i class="ki-duotone ki-plus fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Kelas
                                    </a>
                                    <a type="button" class="btn btn-primary btn-sm text-nowrap" onclick="importGymClass()">
                                        <i class="ki-duotone ki-exit-down fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Import
                                    </a>
                                    <a href="#" class="btn btn-primary btn-sm text-nowrap btn-export">
                                        <i class="ki-duotone ki-exit-up fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Export Excel
                                    </a>
                                </div>
                            </div>
                            <div>
                                <table id="datatable-gym-class" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th>Thumbnail</th>
                                            <th class="min-w-100px">Nama Kelas</th>
                                            <th>Trainer</th>
                                            <th>Level</th>
                                            <th>Kategori</th>
                                            <th>Kuota Peserta</th>
                                            <th>Hari</th>
                                            <th>Masa Berlaku</th>
                                            <th>Status</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-dark fw-semibold"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="category-tab-pane" role="tabpanel" aria-labelledby="category-tab" tabindex="0">
                            <div class="border-0 pt-6 gap-4 d-flex mb-3 flex-wrap justify-content-between align-items-center">
                                <h4>List Kategori Kelas</h4>
                                <div class="d-flex gap-2">
                                    <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create-category" >
                                        <i class="ki-duotone ki-plus fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>Kategori Kelas</a>
                                </div>
                            </div>
                            <div>
                                <table id="datatable-gym-class-category"
                                    class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-125px">Kategori</th>
                                            <th class="min-w-125px">Kategori (English)</th>
                                            <th class="min-w-125px">Dibuat Pada</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-dark fw-semibold"></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="coach-external-tab-pane" role="tabpanel" aria-labelledby="coach-external-tab" tabindex="0">
                            <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                <div>
                                    <h4>List Personal Trainer External</h4>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create-external" >
                                        <i class="ki-duotone ki-plus fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>Personal Trainer External
                                    </a>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table id="datatable-personal-trainer-external" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th style="width: 15%">Avatar Image</th> 
                                            <th style="width: 20%">Nama</th>
                                            <th style="width: 40%">Bio</th>
                                            <th class="text-center min-w-100px">Aksi</th>
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
</div>
@endsection

@push('js')
<script>
    var gymPlaceDescriptions = @json($gym_places->pluck('description', 'id'));
    function updateGymPlaceDesc() {
        var gymPlaceId = $('#gym_place_id').val();
        $('#gym-place-desc').text(gymPlaceDescriptions[gymPlaceId] || '');
    }
    function importGymClass() {
        // Implementasi fungsi import jika diperlukan
        alert('Fitur import belum diimplementasikan.');
    }
    // DataTable Kelas
    function tableClass() {
        if (window.tableClassInstance) {
            window.tableClassInstance.destroy();
        }
        window.tableClassInstance = $('#datatable-gym-class').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('gym-class.index') }}",
                type: 'GET',
                data: function(d) {
                    d.gym_place_id = $('#gym_place_id').val();
                },
                beforeSend: function() {
                    $('#datatable-gym-class tbody').empty();
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
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'thumbnail',
                    name: 'thumbnail',
                    render: function(data, type, row) {
                        if (data == null) {
                            return `
                            <div class=\"symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center\">
                                <span class=\"fs-2x fw-bold text-primary text-capitalize\">${row.name.charAt(0)}</span>
                            </div>`;
                        } else {
                            return `<img src="${data}" alt="image" class="h-50px w-75px object-fit-cover img-thumbnail" />`;
                        }
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    responsivePriority: -2,
                    render: function(data, type, row, meta) {
                        return `<p>${data} <br> <span class=\"text-italic en-feature text-primary\">${row.name_en}</span></p>`;
                    }
                },
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return row.personal_trainer_name;
                    }
                },
                {
                    data: 'level',
                    name: 'level',
                },
                {
                    data: 'gym_class_category.name',
                    sortable: false,
                    searchable: false,
                    render: function(data, type, row, meta) {
                        return `<p>${data} <br> <span class="text-italic en-feature text-primary">${row.gym_class_category.name_en}</span></p>`;
                    }
                },
                {
                    data: 'quota',
                    name: 'quota',
                    render: function(data, type, row, meta) {
                        return `<p>${data}</p>`;
                    }
                },
                {
                    data: 'day',
                    name: 'day',
                    render: function(data, type, row, meta) {
                        return `<p>${data} <br> ${row.start_time} - ${row.end_time}</p>`;
                    }
                },
                {
                    data: 'date',
                    name: 'date',
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
                    responsivePriority: -1,
                }
            ]
        });
    }
    // DataTable Coach Eksternal
    function tableCoachExternal() {
        if (window.tableCoachExternalInstance) {
            window.tableCoachExternalInstance.destroy();
        }
        window.tableCoachExternalInstance = $('#datatable-personal-trainer-external').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('personal-trainer-external.index') }}?route=gym-class",
                type: 'GET',
                data: function(d) {
                    d.gym_place_id = $('#gym_place_id').val();
                },
                beforeSend: function() {
                    $('#datatable-personal-trainer-external tbody').empty();
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
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'avatar',
                    name: 'avatar'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'bio',
                    name: 'bio'
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
    }
    // DataTable Kategori Kelas
    function tableCategory() {
        if (window.tableCategoryInstance) {
            window.tableCategoryInstance.destroy();
        }
        window.tableCategoryInstance = $('#datatable-gym-class-category').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            ajax: {
                url: "{{ route('gym-class-category.index') }}",
                type: 'GET',
                data: function(d) {
                    d.gym_place_id = $('#gym_place_id').val();
                },
                beforeSend: function() {
                    $('#datatable-gym-class-category tbody').empty();
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
                    data: 'name',
                    name: 'name',
                },
                {
                    data: 'name_en',
                    name: 'name_en',
                },
                {
                    data: 'created_at',
                    name: 'created_at',
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
    function updateGymPlaceLinks() {
        var gymPlaceId = $('#gym_place_id').val();
        $('.btn-create').attr('href', "{{ route('gym-class.create', ['gym_place_id' => 'GANTI_ID']) }}".replace('GANTI_ID', gymPlaceId));
        $('.btn-export').attr('href', "{{ route('gym-class.export-excel', 'GANTI_ID') }}".replace('GANTI_ID', gymPlaceId));
        $('.btn-create-category').attr('href', "{{ route('gym-class-category.create', ['gym_place_id' => 'GANTI_ID']) }}".replace('GANTI_ID', gymPlaceId));
        $('.btn-create-external').attr('href', "{{ route('personal-trainer-external.create') }}?gym_place_id=" + gymPlaceId + "&route=gym-class");
    }
    $(document).ready(function() {
        // Jalankan DataTable untuk tab aktif saat halaman dimuat
        var activeTab = $('.nav-tabs .nav-link.active').attr('id');
        switch(activeTab) {
            case 'class-tab':
                tableClass();
                break;
            case 'category-tab':
                tableCategory();
                break;
            case 'coach-external-tab':
                tableCoachExternal();
                break;
        }

        // Event listener untuk tab
        $('#class-tab').on('shown.bs.tab', function() {
            tableClass();
        });
        $('#category-tab').on('shown.bs.tab', function() {
            tableCategory();
        });
        $('#coach-external-tab').on('shown.bs.tab', function() {
            tableCoachExternal();
        });

        // Tambahkan event listener untuk select gym_place_id
        $('#gym_place_id').on('change', function() {
            // Dapatkan tab yang sedang aktif
            var activeTab = $('.nav-tabs .nav-link.active').attr('id');
            
            // Update DataTable sesuai tab aktif
            switch(activeTab) {
                case 'class-tab':
                    tableClass();
                    break;
                case 'category-tab':
                    tableCategory();
                    break;
                case 'coach-external-tab':
                    tableCoachExternal();
                    break;
            }
            
            updateGymPlaceLinks();
            updateGymPlaceDesc();
        });
        
        updateGymPlaceLinks();
        updateGymPlaceDesc();
    });
</script>
@endpush
