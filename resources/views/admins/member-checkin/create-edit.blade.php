@extends('layouts.master', ['title' => 'Detail CheckIn','main' => 'List CheckIn'])
@push('css')
<style>
    [data-bs-theme="light"] {
        --color-gray: rgb(241, 238, 238);
    }

    [data-bs-theme="dark"] {
        --color-gray: rgb(38, 38, 38);
    }

    .hr {
        background: #D7DBFF;
        height: 1px;
    }

    label {
        font-size: 12px;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 18px */
        letter-spacing: 0.06px;
    }

    .text-label {
        /* Paragraph 2/Regular */
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    table.dataTable td {
        text-align: center;
    }

    h1 {
        /* Heading 4/Bold */
        font-family: 'Gothic A1';
        font-size: 1.5rem;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 1.875rem */
        letter-spacing: 0.00625rem;
    }

    h2 {
        color: var(--Black, #000);
        /* Paragraph 2/Bold */
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    .text-sub-title {
        color: #B5B5C3;
        font-family: 'Gothic A1';
        font-size: 0.875rem;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 1.3125rem */
        letter-spacing: 0.00438rem;
    }

    .text-label-grey {
        color: var(--Grey, #A5A5A5);
        /* Paragraph 2/Bold */
        font-size: 12px;
        font-style: normal;
        font-weight: 500;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    .card .card-body {
        padding: 1.2rem 2rem !important;
    }

    hr {
        border-top: 0.1px solid #D7DBFF !important;
        outline: none;
        border: none;
        height: 1px !important;
    }

    .btn.btn-active-color-primary.active,
    .btn.btn-active-color-primary:hover {
        color: #3B4CED !important;
    }

    .btn.btn-active-light.active,
    .btn.btn-active-light:hover,
    .nav-link:hover,
    .btn.btn-active-light.active:hover {
        background: none !important;
    }

    .tab-content {
        font-family: 'Gothic A1' !important;
    }

    .card-body.v2 {
        padding: 2rem !important;
    }

    .table_header {
        padding: .5rem 1rem !important;
        border: 1px solid gray !important;
        border-radius: 1rem !important;
        margin-bottom: 1rem;
        background-color: var(--color-gray);
    }

    .table-bordered> :not(caption)>*,
    .table-bordered> :not(caption)>*>* {
        border-width: 0 !important;
    }
</style>
@endpush
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div id="kt_content_container" class="app-container container-xxl">
        <div class="row">
            <div class="col-sm-6">
                <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                    <div id="kt_content_container">
                        <div class="card">
                            <div class="card-body">
                                <div
                                    class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Detail Member</span>
                                    </h3>
                                    {{-- <div class="d-flex flex-wrap gap-4 align-items-center">
                                        <a href="{{ route('shop-order.export-invoice', @$shopOrder->id) }}"
                                            class="btn btn-primary btn-sm text-nowrap">
                                            <i class="ki-duotone ki-exit-up fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Cetak Invoice
                                        </a>
                                    </div> --}}
                                </div>
                                <div class="table_header">
                                    <table class="table table-sm table-bordered" rules="none">
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Waktu CheckIn</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @if ($checkIn = $user->membership_activities()?->latest()->first()->check_in)
                                                {{ \Carbon\Carbon::parse($checkIn)->locale('id')->isoFormat('HH:mm DD MMM YYYY') }}
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Fasilitas</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{-- <div class="row"> --}}
                                                    <div class="col-auto gap-2">
                                                        <input type="checkbox" {{ $user->membership_activities()?->latest()->first()->user_activity?->ice_bath ? "checked" : "" }} disabled>
                                                        <label for="bath">Ice Bath</label>
                                                    </div>
                                                    <div class="col-auto gap-2">
                                                        <input type="checkbox" {{ $user->membership_activities()?->latest()->first()->user_activity?->sauna ? "checked" : "" }} disabled>
                                                        <label for="bath">Sauna</label>
                                                    </div>
                                                {{-- </div> --}}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Nama</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $user->name ?? '' }}</td>
                                        </tr>
                                        {{-- <tr>
                                            <td class="border-bottom-0" width=36%>Program</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @foreach ($transaction->transaction_details as $detail)
                                                <li><i>{{ $detail->parent?->name }}</i></li>
                                                @endforeach
                                            </td>
                                        </tr> --}}
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Membership ID</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ $user->membership_user ? $user->membership_user->member_id : '' }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Membership</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ $user->active_memberships->map(function ($membership) {
                                                return $membership->membership?->name ??
                                                $membership->gym_class_bundling?->name;
                                                })->implode(', ') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Masa Aktif Membership</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ $user->active_memberships->implode('expiry_date', ', ') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Personal Trainer</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ $user->active_personal_trainer->implode('personal_trainer.name', ',
                                                ') }}
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Masa Aktif Personal Trainer</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ $user->active_personal_trainer->implode('expiry_date', ', ') }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                {{-- <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->name }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Telepon User
                                        </td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->phone }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Email User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->email }}</td>
                                    </tr>
                                </table> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                    <div id="kt_content_container">
                        <div class="card">
                            <div class="card-body">
                                <div
                                    class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Informasi Loker</span>
                                    </h3>
                                    <div class="d-flex flex-wrap gap-4 align-items-center">
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Loker</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $user->user_locker->where('status',
                                            'ACTIVE')->first()->locker->name ?? 'Tanpa Loker' }}</td>
                                        </td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Status</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">
                                            @if ($user->user_locker?->where('status', 'ACTIVE')->first())
                                            <span class='badge text-white bg-success'>Aktif</span>
                                            @else
                                            <span class='badge text-white bg-danger'>Tidak Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    {{-- <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal
                                            pengambilan</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $shopOrder->pickup_date ? date('d F Y H:i',
                                            strtotime($shopOrder->pickup_date)) : "-" }}</td>
                                    </tr> --}}
                                    {{-- <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Harga Produk
                                        </td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="left">Rp{{ number_format($transaction->sub_total,
                                            0, ',', '.') }}</td>
                                    </tr> --}}
                                    <tr height="40px">
                                        <td colspan="3" class="text-center">
                                            <button id="btnChangeLocker" data-bs-toggle="modal"
                                                onclick="getLocker('{{ $user->id }}')"
                                                data-bs-target="#modalChangeLocker"
                                                class="btn btn-primary btn-sm text-nowrap btn-edit">
                                                <i class="ki-duotone ki-notepad-edit fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                Ubah Locker
                                            </button>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
            <div id="kt_content_container">
                <div class="card">
                    <div class="card-header mt-6">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">List Pembelian Hari Ini</span>
                        </h3>
                        <!--end::Card title-->
                        <!--begin::Card toolbar-->
                        <div class="card-toolbar">
                            <!--begin::Button-->
                            <a type="button" class="btn btn-sm btn-primary btn-create" data-bs-target="#modaladdShop"
                                onclick="showCartShop($user->id)" data-bs-toggle="modal">
                                <i class="fa fa-plus"></i>
                                Pesanan</a>
                            <!--end::Button-->
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" rules="all"
                                id="shop_order-detail">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th class="text-center">#</th>
                                        <th class="text-center">No Pesanan</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Tanggal Pesanan</th>
                                        <th class="text-center">Total Harga</th>
                                        {{-- <th>Aksi</th> --}}
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalChangeLocker" tabindex="-1" aria-labelledby="modalChangeLockerLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('member-checkin.change-locker') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalChangeLockerLabel">Ubah Loker Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body
                    d-flex
                    flex-column
                    gap-3">
                    <div class="form-group mb-4">
                        <label for="locker_id" class="text-label mb-4">Loker</label>
                        <select class="form-select" name="locker_id" id="locker_id" required>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal modal-lg fade" id="modaladdShop" tabindex="-1" aria-labelledby="modaladdShopLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('member-checkin.add-to-cart') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <div class="modal-header">
                    <h1 class="modal-title" id="modaladdShopLabel">Tambah Pembelian</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body d-flex flex-column gap-3">
                    <div class="row align-items-end">
                        <div class="form-group mb-4 col-6 col-md-3">
                            <label for="product_id" class="text-label mb-4">Produk</label>
                            <select class="form-select" name="product_id" id="product_id" required>
                                <option value="">Pilih Produk</option>
                                {{-- Product options will be populated dynamically --}}
                            </select>
                        </div>

                        <div class="form-group col-6 col-md-3 mb-4" style="display: none;" id="variantProduct">
                            {{-- Variant options will be populated dynamically --}}
                            {{-- <div id="variantProduct"></div> --}}
                        </div>

                        <div class="form-group mb-4 col-6 col-lg-2 col-md-4">
                            <label for="stock" class="text-label mb-4">Stok</label>
                            <input type="number" class="form-control" id="stock" value="0" readonly>
                        </div>

                        <div class="form-group mb-4 col-6 col-lg-2 col-md-4">
                            <label for="quantity" class="text-label mb-4">Jumlah</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" min="1" required>
                        </div>

                        <div class="form-group mb-4 col-2">
                            <button type="button" class="btn btn-primary" id="btnAddToCart">Tambah</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex gap-5 align-items-center">
                            <h1 class="text-capitalize fs-4 fw-500 mb-5">List Keranjang Belanja</h1>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" rules="all"
                                id="shop_cart">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th class="text-center" style="width: 5%">#</th>
                                        <th class="text-center">Nama Produk</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-center">Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="addShopOrder">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    function getLocker(user_id) {
            axios.get("{{ route('locker.search') }}", {
                params: {
                    user_id: user_id
                }
            })
            .then(function(response) {
                if (response.data.length > 0) {
                    let lockers = response.data;
                    $('#locker_id').empty();
                    $('#locker_id').append('<option value="">Tanpa Loker</option>');
                    lockers.forEach(function(locker) {
                        $('#locker_id').append(`<option value="${locker.id}">${locker.name}</option>`);
                    });
                } else {
                    toastr.error(response.data);
                }
            })
            .catch(function(error) {
                console.log(error);
                toastr.error(error.message);
            });
        }
