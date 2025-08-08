@extends('layouts.master', ['title' => 'Detail Pesanan','main' => 'List Pesanan'])
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
                                        <span class="card-label fw-bold fs-3 mb-1">Transaksi Detail</span>
                                    </h3>
                                    <div class="d-flex flex-wrap gap-4 align-items-center">
                                        <a href="{{ route('shop-order.export-invoice', @$shopOrder->id) }}"
                                            class="btn btn-primary btn-sm text-nowrap">
                                            <i class="ki-duotone ki-exit-up fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Cetak Invoice
                                        </a>
                                    </div>
                                </div>
                                <div class="table_header">
                                    <table class="table table-sm table-bordered" rules="none">
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Kode Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $transaction->payment_code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Tanggal Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ date('d F Y H:i',
                                                strtotime($transaction->created_at)) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Program</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @foreach ($transaction->transaction_details as $detail)
                                                <li><i>{{ $detail->parent?->name }}</i></li>
                                                @endforeach
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Status Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @if ($transaction->status == "PAID")
                                                <span class='badge text-white bg-success'>LUNAS</span>
                                                @elseif ($transaction->status == "PENDING")
                                                <span class='badge text-white bg-warning'>PENDING</span>
                                                @else
                                                <span class='badge text-white bg-danger'>{{ $transaction->status
                                                    }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->name ?? "GUEST" }}</td>
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
                                </table>
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
                                        <span class="card-label fw-bold fs-3 mb-1">Informasi Pemesanan</span>
                                    </h3>
                                    <div class="d-flex flex-wrap gap-4 align-items-center">
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">No Pesanan</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $shopOrder->order_number ?? '' }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Status</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label"><span class='badge text-white bg-success'>{{
                                                $shopOrder->translated_status
                                                }}</span></td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal
                                            pengambilan</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $shopOrder->pickup_date ? date('d F Y H:i',
                                            strtotime($shopOrder->pickup_date)) : "-" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Harga Produk
                                        </td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="left">Rp{{ number_format($transaction->sub_total,
                                            0, ',', '.') }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td colspan="3" class="text-center">
                                            <button id="btnChangeStatus" data-bs-toggle="modal"
                                                data-bs-target="#modalChangeStatus"
                                                class="btn btn-primary btn-sm text-nowrap">
                                                <i class="ki-duotone ki-notepad-edit fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                                Ubah Status
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
                    <div class="card-body">
                        <div class="d-flex gap-5 align-items-center">
                            <h1 class="text-capitalize fs-4 fw-500 mb-5">List Pembelian</h1>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" rules="all">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th class="text-center">#</th>
                                        <th class="text-center">Nama Produk</th>
                                        <th class="text-center">Harga</th>
                                        <th class="text-center">Jumlah</th>
                                        <th class="text-center">Total Harga</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shopOrder->shop_order_detail as $item)
                                    <tr class="text-dark fw-semibold">
                                        <td class="text-center" width=5%>{{ $loop->iteration }}</td>
                                        <td class="text-center" width=20%>{{ $item->product?->name ?? '-' }}<br>{{ $item->varian?->name ?? '' }}</td>
                                        <td class="text-center" width=20%>Rp{{ number_format($item->price, 0,
                                            ',', '.')
                                            }}</td>
                                        <td class="text-center" width=15%>{{ $item->quantity ?? '' }}</td>
                                        <td class="text-center" width=40%>Rp{{
                                            number_format(($item->price * $item->quantity), 0, ',', '.')
                                            }}</td>
                                    </tr>
                                    @endforeach
                                    <tr class="text-dark fw-semibold">
                                        <td colspan="4" class="text-center">Total</td>
                                        <td class="text-center">Rp{{ number_format($shopOrder->transaction?->pay_amount, 0,
                                            ',', '.') }}</td>
                                    </tr>
                                </tbody>
                                {{-- add button if $shopOrder->status == PAID to confirm pemesanan --}}
                                @if ($shopOrder->status == 'PAID')
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end">
                                            <form action="{{ route('shop-order.update', $shopOrder->id) }}"
                                                method="POST">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="status" value="PROCESS">
                                                <button type="submit" class="btn btn-primary btn-sm text-nowrap">
                                                    <i class="ki-duotone ki-check fs-3">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Konfirmasi Pemesanan
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modalChangeStatus" tabindex="-1" aria-labelledby="modalChangeStatusLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('shop-order.update', $shopOrder->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="modalChangeStatusLabel">Ubah Status Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body
                    d-flex
                    flex-column
                    gap-3">
                    <div class="form-group mb-4">
                        <label for="status" class="text-label mb-4">Status Pesanan</label>
                        <select class="form-select" name="status" id="status" required>
                            @foreach($types as $type => $label)
                            <option value="{{ $type }}" {{ $type==$shopOrder->status ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
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
    $(document).ready(function () {
            $('#btnChangeStatus').click(function () {
                $('#status').val('{{ $shopOrder->status }}');
            });
        });
</script>
@endpush