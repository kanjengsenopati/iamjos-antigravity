@extends('layouts.master', ['title' => 'Review Layanan', 'main' => 'Dashboard'])

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
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body">
                        <div
                            class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Review Layanan</span>
                            </h3>
                            <div class="d-flex flex-wrap gap-4 align-items-center">
                                @if(Auth::user()->is_show_all_gymplace)
                                <div>
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status"
                                        onchange="table()">
                                        <option value="">Semua Gym Place</option>
                                        @foreach ($gym_places as $gym_place)
                                        <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div>
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px"
                                        data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status"
                                        disabled>
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
                                    <select name="rating" id="rating" class="form-select w-170px" data-control="select2"
                                        data-hide-search="true" data-dropdown-css-class="w-150px"
                                        data-placeholder="Select an option" data-kt-table-widget-4="filter_status"
                                        onchange="table()">
                                        <option value=" " selected>Semua Rating</option>
                                        <option value="1">Rating 1</option>
                                        <option value="2">Rating 2</option>
                                        <option value="3">Rating 3</option>
                                        <option value="4">Rating 4</option>
                                        <option value="5">Rating 5</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="hover-scroll-x mt-4">
                            <div class="d-grid">
                                <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_all" data-bs-toggle="tab" href="#tab_all">
                                            Semua Review
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_membership" data-bs-toggle="tab" href="#tab_membership">
                                            Membership
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_class" data-bs-toggle="tab" href="#tab_class">
                                            Kelas
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_pt_plus" data-bs-toggle="tab" href="#tab_pt_plus">
                                            Coach Plus
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_pt" data-bs-toggle="tab" href="#tab_pt">
                                            Coach
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_product" data-bs-toggle="tab" href="#tab_product">
                                            Produk
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-flush mt-6">
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade" id="tab_all" role="tabpanel">
                                @include('admins.service-review.tab.all_review')
                            </div>
                            <div class="tab-pane fade" id="tab_membership" role="tabpanel">
                                @include('admins.service-review.tab.membership_review')
                            </div>
                            <div class="tab-pane fade" id="tab_class" role="tabpanel">
                                @include('admins.service-review.tab.class_review')
                            </div>
                            <div class="tab-pane fade" id="tab_pt_plus" role="tabpanel">
                                @include('admins.service-review.tab.pt_plus_review')
                            </div>
                            <div class="tab-pane fade" id="tab_pt" role="tabpanel">
                                @include('admins.service-review.tab.pt_review')
                            </div>
                            <div class="tab-pane fade" id="tab_product" role="tabpanel">
                                @include('admins.service-review.tab.product_review')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="modal-service-review">
    <div class="modal-dialog" style="--bs-modal-width: 1100px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="ex_personal_trainer_name">Review</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <table class="table table-sm table-striped table-bordered" id="table-review"></table>
            </div>

        </div>
    </div>
</div>
@endsection

@push('js')
@if (request()->tab)
<script>
    $('#tab_{{request()->tab}}').addClass('show active')
    $('#nav_tab_{{request()->tab}}').addClass('active')
</script>
@else
<script>
    $('#tab_all').addClass('show active')
    $('#nav_tab_all').addClass('active')
</script>
@endif