</script>
<script>
    function showCartShop(userId) {
        // Fetch the product data from an API endpoint
        fetch(`{{ route('member-checkin.shop-product') }}?user_id=${userId}`)
        .then(response => response.json())
        .then(data => {
            const productSelect = document.getElementById('product_id');
            productSelect.innerHTML = '<option value="">Pilih Produk</option>'; // Clear existing options
            
            // Populate the select options with fetched data
            data.forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.text = product.name;
                productSelect.appendChild(option);
            });
        })
        .catch(error => {
            console.error('Error fetching products:', error);
        });
    }
        
    // Add event listener to trigger the function when the modal is shown
    document.getElementById('modaladdShop').addEventListener('show.bs.modal', function (event) {
        const userId = document.querySelector('input[name="user_id"]').value;
        showCartShop(userId);
    });

    // on change product_id get variant
    $('#product_id').on('change', function() {
        let product_id = $(this).val();
        axios.get("{{ route('member-checkin.shop-product-variant') }}", {
            params: {
                shop_product_id: product_id
            }
        })
        .then(function(response) {
            if (response.data.length > 0) {
                let variants = response.data;
                $('#variantProduct').show();
                $('#variantProduct').empty();
                $('#variantProduct').append('<label for="variant_id" class="text-label mb-4">Variant</label>');
                $('#variantProduct').append('<select class="form-select" name="variant_id" id="variant_id">');
                variants.forEach(function(variant) {
                    $('#variant_id').append(`<option value="${variant.id}">${variant.name}</option>`);
                });
                $('#variantProduct').append('</select>');
                $('#stock').val(variants[0].stock);     // get first stock

                // Menambahkan event listener untuk mengambil stok saat variant berubah
                $('#variant_id').on('change', function() {
                    let variant_id = $(this).val();
                    let selectedVariant = variants.find(variant => variant.id === variant_id);
                    if (selectedVariant && selectedVariant.stock) {
                        $('#stock').val(selectedVariant.stock);
                        // toastr.info('Stok untuk varian ini: ' + selectedVariant.stock);
                    } else {
                        $('#stock').val(0);
                        // toastr.error('Stok tidak tersedia');
                    }
                });
            } else {
                axios.get("{{ route('member-checkin.shop-product-stock') }}", {
                    params: {
                        shop_product_id: product_id
                    }
                })
                .then(function(stockResponse) {
                    $('#stock').val(stockResponse.data);
                })
                .catch(function(stockError) {
                    $('#stock').val(0);
                });
                $('#variantProduct').hide();
                toastr.error('Data varian kosong');
            }
        })
        .catch(function(error) {
            console.log(error);
            toastr.error(error.message);
        });
    });

