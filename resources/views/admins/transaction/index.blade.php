@extends('layouts.master', ['title' => 'Riwayat Transaksi', 'main' => 'Dashboard'])

@push('css')
<style>
    .w-170px {

        width: 170px;
    }

    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #myImg:hover {
        opacity: 0.7;
    }


    /* Modal Content (image) */
    .modal-content-payment-receipt {
        height: 100%;
        width: 100%;
        object-fit: contain;
        max-height: 78vh;
        margin: 0 auto;
    }

    .product-row {
        padding-bottom: 1rem;
        border-bottom: 1px dashed #dee2e6;
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
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-6">
                        <!-- Baris 1: Judul + Create Button -->
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-6">
                            <h3 class="card-title fw-bold fs-3 mb-0">Riwayat Transaksi</h3>
                            <button type="button" class="btn btn-primary btn-sm btn-create" data-bs-toggle="modal" data-bs-target="#modal-add-transaction">
                                <i class="fa fa-plus"></i>
                                Buat Transaksi
                            </a>
                        </div>
                        
                        <!-- Baris 2: Filter di sebelah kanan -->
                        <div class="d-flex justify-content-end mb-4">
                            <div class="d-flex flex-wrap gap-4">

                                <!-- Filter: Gym Place -->
                                @if(Auth::user()->is_show_all_gymplace)
                                <div class="w-170px">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px" data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status">
                                        <option value="">Semua Gym Place</option>
                                        @foreach ($gym_places as $gym_place)
                                            <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="w-170px">
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

                                <!-- Filter: Date Range -->
                                <div class="mt-1">
                                    <x-form.date-range-filter />
                                    <input type="text" id="start_date" hidden>
                                    <input type="text" id="end_date" hidden>
                                </div>

                            </div>
                        </div>

                        <!-- Baris 3: Filter di sebelah kanan -->
                        <div class="d-flex justify-content-end mb-4">
                            <div class="d-flex flex-wrap gap-4">

                                <!-- Filter: Payment -->
                                <div class="w-170px">
                                    <select name="payment" id="payment" class="form-select" data-control="select2"
                                        data-hide-search="true" data-placeholder="Tipe Pembelian">
                                        <option></option>
                                        <option value=" " selected>SEMUA TIPE PEMBELIAN</option>
                                        @foreach ($payment_methods as $payment_method)
                                            <option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter: Package -->
                                <div class="w-170px">
                                    <select name="package" id="package" class="form-select" data-control="select2"
                                        data-hide-search="true" data-placeholder="Jenis Paket">
                                        <option></option>
                                        <option value=" " selected>SEMUA JENIS PAKET PEMBELIAN</option>
                                        @foreach ($types as $model => $type)
                                            <option value="{{ $model }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filter: Status -->
                                <div class="w-170px">
                                    <select name="status" id="status" class="form-select" data-control="select2"
                                        data-hide-search="true" data-placeholder="Status">
                                        <option></option>
                                        <option value=" " selected>SEMUA STATUS</option>
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Baris 3: Total Pendapatan Kiri + Export Kanan -->
                        <div class="d-flex flex-wrap justify-content-between align-items-start">
                            <!-- Total Pendapatan -->
                            <div class="bg-gray-100 bg-opacity-70 rounded-2 px-6 py-5 mb-4">
                                <div class="symbol symbol-30px mb-3">
                                    <span class="symbol-label">
                                        <i class="ki-duotone ki-chart fs-1 text-primary">
                                            <span class="path1"></span><span class="path2"></span>
                                        </i>
                                    </span>
                                </div>
                                <div>
                                    <span class="text-gray-700 fw-bolder d-block fs-2qx mb-1" id="revenueTotal">Rp0</span>
                                    <span class="text-gray-500 fw-semibold fs-6">TOTAL PENDAPATAN</span>
                                </div>
                            </div>

                            <!-- Tombol Export -->
                            <div class="d-flex flex-column gap-2">
                                <!-- Export Excel -->
                                <div id="exportForm">
                                    <input type="text" id="filter_excel_start_date" name="start_date" hidden>
                                    <input type="text" id="filter_excel_end_date" name="end_date" hidden>
                                    <input type="text" id="filter_excel_payment" name="payment" hidden>
                                    <input type="text" id="filter_excel_package" name="package" hidden>
                                    <input type="text" id="filter_excel_status" name="status" hidden>
                                    <input type="text" id="filter_excel_gym_place" name="gym_place_id" hidden>
                                    <button type="button" class="btn btn-success btn-sm text-nowrap w-150px" onclick="downloadExport(event)">
                                        <i class="ki-duotone ki-exit-up fs-3 me-1"></i> Export Excel
                                    </button>
                                </div>

                                <!-- Export PDF -->
                                <form action="{{ route('transaction.export.pdf') }}" method="GET">
                                    <input type="text" id="filter_pdf_start_date" name="start_date" hidden>
                                    <input type="text" id="filter_pdf_end_date" name="end_date" hidden>
                                    <input type="text" id="filter_pdf_payment" name="payment" hidden>
                                    <input type="text" id="filter_pdf_package" name="package" hidden>
                                    <input type="text" id="filter_pdf_gym_place" name="package" hidden>
                                    <button class="btn btn-danger btn-sm text-nowrap w-150px d-none" type="submit">
                                        <i class="ki-duotone ki-exit-up fs-3 me-1"></i> Export PDF
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!--begin::Table-->
                        <table id="datatable" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Tanggal</th>
                                    <th>Nama User</th>
                                    <th>Kode Pembayaran</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Note</th>
                                    <th>Program</th>
                                    <th>Pembayaran</th>
                                    <th>Tanggal Pembayaran</th>
                                    <th>Bukti Pembayaran</th>
                                    <th>Status</th>
                                    <th class="text-center min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark fw-semibold"></tbody>
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

<form id="form-change-amount" method="post" enctype="multipart/form-data">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-change-amount" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit Nominal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mt-3">
                        <!-- Kolom Kiri: Total Pembayaran -->
                        <div class="col-lg-6">
                            <div class="form-group mb-3 row">
                                <label for="pay_amount" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Harga Produk
                                </label>
                                <div class="col-lg-7">
                                    <input type="text" name="pay_amount" class="form-control input-money"
                                        id="pay_amount" required>
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label for="promo_type" class="col-lg-5 col-form-label fw-semibold fs-6">
                                    Jenis Promo
                                </label>
                                <div class="col-lg-7">
                                    <select name="promo_type" id="promo_type" class="form-select">
                                        <option value="">-- Pilih Jenis Promo --</option>
                                        <option value="nominal">Nominal (Rp)</option>
                                        <option value="percent">Persen (%)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group mb-3 row" id="discount_promo_group" style="display: none;">
                                <label for="discount_promo" class="col-lg-5 col-form-label fw-semibold fs-6">
                                    Potongan Promo
                                </label>
                                <div class="col-lg-7">
                                    <input type="number" min="0" name="discount_promo" class="form-control" id="discount_promo">
                                    <small id="discount_promo_info" class="text-muted"></small>
                                </div>
                            </div>
                            <div class="form-group mb-3 row" id="after_discount_group" style="display: none;">
                                <label for="after_discount" class="col-lg-5 col-form-label fw-semibold fs-6">
                                    Total Bayar Setelah Diskon
                                </label>
                                <div class="col-lg-7">
                                    <input type="text" readonly class="form-control" id="after_discount" name="pay_amount_after_discount">
                                </div>
                            </div>
                            <div class="form-group mb-3 row" style="display: none;" id="offline_payment_method_group">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                    <span class="required">Pilih Metode Pembayaran di Tempat</span>
                                </label>
                                <div class="p-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="QRIS" id="QRIS"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="QRIS">
                                            QRIS
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="DEBIT BNI" id="DEBITBNI"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="DEBITBNI">
                                            DEBIT BNI
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="CREDIT BNI" id="CREDITBNI"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="CREDITBNI">
                                            CREDIT BNI
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="QRIS BNI" id="QRISBNI"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="QRISBNI">
                                            QRIS BNI
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="DEBIT OCBC" id="DEBITOCBC"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="DEBITOCBC">
                                            DEBIT OCBC
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="CREDIT OCBC"
                                            id="CREDITOCBC" name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="CREDITOCBC">
                                            CREDIT OCBC
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="QRIS OCBC" id="QRISOCBC"
                                            name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="QRISOCBC">
                                            QRIS OCBC
                                        </label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="radio" value="TRANSFER BCA"
                                            id="TRANSFERBCA" name="offline_payment_method" />
                                        <label class="form-check-label fw-semibold fs-6" for="TRANSFERBCA">
                                            TRANSFER BCA
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <!-- Hidden input untuk total potongan promo -->
                            <input type="hidden" name="total_discount_promo" id="total_discount_promo">
                        </div>
                        <!-- Kolom Kanan: Lainnya -->
                        <div class="col-lg-6">
                            <div class="form-group mb-3 row">
                                <label for="paid_at" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Tanggal Pembayaran
                                </label>
                                <div class="col-lg-7">
                                    <input type="datetime-local" name="paid_at" class="form-control" id="paid_at">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label for="created_at" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Tanggal Transaksi
                                </label>
                                <div class="col-lg-7">
                                    <input type="datetime-local" name="created_at" class="form-control" id="created_at">
                                </div>
                            </div>
                            <div class="form-group mb-3 row">
                                <label for="note" class="col-lg-5 col-form-label required fw-semibold fs-6">
                                    Catatan
                                </label>
                                <div class="col-lg-7">
                                    <textarea type="text" name="note" class="form-control" id="note"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-save fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal image popup --}}
{{-- <div id="modal-payment-receipt-popup" tabindex="-1" class="modal-payment-receipt" onclick="closeModal()">
    <span class="close">&times;</span>
    <img class="modal-content-payment-receipt" id="img01">
    <img id="myImg" src="img_snow.jpg" alt="Snow" style="width:100%;max-width:300px">
    <div id="caption"></div>
</div> --}}
<div class="modal fade" id="modal-payment-receipt-popup" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Pembayaran</h5>
                <button onclick="closeModal()" type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body mx-auto">
                <img class="modal-content-payment-receipt" id="img01" alt="">
            </div>
        </div>
    </div>
