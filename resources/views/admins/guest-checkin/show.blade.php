@extends('layouts.master', ['title' => 'Detail Guest CheckIn','main' => 'List Guest CheckIn'])
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
            <div class="col-sm-8">
                <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                    <div id="kt_content_container">
                        <div class="card">
                            <div class="card-body">
                                <div
                                    class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Detail Guest</span>
                                    </h3>
                                </div>
                                {{-- <div class="table_header"> --}}
                                    <table class="table table-sm table-bordered" rules="none">
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Waktu CheckIn</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ \Carbon\Carbon::parse($guest->check_in)->locale('id')->isoFormat('HH:mm DD MMM YYYY') }}
                                            </td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Gym Place</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $guest->gym_place->name ?? '' }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Nama</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $guest->name ?? '' }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Phone</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $guest->phone ?? '' }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Gender</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $guest->gender == "MALE" ? 'Laki-laki' : 'Perempuan' }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Fasilitas</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{-- <div class="row"> --}}
                                                    <div class="col-auto gap-2">
                                                        <input type="checkbox" {{ $guest->ice_bath ? "checked" : "" }} disabled>
                                                        <label for="bath">Ice Bath</label>
                                                    </div>
                                                    <div class="col-auto gap-2">
                                                        <input type="checkbox" {{ $guest->sauna ? "checked" : "" }} disabled>
                                                        <label for="bath">Sauna</label>
                                                    </div>
                                                {{-- </div> --}}
                                            </td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Program</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $guest->transaction->transaction_details[0]->parent?->name }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td class="border-bottom-0" width=36%>Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0"><a href="{{ route('transaction.show', $guest->transaction->id) }}" target="_blank">{{ $guest->transaction->payment_code }}</a></td>
                                        </tr>
                                    </table>
                                {{-- </div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-4">
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
                                        <td class="text-label">{{ $guest->user_locker?->where('status', 'ACTIVE')->latest()->first()?->locker?->name ?? 'Tanpa Loker' }}</td>
                                        </td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Status</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">
                                            @if ($guest->user_locker?->where('status', 'ACTIVE')->latest()->first())
                                            <span class='badge text-white bg-success'>Aktif</span>
                                            @else
                                            <span class='badge text-white bg-danger'>Tidak Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr height="40px">
                                        <td colspan="3" class="text-center">
                                            <button id="btnChangeLocker" data-bs-toggle="modal"
                                                onclick="getLocker('{{ $guest->gender }}')"
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
    </div>
</div>
<div class="modal fade" id="modalChangeLocker" tabindex="-1" aria-labelledby="modalChangeLockerLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('guest.change-locker') }}" method="POST">
                @csrf
                <input type="hidden" name="guest_id" value="{{ $guest->id }}">
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
@endsection
@push('js')
<script>
    function getLocker(gender) {
        axios.get("{{ route('locker.search.guest') }}", {
            params: {
                gender: gender
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
            url: "{{ route('member-checkin.edit', $guest->id) }}",
            type: 'GET',
            data: {
                type: 'ORDER',
                user_id: '{{ $guest->id }}'
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
    url: "{{ route('member-checkin.edit', $guest->id) }}",
    type: 'GET',
    data: {
        type: 'CART',
        user_id: '{{ $guest->id }}'
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