// on click add to cart
$('#btnAddToCart').on('click', function() {
    $('#btnAddToCart').prop('disabled', true)
    let product_id = $('#product_id').val();
    let variant_id = $('#variant_id').val();
    let quantity = $('#quantity').val();
    let user_id = $('input[name="user_id"]').val();
    axios.post("{{ route('member-checkin.add-to-cart') }}", {
        shop_product_id: product_id,
        variant_id: variant_id,
        quantity: quantity,
        user_id: user_id
    })
    .then(function({data}) {
        toastr.success(data.success);
        $('#shop_cart').DataTable().ajax.reload();
        $('#btnAddToCart').prop('disabled', false)
    })
    .catch(function(error) {
        console.log(error);
        toastr.error(error.message);
        $('#btnAddToCart').prop('disabled', false)
    });
});

// onclick add shop order
$('#addShopOrder').on('click', function(event) {
    event.preventDefault()
    $('#addShopOrder').prop('disabled', true)
    let user_id = $('input[name="user_id"]').val();
    axios.post("{{ route('member-checkin.add-shop-order') }}", {
        user_id: user_id
    })
    .then(function({data}) {
        toastr.success(data.success);
        $('#shop_order-detail').DataTable().ajax.reload();
        $('#shop_cart').DataTable().ajax.reload();
        $('#modaladdShop').modal('hide');
        $('#addShopOrder').prop('disabled', false)
    })
    .catch(function(error) {
        console.log(error);
        toastr.error(error.message);
        $('#addShopOrder').prop('disabled', false)
    });
});