</div>

{{-- form modal upload image --}}
<form id="form-upload-payment-receipt" enctype="multipart/form-data" method="POST">
    @csrf
    <div class="modal fade" tabindex="-1" id="kt_modal_upload_payment_receipt">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Form Upload Bukti Pembayaran</h3>
                </div>

                <div class="modal-body">
                    <x-form.image-upload label="Bukti Pembayaran (Wajib Diisi)" name="payment_receipt" :value="null"
                        required />
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal make transaction --}}
<form action="{{ route('transaction.store') }}" method="post" enctype="multipart/form-data" id="form-add-transaction">
    @csrf
    <div class="modal fade" id="modal-add-transaction" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" id="custom-modal-user-checkin">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Buat Transaksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex flex-wrap">
                        <div class="col-xl-8" id="product-rows-container">
                            <div class="row mt-3 product-row" data-index="0">
                                <div class="col-1">
                                    <label class="fs-6 fw-bold form-label mt-3" for="">Aksi</label><br>
                                    <button type="button" class="btn btn-secondary btn-sm mt-2" onclick="addShopProduct()">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </div>
                                <div class="col-5">
                                    <label class="fs-6 fw-bold form-label mt-3" for="shop_product_id[]">
                                        <span class="required">Nama Produk</span>
                                    </label>
                                    <select class="form-select" name="shop_product_id[]" id="shop_product_id[]" onchange="varianShopProduct(this)" required>
                                        <option value="">--Pilih Produk--</option>
                                        @foreach ($shopProducts as $shopProduct)
                                            <option value="{{ $shopProduct->id }}">{{ $shopProduct->name }} {{ $shopProduct->stock ? ", Stock : {$shopProduct->stock}" : "" }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4 d-none varian-wrapper" id="varian_wrapper_0">
                                    <label class="fs-6 fw-bold form-label mt-3" for="shop_product_varian_id_0">
                                        <span class="required">Varian</span>
                                    </label>
                                    <select name="shop_product_varian_id[0]" class="form-select" id="shop_product_varian_id_0"></select>
                                </div>
                                <div class="col-2">
                                    <label class="fs-6 fw-bold form-label mt-3" for="quantity[]">
                                        <span class="required">Kuantitas</span>
                                    </label>
                                    <input type="number" class="form-control" name="quantity[]" id="quantity[]" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-1">
                            <div class=""></div>
                        </div>
                        <div class="col-xl-3">
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="tag">Member</label>
                                <select class="form-select" name="user_id" id="user_id" data-control="select2" data-placeholder="Pilih Member" >
                                    <option value="">--Pilih Member--</option>
                                    @foreach ($members as $member)
                                        <option value="{{ $member->id }}">
                                            {{ $member->name }} 
                                            @if ($member->membership_user)
                                                ({{ $member->membership_user?->member_id }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                    <span class="required">Pilih Metode Pembayaran di Tempat</span>
                                </label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="QRIS" id="QRIS"
                                        name="offline_payment_method" required/>
                                    <label class="form-check-label fw-semibold fs-6" for="QRIS">
                                        QRIS
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="DEBIT BNI" id="DEBITBNI"
                                        name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="DEBITBNI">
                                        DEBIT BNI
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="CREDIT BNI" id="CREDITBNI"
                                        name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="CREDITBNI">
                                        CREDIT BNI
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="QRIS BNI" id="QRISBNI"
                                        name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="QRISBNI">
                                        QRIS BNI
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="DEBIT OCBC" id="DEBITOCBC"
                                        name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="DEBITOCBC">
                                        DEBIT OCBC
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="CREDIT OCBC"
                                        id="CREDITOCBC" name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="CREDITOCBC">
                                        CREDIT OCBC
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="QRIS OCBC" id="QRISOCBC"
                                        name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="QRISOCBC">
                                        QRIS OCBC
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="TRANSFER BCA"
                                        id="TRANSFERBCA" name="offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="TRANSFERBCA">
                                        TRANSFER BCA
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        Tambah Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
@push('js')
<script>
        $(".input-money").on('keyup', function() {
            var n = parseInt($(this).val().replace(/\D/g, ''), 10) || 0
            if (n > 0) {
                // var value = n.toLocaleString()
                // $(this).val(value);
                var value = n.toLocaleString('en-US')
                $(this).val(value.replace(/\./g, ','));
            } else {
                $(this).val(0);
            }
        });

        function changeAmount(id) {
            $.ajax({
                url: "{{ route('transaction.show', ':id') }}".replace(':id', id),
                success(data) {
                    $('#form-change-amount').attr('action', "{{ route('transaction.change-amount', ':id') }}"
                        .replace(
                            ':id', id))
                    $('#modal-change-amount').modal('show')
                    // Reset total bayar setelah diskon saat modal dibuka
                    $('#after_discount').val('');
                    $('#after_discount_group').css('display', 'none');
                    $('#total_discount_promo').val('');
                    $('#discount_promo_info').text('');
                    $('#promo_type').val('');
                    if (data.data.transaction.discount_promo !== null && data.data.transaction.discount_promo !== undefined && data.data.transaction.discount_promo > 0) {
                        var subTotal = data.data.transaction.pay_amount + data.data.transaction.discount_promo;
                        $('#pay_amount').val(subTotal.toString().replace(
                            /\B(?=(\d{3})+(?!\d))/g, ","))
                        $('#discount_promo_group').css('display', '');
                        $('#discount_promo').val(data.data.transaction.discount_promo);
                    } else {
                        $('#pay_amount').val(data.data.transaction.pay_amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","))
                        $('#discount_promo_group').css('display', 'none');
                        $('#discount_promo').val('');
                    }
                    $('#note').val(data.data.transaction.note);
                    if (data.data.transaction.payment_method.type === "AUTO") {
                        $('#offline_payment_method_group').css('display', 'none');
                        $('input[name="offline_payment_method"]').prop('checked', false);
                    } else {
                        $('#offline_payment_method_group').css('display', '');
                        
                        // Hapus pilihan sebelumnya
                        $('input[name="offline_payment_method"]').prop('checked', false);
                        
                        // Pilih metode pembayaran offline yang sesuai
                        $('input[name="offline_payment_method"]').each(function() {
                            if ($(this).val() === data.data.transaction.offline_payment_method) {
                                $(this).prop('checked', true);
                            }
                        });
                    }
                    // show timestamp to datetime-local
                    // Convert the timestamp to datetime-local format
                    function formatDate(date) {
                        return date.getFullYear() + '-' +
                        ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                        ('0' + date.getDate()).slice(-2) + 'T' +
                        ('0' + date.getHours()).slice(-2) + ':' +
                        ('0' + date.getMinutes()).slice(-2);
                    }
                
                    if (data.data.transaction.paid_at) {
                        let paidDate = new Date(data.data.transaction.paid_at);
                        $('#paid_at').val(formatDate(paidDate));
                    }
                    
                    if (data.data.transaction.created_at) {
                    let createdDate;
                        if (typeof data.data.transaction.created_at === 'string') {
                            // Parsing the format "DD-MM-YYYY HH:MM"
                            let parts = data.data.transaction.created_at.split(' ');
                            let dateParts = parts[0].split('-');
                            let timeParts = parts[1].split(':');
                            
                            createdDate = new Date(
                            dateParts[2], // Year
                            dateParts[1] - 1, // Month (0-based)
                            dateParts[0], // Day
                            timeParts[0], // Hour
                            timeParts[1] // Minute
                            );
                        } else {
                            // Assuming it's a timestamp
                            createdDate = new Date(data.data.transaction.created_at * 1000);
                        }

                        $('#created_at').val(formatDate(createdDate));

                    }
                },
                error(err) {

                }
            })
        }

        // Gabungkan event handler menjadi satu
        $('#payment, #package, #status, #gym_place_id').on('change', function() {
            table();
        });

        function table() {
            var table = $('#datatable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('transaction.index') }}",
                    type: 'GET',
                    data: {
                        payment: $("#payment").val(),
                        package: $("#package").val(),
                        start_date: $("#start_date").val(),
                        end_date: $("#end_date").val(),
                        status: $('#status').val(),
                        gym_place_id: $('#gym_place_id').val()
                    },
                    beforeSend: function() {
                        $('#datatable tbody').empty();
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
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return row.user ? row.user.name : row.guest ? row.guest.name : 'GUEST';
                        }
                    },
                    {
                        data: 'payment_code',
                        name: 'payment_code'
                    },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            let offline_payment = data.offline_payment_method ? " (" + data
                                .offline_payment_method + ")" : "";
                            let data_payment_method = data.payment_method.type == "AUTO" ? data
                                .payment_method.name : data.payment_method.name +
                                offline_payment;
                            return data_payment_method;
                        }
                    },
                    {
                        data: 'note',
                        name: 'note'
                    },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            if (row?.transaction_details && row?.transaction_details.length > 0) {
                                let items = '';
                                for (item of row?.transaction_details ?? []) {
                                    if (item.parentable_type == "App\\Models\\Event") {
                                        items += `<p>${item.parent?.name} (Tiket Event)</p>`;
                                        if (row?.event_ticket_order?.event_ticket_order_detail_groups) {
                                            for (eventTicketOrderDetail of row?.event_ticket_order?.event_ticket_order_detail_groups) {
                                                items += `<li><i>${eventTicketOrderDetail.event_ticket?.name} (${eventTicketOrderDetail.total_quantity}Tiket)</i></li>`;
                                            }
                                        }
                                    } else {
                                        items += `<li><i>${item.parent?.name}</i></li>`;
                                    }
                                }
                                return `<ul>${items}</ul>`;
                            } else if (row?.user_timeoff_history) {
                                let month = Math.round(row?.user_timeoff_history?.duration / 30);
                                return `<ul><li><i>Cuti Membership ${month} Bulan</i></li></ul>`;
                            } else {
                                let name = row?.annual_payment_history?.name;
                                return `<ul><li><i>${name}</i></li></ul>`;
                            }
                        }
                    },
                    {
                        data: null,
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return '<small>Diskon Produk: Rp' + row.discount_product.toString()
                                .replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '<br>Diskon Promo: Rp' + row.discount_promo.toString().replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '<br>Total Bayar: Rp' + row.pay_amount.toString().replace(
                                    /\B(?=(\d{3})+(?!\d))/g, ",") +
                                '</small></i>' +
                                '<br>' +
                                '<a class="btn-superadmin" onclick=changeAmount("' + row.id +
                                '")><i class="ki-duotone ki-notepad-edit fs-2">' +
                                '<span class="path1"> </span> <span class="path2" > </span><span class="path3"> </span></i></a>'
                        }
                    },
                    {
                        data: 'paid_at',
                        name: 'paid_at'
                    },
                    {
                        data: null,
                        sortable: false,
                        searchable: false,
                        render: function(data, type, row, meta) {
                            return row.payment_receipt_status;
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function(data, type, row) {
                            if (data == 'PAID') {
                                return `<span class="badge badge-light-success">LUNAS</span>`;
                            } else if (data == 'PENDING') {
                                return `<span class="badge badge-light-warning">${data}</span>`;
                            } else {
                                return `<span class="badge badge-light-danger">${data}</span>`;
                            }
                        },
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -2
                    },
                ],
                rowCallback: function(row, data, index) {
                    // jika user belum upload bukti pembayaran
                    if(data.payment_method.type === "OTS" && !data.payment_receipt){
                        $(row).addClass('table-warning');
                    }
                }
            });

            document.getElementById('filter_excel_start_date').value = $("#start_date").val();
            document.getElementById('filter_excel_end_date').value = $("#end_date").val();
            document.getElementById('filter_excel_payment').value = $("#payment").val();
            document.getElementById('filter_excel_package').value = $("#package").val();
            document.getElementById('filter_excel_gym_place').value = $("#gym_place_id").val();
            document.getElementById('filter_pdf_start_date').value = $("#start_date").val();
            document.getElementById('filter_pdf_end_date').value = $("#end_date").val();
            document.getElementById('filter_pdf_payment').value = $("#payment").val();
            document.getElementById('filter_pdf_package').value = $("#package").val();
            document.getElementById('filter_pdf_gym_place').value = $("#gym_place_id").val();

            // Menyembunyikan tabel selama proses loading
            table.on('preXhr.dt', function(e, settings, data) {
                $('#datatable tbody').empty();
            });

            // Menampilkan tabel setelah data selesai dimuat
            table.on('draw.dt', function() {
                $('#datatable').fadeIn();
            });

            getRevenue()
        }

        function getRevenue() {
            $.ajax({
                url: "{{ route('transaction.revenue') }}",
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    payment: $("#payment").val(),
                    package: $("#package").val(),
                    gym_place_id: $('#gym_place_id').val()
                },
                success(data) {
                    $('#revenueTotal').text('Rp' + data.revenue_total.toString().replace(
                        /\B(?=(\d{3})+(?!\d))/g, ","))
                },
                error(err) {

                }
            })
        }

        function uploadImage(id){
            $("#form-upload-payment-receipt").attr('action',"{{url('transaction/upload_payment_receipt')}}/"+id);
            $("#kt_modal_upload_payment_receipt").modal("show")
        }
        
        function showImage(id){
            $.ajax({
                url: "{{url('transaction/payment_receipt')}}/"+id,
                method: 'get',
                type: 'json',
            }).done(function(data) {
                var modalImg = document.getElementById("img01");
                $("#modal-payment-receipt-popup").modal("show");
                modalImg.src = data.payment_receipt; 
            });
        }

        function closeModal() {
            var modal = document.getElementById("modal-payment-receipt-popup");
            modal.style.display = "none";
        }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function calculateDiscountPromo() {
            const promoType = document.getElementById('promo_type').value;
            const payAmountInput = document.getElementById('pay_amount');
            const discountPromoInput = document.getElementById('discount_promo');
            const info = document.getElementById('discount_promo_info');
            const afterDiscountInput = document.getElementById('after_discount');
            const totalDiscountPromoInput = document.getElementById('total_discount_promo');
            let payAmount = payAmountInput.value.replace(/[^0-9]/g, '');
            payAmount = payAmount ? parseInt(payAmount) : 0;
            let discount = discountPromoInput.value ? parseFloat(discountPromoInput.value) : 0;
            let afterDiscount = payAmount;
            let totalPotongan = 0;
            // Ubah format Rp menjadi 200,000 (tanpa titik, pakai koma)
            function formatRupiahTanpaRp(angka) {
                // Pastikan angka adalah integer/float
                angka = angka || 0;
                return angka.toLocaleString('en-US');
            }

            if (promoType === 'percent') {
                if (discount > 100) discount = 100;
                let calculated = Math.round((discount / 100) * payAmount);
                info.innerText = 'Diskon: ' + formatRupiahTanpaRp(calculated);
                afterDiscount = payAmount - calculated;
                totalPotongan = calculated;
            } else if (promoType === 'nominal') {
                info.innerText = '';
                afterDiscount = payAmount - discount;
                totalPotongan = discount;
            } else {
                info.innerText = '';
                afterDiscount = payAmount;
                totalPotongan = 0;
            }
            if (promoType !== '') {
                if (afterDiscount < 0) afterDiscount = 0;
                afterDiscountInput.value = formatRupiahTanpaRp(afterDiscount);
            } else {
                afterDiscountInput.value = '';
            }
            // Set nilai total potongan ke input hidden
            totalDiscountPromoInput.value = totalPotongan;
        }
        function togglePromoFields() {
            const promoType = document.getElementById('promo_type').value;
            const discountPromoGroup = document.getElementById('discount_promo_group');
            const afterDiscountGroup = document.getElementById('after_discount_group');
            const discountPromoInput = document.getElementById('discount_promo');
            const afterDiscountInput = document.getElementById('after_discount');
            if (promoType === '') {
                discountPromoGroup.style.display = 'none';
                afterDiscountGroup.style.display = 'none';
                discountPromoInput.value = '';
                afterDiscountInput.value = '';
            } else {
                discountPromoGroup.style.display = '';
                afterDiscountGroup.style.display = '';
            }
        }
        document.getElementById('promo_type').addEventListener('change', function() {
            const promoType = this.value;
            const discountPromoInput = document.getElementById('discount_promo');
            discountPromoInput.value = '';
            if (promoType === 'percent') {
                discountPromoInput.setAttribute('max', '100');
                discountPromoInput.setAttribute('step', '0.01');
                discountPromoInput.placeholder = 'Masukkan persen promo (0-100)';
            } else if (promoType === 'nominal') {
                discountPromoInput.removeAttribute('max');
                discountPromoInput.setAttribute('step', '1');
                discountPromoInput.placeholder = 'Masukkan nominal promo';
            } else {
                discountPromoInput.placeholder = '';
            }
            togglePromoFields();
            calculateDiscountPromo();
        });
        document.getElementById('discount_promo').addEventListener('input', function() {
            calculateDiscountPromo();
        });
        document.getElementById('pay_amount').addEventListener('input', function() {
            calculateDiscountPromo();
        });
        // Inisialisasi tampilan saat pertama kali
        togglePromoFields();
        calculateDiscountPromo();
    });
