@extends('layouts.master', ['title' => 'Detail Produk', 'main' => 'Dashboard'])
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
                        <div class="d-flex gap-3 align-items-center">
                            <a href="{{ route('shop-product.index') }}" class="mt-1">
                                <span class="menu-icon back pt-1">
                                    <i class="ki-duotone ki-arrow-left">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </a>
                            <span class="card-label fw-bold fs-3">{{ $shopProduct->name }}</span>
                            <a href="{{ route('shop-product.edit', $shopProduct->id) }}">
                                <i class="ki-duotone ki-notepad-edit fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <!--begin::Tabs-->
                        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_detail">Detail Produk</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_sales">Penjualan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_review">Review</a>
                            </li>
                        </ul>
                        <!--end::Tabs-->
                        <!--begin::Tab Content-->
                        <div class="tab-content" id="myTabContent">
                            <!--begin::Tab Detail-->
                            <div class="tab-pane fade show active" id="kt_tab_detail" role="tabpanel">
                                <div class="row">
                                    <!--begin::Col Left-->
                                    <div class="col-md-5">
                                        <!--begin::Images-->
                                        <div class="mb-5">
                                            <div class="carousel slide" data-bs-ride="carousel" id="kt_carousel_1">
                                                <div class="carousel-inner">
                                                    @foreach($shopProduct->shopProductImages as $key => $image)
                                                    <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                                        <img src="{{ asset($image->image) }}" class="d-block w-100" alt="Product Image">
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <button class="carousel-control-prev" type="button" data-bs-target="#kt_carousel_1" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon"></span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#kt_carousel_1" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon"></span>
                                                </button>
                                            </div>
                                        </div>
                                        <!--end::Images-->
                                        <!--begin::Variants-->
                                        @if($shopProduct->shopProductVariants->count() > 0)
                                        <div class="mb-5">
                                            <h4 class="mb-3">Varian Produk</h4>
                                            <div class="table-responsive">
                                                <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                                    <thead>
                                                        <tr class="fw-bold text-muted">
                                                            <th style="width: 70%">Nama Varian</th>
                                                            <th style="width: 30%">Stok</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($shopProduct->shopProductVariants as $variant)
                                                        <tr>
                                                            <td class="fw-semibold">{{ $variant->name }}</td>
                                                            <td>
                                                                <span class="badge badge-light-{{ $variant->stock > 0 ? 'success' : 'danger' }}">
                                                                    {{ $variant->stock }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                        <!--end::Variants-->
                                    </div>
                                    <!--end::Col Left-->
                                    <!--begin::Col Right-->
                                    <div class="col-md-7">
                                        <div class="mb-5">
                                            <h4 class="mb-3">Informasi Produk</h4>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="fw-bold me-2">Nama:</span>
                                                    <span>{{ $shopProduct->name }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="fw-bold me-2">Harga:</span>
                                                    <span>Rp {{ number_format($shopProduct->price, 0, ',', '.') }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="fw-bold me-2">Deskripsi:</span>
                                                    <span>{{ $shopProduct->description }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="fw-bold me-2">Kategori:</span>
                                                    <span>{{ $shopProduct->shopCategory->name ?? '-' }}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-3">
                                                    <span class="fw-bold me-2">Status:</span>
                                                    <span class="badge badge-light-{{ $shopProduct->is_active ? 'success' : 'danger' }}">
                                                        {{ $shopProduct->is_active ? 'Aktif' : 'Tidak Aktif' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Col Right-->
                                </div>
                            </div>
                            <!--end::Tab Detail-->
                            <!--begin::Tab Sales-->
                            <div class="tab-pane fade" id="kt_tab_sales" role="tabpanel">
                                <table id="table-sales" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-125px">No Transaksi</th>
                                            <th class="min-w-125px">Tanggal</th>
                                            <th class="min-w-125px">Pelanggan</th>
                                            <th class="min-w-125px">Varian dan Kuantitas</th>
                                            <th class="min-w-75px">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-dark">
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Tab Sales-->
                            <!--begin::Tab Review-->
                            <div class="tab-pane fade" id="kt_tab_review" role="tabpanel">
                                <table id="table-review" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th style="width: 5%">No</th>
                                            <th class="min-w-125px">No Transaksi</th>
                                            <th class="min-w-125px">Tanggal</th>
                                            <th class="min-w-125px">User</th>
                                            <th class="min-w-125px">Rating</th>
                                            <th class="min-w-125px">Review</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-dark">
                                    </tbody>
                                </table>
                            </div>
                            <!--end::Tab Review-->
                        </div>
                        <!--end::Tab Content-->
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
    $(document).ready(() => {
        // DataTable Sales
        var tableSales = $('#table-sales').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            searchDelay: 500,
            ajax: "{{ route('shop-product.show', ['shop_product' => $shopProduct->id, 'data' => 'sales']) }}",
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing..."
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
                    data: 'transaction.payment_code',
                    name: 'transaction.payment_code'
                },
                {
                    data: 'transaction.created_at',
                    name: 'transaction.created_at',
                    render: function(data) {
                        return moment(data).format('DD MMMM YYYY HH:mm');
                    }
                },
                {
                    data: 'user.name',
                    name: 'user.name',
                    render: function(data) {
                        return data || 'GUEST';
                    }
                },
                {
                    data: 'variant_quantity',
                    name: 'variant_quantity'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1
                }
            ]
        });

        // DataTable Review
        var tableReview = $('#table-review').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            searchDelay: 500,
            ajax: "{{ route('shop-product.show', ['shop_product' => $shopProduct->id, 'data' => 'review']) }}",
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing..."
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
                    data: 'payment_code',
                    name: 'payment_code'
                },
                {
                    data: 'transaction_at',
                    name: 'transaction_at'
                },
                {
                    data: 'user.name',
                    name: 'user.name',
                    responsivePriority: -2,
                    render: function(data) {
                        return data || 'GUEST';
                    }
                },
                {
                    data: 'star',
                    name: 'star',
                    render: function(data) {
                        let stars = '';
                        for(let i = 0; i < 5; i++) {
                            if(i < data) {
                                stars += '<i class="fas fa-star text-warning"></i>';
                            } else {
                                stars += '<i class="far fa-star text-warning"></i>';
                            }
                        }
                        return stars;
                    }
                },
                {
                    data: 'review',
                    name: 'review'
                }
            ]
        });
    });
</script>
@endpush