<script>
    $(document).ready(function() {
        tableReview();
    });
    $('#nav_tab_all').on('click', function() {
        tableReview();
    })
    $('#nav_tab_membership').on('click', function() {
        tableMembership();
    })
    $('#nav_tab_class').on('click', function() {
        tableClass();
    })
    $('#nav_tab_pt_plus').on('click', function() {
        tablePtPlus();
    })
    $('#nav_tab_pt').on('click', function() {
        tablePt();
    })
    $('#nav_tab_product').on('click', function() {
        tableProduct();
    })

    function table() {
        tableReview();
        tableMembership();
        tableClass();
        tablePtPlus();
        tablePt();
        tableProduct();
    }

    function detailReview(id, type){
        $('#modal-service-review').modal('show');
        $.ajax({
            url: "{{ route('service-reviews.detail') }}",
            method: 'GET',
            data: {
                id: id,
                type: type
            },
            success: function(data) {
                console.log(data)
    
                let table = document.getElementById("table-review");
                let response = data;

                let th = type == "class" ? "<th class='text-center'>CLASS DATE</th><th class='text-center'>REVIEW AT</th>" : "<th class='text-center'>NO TRANSACTION</th>\
                        <th class='text-center'>TRANSACTION AT</th>";
                let html =
                    "<tr>\
                        <th class='text-center'>NO</th>\
                        "+th+"\
                        <th class='text-center'>USER</th>\
                        <th class='text-center'>RATING</th>\
                        <th class='text-center'>REVIEW</th>\
                    </tr>";
                let i = 1;
                if (response.length > 0) {
                    response.forEach(function(item) {
                        html += "<tr>";
                        html += "<td class='text-center' style='width: 5%'>" + (i++) + "</td>";
                        html += "<td class='text-center' style='width: 18%'>" + (type == "class" ? item.class_date : item.code_transaction) + "</td>";
                        html += "<td class='text-center' style='width: 20%'>" + (type == "class" ? item.review_at : item.transaction_at) + "</td>";
                        html += "<td class='text-center' style='width: 20%'>" + item.user + "</td>";
                        html += "<td class='text-center' style='width: 10%'>" + item.rating + "</td>";
                        html += "<td>" + item.review + "</td>";
                        html += "</tr>";
                    });
                    table.innerHTML = html;
                } else {
                    html += "<tr>";
                    html += "<td colspan='6' class='text-center'>-- Tidak Ada Data Review--</td>";
                    html += "</tr>";
                    table.innerHTML = html;
                }
            },
            error: function(err) {
                console.log(err);
            }
        });
    }

    // data table review
    function tableReview() {
        var table = $('#datatable-all-review').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            columnDefs: [
                    {"targets": 4, "className": "dt-center"}
                ],
            ajax: {
                url: "{{ route('service-reviews.index', 'data=all_review') }}",
                type: 'GET',
                data: {
                    gym_place_id:$("#gym_place_id").val(),
                    rating: $("#rating").val()
                },
                beforeSend: function() {
                    $('#datatable-all-review tbody').empty(); 
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
                { data: 'service', name: 'service' },
                { data: 'user', name: 'user' },
                { data: 'created_at', name: 'created_at' },
                { data: 'star', name: 'star' },
                { data: 'review', name: 'review' },
            ]
        });

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-all-review tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable-all-review').fadeIn();
        });
    }

    // data table membership
    function tableMembership() {
        var tableMembership = $('#datatable-membership').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('service-reviews.index', 'data=membership') }}",
                type: 'GET',
                data: {
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable-membership tbody').empty(); 
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
                { data: 'name', name: 'name' },
                { data: 'total_user', name: 'total_user' },
                { data: 'total_rating', name: 'total_rating' },
                { data: 'action', name: 'action'},
            ]
        });

        // Menyembunyikan tabel selama proses loading
        tableMembership.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-membership tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        tableMembership.on('draw.dt', function() {
            $('#datatable-membership').fadeIn();
        });
    }

    // data table kelas
    function tableClass() {
        var tableClass = $('#datatable-class').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('service-reviews.index', 'data=class') }}",
                type: 'GET',
                data: {
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable-class tbody').empty(); 
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
                { data: 'name', name: 'name' },
                { data: 'total_user', name: 'total_user' },
                { data: 'total_rating', name: 'total_rating' },
                { data: 'action', name: 'action'},
            ]
        });

        // Menyembunyikan tabel selama proses loading
        tableClass.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-class tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        tableClass.on('draw.dt', function() {
            $('#datatable-class').fadeIn();
        });
    }

    // data table pt plus
    function tablePtPlus() {
        var tablePtPlus = $('#datatable-pt-plus').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('service-reviews.index', 'data=pt_plus') }}",
                type: 'GET',
                data: {
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable-pt-plus tbody').empty(); 
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
                { data: 'name', name: 'name' },
                { data: 'total_user', name: 'total_user' },
                { data: 'total_rating', name: 'total_rating' },
                { data: 'action', name: 'action'},
            ]
        });

        // Menyembunyikan tabel selama proses loading
        tablePtPlus.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-pt-plus tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        tablePtPlus.on('draw.dt', function() {
            $('#datatable-pt-plus').fadeIn();
        });
    }

    // data table pt
    function tablePt() {
        var tablePt = $('#datatable-pt').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('service-reviews.index', 'data=pt') }}",
                type: 'GET',
                data: {
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable-pt tbody').empty();
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
                { data: 'name', name: 'name' },
                { data: 'total_user', name: 'total_user' },
                { data: 'total_rating', name: 'total_rating' },
                { data: 'action', name: 'action'},
            ]
        });

        // Menyembunyikan tabel selama proses loading
        tablePt.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-pt tbody').empty();
        })

        // Menampilkan tabel setelah data selesai dimuat
        tablePt.on('draw.dt', function() {
            $('#datatable-pt').fadeIn();
        })
    }

    // data table product
    function tableProduct() {
        var tableProduct = $('#datatable-product').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
            url: "{{ route('service-reviews.index', 'data=product') }}",
            type: 'GET',
            data: {
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable-product tbody').empty();
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
                { data: 'name', name: 'name' },
                { data: 'total_sold', name: 'total_sold' },
                { data: 'total_rating', name: 'total_rating' },
                { data: 'action', name: 'action'},
            ]
        });
    
        // Menyembunyikan tabel selama proses loading
        tableProduct.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-product tbody').empty();
        })
    
        // Menampilkan tabel setelah data selesai dimuat
        tableProduct.on('draw.dt', function() {
            $('#datatable-product').fadeIn();
        })
    }
</script>
@endpush