</script>

<script>
   function downloadExport(event) {
        // Cegah form submit default
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        // Tampilkan loading screen menggunakan SweetAlert
        Swal.fire({
            title: 'Sedang memproses export...',
            text: 'Mohon tunggu, file sedang disiapkan.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Ambil nilai dari semua filter
        const startDate = $('#filter_excel_start_date').val();
        const endDate = $('#filter_excel_end_date').val();
        const payment = $('#payment').val();
        const package = $('#package').val();
        const status = $('#status').val();
        const gymPlaceId = $('#filter_excel_gym_place').val();

        // Debug: log nilai yang diambil
        console.log('Export Parameters:', {
            start_date: startDate,
            end_date: endDate,
            payment: payment,
            package: package,
            status: status,
            gym_place_id: gymPlaceId
        });

        // Buat parameter URL dengan pengecekan nilai kosong
        const params = new URLSearchParams();
        
        if (startDate && startDate.trim() !== '') params.append('start_date', startDate);
        if (endDate && endDate.trim() !== '') params.append('end_date', endDate);
        if (payment && payment.trim() !== '') params.append('payment', payment);
        if (package && package.trim() !== '') params.append('package', package);
        if (status && status.trim() !== '') params.append('status', status);
        if (gymPlaceId && gymPlaceId.trim() !== '') params.append('gym_place_id', gymPlaceId);

        const queryString = params.toString();
        const url = '/transactions/export/all' + (queryString ? '?' + queryString : '');

        console.log('Download URL:', url); // Debug URL

        // Proses download
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.blob();
            })
            .then(blob => {
                // Create download link
                const downloadUrl = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = downloadUrl;
                
                // Generate filename with date range
                const start = startDate ? formatDateForFilename(startDate) : 'all';
                const end = endDate ? formatDateForFilename(endDate) : 'all';
                a.download = `transactions_export_${start}_to_${end}.xlsx`;
                
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(downloadUrl);
                
                Swal.fire({
                    icon: 'success',
                    title: 'Export Selesai',
                    text: 'File berhasil diunduh!',
                    timer: 2000,
                    showConfirmButton: false
                });
            })
            .catch(error => {
                console.error('Download error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Export Gagal',
                    text: 'Terjadi kesalahan saat mengunduh file.',
                    timer: 3000,
                    showConfirmButton: true
                });
            });
    }

    // Helper function to format date for filename
    function formatDateForFilename(dateString) {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0].replace(/-/g, '');
    }

    // Update the updateAllFilters function to include all filters
    function updateAllFilters(filters) {
        if (filters.start_date) $('#filter_excel_start_date').val(filters.start_date);
        if (filters.end_date) $('#filter_excel_end_date').val(filters.end_date);
        if (filters.payment) $('#payment').val(filters.payment);
        if (filters.package) $('#package').val(filters.package);
        if (filters.status) $('#status').val(filters.status);
        if (filters.gym_place_id) $('#filter_excel_gym_place').val(filters.gym_place_id);
    }
