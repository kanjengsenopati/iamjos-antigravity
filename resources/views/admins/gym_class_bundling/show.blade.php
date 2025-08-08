@extends('layouts.master', ['title' => 'Detail Coach Plus', 'main' => 'Coach Plus'])
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
            <x-alert.alert-validation />
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="d-flex gap-2 align-items-center mb-3">
                        <a href="{{ route('gym-place.show', $gymClassBundling->gym_place->id) }}" class="mt-1">
                            <span class="menu-icon back pt-1">
                                <i class="ki-duotone ki-arrow-left">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </a>
                        <h3 class="text-capitalize mb-0">{{$gymClassBundling->name}}</h3>
                        <a href="{{route('gym-class-bundling.edit', $gymClassBundling->id)}}">
                            <i class="ki-duotone ki-notepad-edit fs-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </a>
                    </div>
                    <div class="hover-scroll-x mt-5">
                        <div class="d-grid">
                            <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                <li class="nav-item">
                                    <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary active" 
                                        id="nav_tab_detail" data-bs-toggle="tab" href="#tab_detail">
                                        Detail Coach Plus
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary" 
                                        id="nav_tab_price_period" data-bs-toggle="tab" href="#tab_price_period">
                                        Membership Price Period
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <div class="card mt-6">
                <div class="card-body v2">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="tab_detail" role="tabpanel">
                            @if ($gymClassBundling->type == 'LIMIT_DAY')
                            <span class="badge badge-primary">{{$gymClassBundling->period}} Hari</span>
                            @elseif ($gymClassBundling->type == 'LIMIT_DAY_AND_SESSION')
                            <span class="badge badge-primary">{{$gymClassBundling->period}} Hari</span> 
                            <span class="badge badge-secondary">{{$gymClassBundling->total_session}} Sesi</span>
                            @else
                            <span class="text-sub-title">Membership Selamanya</span>
                            @endif
                            <hr class="mt-8 mb-3">
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="text-label text-muted">Harga</label>
                                    @if ($gymClassBundling->discount_price)
                                    <p class="text-label">Rp <s class="text-label text-danger">@money($gymClassBundling->price)</s>
                                        @money($gymClassBundling->discount_price) <br>
                                        <i>
                                            <small class="text-danger">Promo Berlaku {{$gymClassBundling->start_date_discount}}
                                                {{$gymClassBundling->start_time_discount}} ~ {{$gymClassBundling->end_date_discount}}
                                                {{$gymClassBundling->end_time_discount}}</small>
                                        </i>
                                    </p>
                                    @else
                                    <p class="text-label">Rp @money($gymClassBundling->price)</p>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-4">
                                    <label class="text-label text-muted">Benefit</label>
                                    @foreach ($gymClassBundling->membership_benefits ?? [] as $membershipBenefit )
                                    <p><i class="fa fa-check">&nbsp;</i>{{$membershipBenefit->name}}</p>
                                    @endforeach
                                </div>
                                <div class="col-sm-4 en-feature">
                                    <label class="text-label text-muted">Benefit (en)</label>
                                    @foreach ($gymClassBundling->en_membership_benefits ?? [] as $membershipBenefit )
                                    <p><i class="fa fa-check">&nbsp;</i>{{$membershipBenefit->name}}</p>
                                    @endforeach
                                </div>
                                <div class="col-sm-4" style="display: none">
                                    <label class="text-label text-muted">Benefit (cn)</label>
                                    @foreach ($gymClassBundling->cn_membership_benefits ?? [] as $membershipBenefit )
                                    <p><i class="a fa-check">&nbsp;</i>{{$membershipBenefit->name}}</p>
                                    @endforeach
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-4 en-feature">
                                    <label class="text-label text-muted">Deskripsi</label>
                                    <p class="text-label">{{$gymClassBundling->description}}</p>
                                </div>
                                <div class="col-sm-4 en-feature">
                                    <label class="text-label text-muted">Deskripsi (English)</label>
                                    <p class="text-label">{{$gymClassBundling->description_en}}</p>
                                </div>
                                <div class="col-sm-4" style="display: none">
                                    <label class="text-label text-muted">Deskripsi (Chinese)</label>
                                    <p class="text-label">{{$gymClassBundling->description_cn}}</p>
                                </div>
                            </div>
                            <div class="card-header mt-6">
                                <!--begin::Card title-->
                                <h3 class="card-title align-items-start flex-column">
                                    <span class="card-label fw-bold fs-3 mb-1">Reward Produk Membership</span>
                                </h3>
                                <!--end::Card title-->
                                <!--begin::Card toolbar-->
                                <div class="card-toolbar">
                                    <!--begin::Button-->
                                    <a type="button" class="btn btn-sm btn-primary btn-create"
                                        href="{{ route('membership-product.create',['gym_class_bundling_id' => $gymClassBundling->id]) }}">
                                        <i class="fa fa-plus"></i>
                                        Reward Produk
                                    </a>
                                    <!--end::Button-->
                                </div>
                                <!--end::Card toolbar-->
                            </div>
                            <!--end::Card body-->
                            <div class=" card-body pt-0">
                                <!--begin::Table-->
                                <table id="datatable" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="w-125px">Foto</th>
                                            <th class="w-250px">Produk</th>
                                            <th>Jumlah</th>
                                            <th class="text-center min-w-100px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-dark fw-semibold"></tbody>
                                </table>
                                <!--end::Table-->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="tab_price_period" role="tabpanel">
                            <div class="card-body pt-0">
                                <table id="datatable-price-period" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="w-125px">Period</th>
                                            <th class="w-250px">Price</th>
                                            <th class="w-250px">Membership Price</th>
                                            <th class="w-250px">Coach Price</th>
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
        $(document).ready(function() {
            var table = $('#datatable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('gym-class-bundling.show', ['gym_class_bundling' => $gymClassBundling->id, 'data' => 'gym_class_bundling']) }}",
                    type: 'GET',
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
                        render: function(data, type, row) {
                            if (data == null) {
                                return `
                            <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center">
                                <span class="fs-2x fw-bold text-primary text-capitalize">
                                    ${row.name.charAt(0)}</span>
                            </div>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px object-fit-cover w-75px img-thumnail" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                        responsivePriority: -2,
                    },
                    {
                        data: 'quantity',
                        name: 'quantity',
                        responsivePriority: -1,
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -1,
                    },
                ]
            });

             var tablePeriod = $('#datatable-price-period').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('gym-class-bundling.show', ['gym_class_bundling' => $gymClassBundling->id, 'data' => 'price-period']) }}",
                    type: 'GET',
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
                        data: 'period',
                        name: 'period',
                        responsivePriority: -1,
                    },
                    {
                        data: 'price',
                        name: 'price',
                        responsivePriority: -1,
                        render: function(data) {
                            return 'Rp ' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    },
                    {
                        data: 'membership_price',
                        name: 'membership_price',
                        responsivePriority: -1,
                        render: function(data) {
                            return 'Rp ' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    },
                    {
                        data: 'coach_price',
                        name: 'coach_price',
                        responsivePriority: -1,
                        render: function(data) {
                            return 'Rp ' + data.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        }
                    },
                    // {
                    //     data: 'action',
                    //     name: 'action',
                    //     orderable: false,
                    //     searchable: false,
                    //     responsivePriority: -1,
                    // },
                ]
            });
        });
    </script>
    @endpush