</script>
<script>
    $(document).ready(function() {
    var table = $('#shop_order-detail').DataTable({
        ordering: false,
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('member-checkin.edit', $user->id) }}",
            type: 'GET',
            data: {
                type: 'ORDER',
                user_id: '{{ $user->id }}'
            }
        },
        language: {
            paginate: {
                next: "<i class='fa fa-angle-right'></i>",
                previous: "<i class='fa fa-angle-left'></i>"
            },
            loadingRecords: "Loading...",
            processing: "Processing..."
        },
        columns: [
            {
                data: null,
                sortable: false,
                searchable: false,
                responsivePriority: -3,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            {
                data: 'order_number',
                name: 'order_number'
            },
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'pickup_date',
                name: 'pickup_date'
            },
            {
                data: 'total',
                name: 'total'
            },
            // {
            //     data: 'action',
            //     name: 'action',
            //     orderable: false,
            //     searchable: false,
            //     responsivePriority: -1
            // }
        ]
    });

    $('#gym_place_id').on('change', function() {
        table.ajax.url(`membership-history?gym_place_id=${$('#gym_place_id').val()}`).load();
    });

    var cartTable = $('#shop_cart').DataTable({
    ordering: false,
    processing: true,
    serverSide: true,
    responsive: true,
    ajax: {
    url: "{{ route('member-checkin.edit', $user->id) }}",
    type: 'GET',
    data: {
        type: 'CART',
        user_id: '{{ $user->id }}'
    }
    },
    language: {
    paginate: {
    next: "<i class='fa fa-angle-right'></i>",
    previous: "<i class='fa fa-angle-left'></i>"
    },
    loadingRecords: "Loading...",
    processing: "Processing..."
    },
    columns: [
    {
    data: null,
    sortable: false,
    searchable: false,
    responsivePriority: -3,
    render: function(data, type, row, meta) {
    return meta.row + meta.settings._iDisplayStart + 1;
    }
    },
    {
    data: 'productwithvariant',
    name: 'productwithvariant'
    },
    {
    data: 'quantity',
    name: 'quantity',
    },
    {
    data: 'price',
    name: 'price'
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
});
</script>
@endpush