</script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 untuk user_id
    $('#user_id').select2({
        placeholder: "Pilih Member",
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modal-add-transaction') // Penting untuk modal
    });
    // Pastikan Select2 bekerja di dalam modal
    $('#modal-add-transaction').on('shown.bs.modal', function () {
        $('#user_id').select2({
            dropdownParent: $('#modal-add-transaction')
        });
    });
});

let productIndex = 0;

function addShopProduct() {
    productIndex++;

    const container = document.createElement('div');
    container.className = 'row mt-3 product-row';
    container.setAttribute('data-index', productIndex);

    container.innerHTML = `
        <div class="col-1 mt-1">
            <label class="fs-6 fw-bold form-label mt-3" for="">Aksi</label><br>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeProductRow(this)">
                <i class="fa fa-trash"></i>
            </button>
        </div>
        <div class="col-5">
            <label class="fs-6 fw-bold form-label mt-3" for="shop_product_id_${productIndex}">
                <span class="required">Nama Produk</span>
            </label>
            <select class="form-select" name="shop_product_id[]" id="shop_product_id_${productIndex}" onchange="varianShopProduct(this)" required>
                <option value="">--Pilih Produk--</option>
                @foreach ($shopProducts as $shopProduct)
                    <option value="{{ $shopProduct->id }}">{{ $shopProduct->name }} {{ $shopProduct->stock ? ", Stock : {$shopProduct->stock}" : "" }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-4 d-none varian-wrapper" id="varian_wrapper_${productIndex}">
            <label class="fs-6 fw-bold form-label mt-3" for="shop_product_varian_id_${productIndex}">
                <span class="required">Varian</span>
            </label>
            <select name="shop_product_varian_id[${productIndex}]" class="form-select" id="shop_product_varian_id_${productIndex}">
            </select>
        </div>
        <div class="col-2">
            <label class="fs-6 fw-bold form-label mt-3" for="quantity_${productIndex}">
                <span class="required">Kuantitas</span>
            </label>
            <input type="number" class="form-control" name="quantity[]" id="quantity_${productIndex}" required>
        </div>
    `;

    const productRowsContainer = document.getElementById('product-rows-container');
    productRowsContainer.appendChild(container);
}

function removeProductRow(button) {
    const row = button.closest('.product-row');
    row.remove();
}

function varianShopProduct(selectElement) {
    const productId = selectElement.value;
    const wrapper = selectElement.closest('.product-row');
    const index = wrapper.getAttribute('data-index') || 0;
    const varianWrapper = wrapper.querySelector('.varian-wrapper');
    const varianSelect = document.getElementById(`shop_product_varian_id_${index}`);


    const url = "{{ url('transaction/shop-product') }}/" + productId + "/varian";

    axios.get(url)
        .then(function(response) {
            if (response.data && response.data.length > 0) {
                varianSelect.innerHTML = '<option value="">--Pilih Varian--</option>';
                response.data.forEach(element => {
                    varianSelect.innerHTML += `<option value="${element.id}">${element.name}, Stock: ${element.stock}</option>`;
                });
                if (varianWrapper) {
                    varianWrapper.classList.remove('d-none');
                    varianSelect.setAttribute('required', '');
                }
            } else {
                varianWrapper.classList.add('d-none');
                varianSelect.removeAttribute('required');
                varianSelect.innerHTML = '';
            }
        })
        .catch(function(error) {
            console.error('Error:', error);
        });
}
</script>
@endpush