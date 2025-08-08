@extends('layouts.master', ['title' => 'Detail Transaksi','main' => 'List Transaksi'])
@push('css')
<style>
    [data-bs-theme="light"] {
        --color-gray: rgb(241, 238, 238);
    }

    [data-bs-theme="dark"] {
        --color-gray: rgb(38,38,38);
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

    .table-bordered > :not(caption) > *,
    .table-bordered > :not(caption) > * > * {
        border-width: 0 !important;
    }
    
    #myImg {
        border-radius: 5px;
        cursor: pointer;
        transition: 0.3s;
    }

    #myImg:hover {opacity: 0.7;}

    /* The Modal (background) */
    .modal-content-payment-receipt {
        height: 100%;
        width: 100%;
        object-fit: contain;
        max-height: 78vh;
        margin: 0 auto;
    }

    /* Tambahkan style untuk term and condition */
    .term-content {
        color: #000000;
        font-size: 14px;
        line-height: 1.6;
    }

    .term-content p {
        color: #000000;
        margin-bottom: 1rem;
    }

    @media print {
        .term-content {
            color: #000000 !important;
            font-size: 14px !important;
            line-height: 1.6 !important;
        }

        .term-content p {
            color: #000000 !important;
            margin-bottom: 1rem !important;
        }

        .term-content * {
            color: #000000 !important;
        }
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
                                <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                                    <div class="d-flex align-items-start gap-3 mb-0">
                                        <a href="{{ route('transaction.index') }}">
                                            <span class="menu-icon back pt-1">
                                                <i class="ki-duotone ki-arrow-left">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                        </a>
                                        <span class="card-label fw-bold fs-3 mb-1">Transaksi Detail</span>
                                    </div>
                                    <div class="d-flex flex-wrap gap-4 align-items-center">
                                        @if ($transaction->status !== 'PAID')
                                        <a data-id="{{ $transaction->id }}" data-status="{{ $transaction->status }}" class="btn btn-info btn-sm text-nowrap btn-update-status">Ubah Status</a>
                                        @endif
                                        <a href="{{ route('transaction.export.invoice', $transaction->id) }}" class="btn btn-primary btn-sm text-nowrap">
                                            <i class="ki-duotone ki-exit-up fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                            Cetak Invoice
                                        </a>
                                        <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal" data-bs-target="#modal_term_condition">
                                            <i class="ki-duotone ki-document fs-3">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                            Cetak Term & Condition
                                        </button>
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
                                            <td class="border-bottom-0">{{ date('d F Y H:i', strtotime($transaction->created_at)) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Program</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @if ($transaction->transaction_details()->exists())
                                                    @foreach ($transaction->transaction_details as $detail)
                                                        @if ($detail->parentable_type == "App\Models\Event")
                                                            <p>{{ $detail->parent?->name . " (Tiket Event)" }}</p>
                                                            @foreach ($transaction->event_ticket_order->eventTicketOrderDetailGroups as $eventTicketOrderDetail)
                                                                <li><i>{{ $eventTicketOrderDetail->eventTicket?->name . " ({$eventTicketOrderDetail->total_quantity}Tiket)"}}</i></li>
                                                            @endforeach
                                                        @else
                                                            <li><i>{{ $detail->parent?->name }}</i></li>
                                                        @endif
                                                    @endforeach
                                                @elseif ($transaction->user_timeoff_history)
                                                    @php
                                                        $month = round($transaction->user_timeoff_history->duration / 30);
                                                    @endphp
                                                    <i type="button" onclick="window.location = '{{ route('user-timeoff.show', $transaction->user_timeoff_history->id) }}'">Cuti Membership {{ $month }} Bulan</i>
                                                @elseif ($transaction->annual_payment_history)        
                                                    <i type="button" onclick="window.location = '{{ route('annual-payment.show', $transaction->annual_payment_history->id) }}'">{{ $transaction->annual_payment_history->name }}</i>
                                                @endif
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
                                                <span class='badge text-white bg-danger'>{{ $transaction->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Note</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                {{ $transaction->note }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    @if ($transaction->user)
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->name ?? "GUEST" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Telepon User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->phone ?? "-" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Email User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->user?->email ?? "-" }}</td>
                                    </tr>
                                    @else
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->guest?->name ?? "GUEST" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Telepon User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->guest?->phone ?? "-" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Jenis Kelamin</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">
                                            @if ($transaction->guest || $transaction->user)
                                            {{ $transaction->guest?->gender == "MALE" ? "Laki-laki" : "Perempuan" }}</td>
                                            @else
                                            -
                                            @endif
                                    </tr>
                                    @endif
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
                                <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Pembayaran Detail</span>
                                    </h3>
                                    <div class="d-flex flex-wrap gap-4 align-items-center">
                                    </div>
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Metode Pembayaran</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">
                                            @php
                                                $offline_payment = $transaction->offline_payment_method ? " (" . $transaction->offline_payment_method . ")" : "";
                                            @endphp
                                            {{ $transaction->payment_method->type == "AUTO" ? $transaction->payment_method->name : $transaction->payment_method->name . $offline_payment }}
                                        </td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Batas Pembayaran</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ date('d F Y H:i', strtotime($transaction->expiry_time)) ?? "-" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Pembayaran</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $transaction->paid_at ? date('d F Y H:i', strtotime($transaction->paid_at)) : "-" }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Harga Produk</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="right">Rp{{ number_format($transaction->sub_total, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Diskon Produk</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="right">Rp{{ number_format($transaction->discount_product, 0, ',', '.') }}</td>
                                    </tr>
                                    {{-- Tampilkan diskon promo --}}
                                    <tr height="40px">
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">
                                            Diskon Promo
                                            @if ($transaction->membership_price_reset_code)
                                                <br>
                                                (<span class="text-muted small">Reset Harga Membership</span>)
                                            @endif
                                        </td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="right">
                                            @if (!$transaction->membership_price_reset_code)
                                                {{-- Tampilan untuk promo biasa --}}
                                                Rp{{ number_format($transaction->discount_promo, 0, ',', '.') }}
                                                @if ($transaction->promo_history)
                                                    <br>({{ $transaction->promo_history?->promo?->name }})
                                                @endif
                                            @else
                                                {{-- Tampilan untuk membership price reset --}}
                                                Rp{{ number_format($transaction->membership_price_reset_code->membership_price_period->price, 0, ',', '.') }}
                                                <br>
                                                {{ $transaction->membership_price_reset_code->price_reset_code }} <span class="text-muted small">(Periode: {{ $transaction->membership_price_reset_code->membership_price_period->period }})</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Total Bayar</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="right">Rp{{ number_format($transaction->pay_amount, 0, ',', '.') }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Bukti Pembayaran</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" align="right">
                                            @if ($transaction->payment_method->type === "AUTO")
                                                -
                                            @else
                                                @if ($transaction->payment_receipt)
                                                <a class="text-info btn p-0" onclick="showImage('{{ $transaction->id }}')">Lihat Bukti Pembayaran</a>
                                                @else
                                                <button style="background-color: transparent; border: none; padding: 0px;" title="Upload Bukti Pembayaran" data-bs-toggle="modal" data-bs-target="#kt_modal_upload_payment_receipt">
                                                    <i class="ki-duotone ki-file-up fs-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                </button>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if (!$transaction->transaction_details()->first()?->membership_shop_product_merchandise)
            <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                <div id="kt_content_container">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex gap-5 align-items-center">
                                <h1 class="text-capitalize fs-4 fw-500 mb-5">Review User</h1>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" rules="all">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                            <th class="text-center">#</th>
                                            <th class="text-center">Pembelian</th>
                                            <th class="text-center">Tanggal Review</th>
                                            <th class="text-center">Nilai Review</th>
                                            <th class="text-center">Review</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($transaction->transaction_details as $transaction_detail)
                                        <tr class="text-dark fw-semibold">
                                            <td class="text-center" width=5%>{{ $loop->iteration }}</td>
                                            <td class="text-center" width=20%>{{ $transaction_detail->parent?->name }}</td>
                                            <td class="text-center" width=20%>{{ $transaction_detail->parent?->review?->created_at == true ? date('d F Y H:i', strtotime($transaction_detail->parent?->review?->created_at)) : "-" }}</td>
                                            <td class="text-center" width=15%>{{ $transaction_detail->parent?->review?->star ?? "-" }}</td>
                                            <td class="text-center" width=40%>{{ $transaction_detail->parent?->review?->review ?? "-" }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="row">
                <div class="col-lg-6">
                    <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                        <div id="kt_content_container">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex gap-5 align-items-center">
                                        <h1 class="text-capitalize fs-4 fw-500 mb-5">Review User</h1>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" rules="all">
                                            <thead>
                                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                    <th class="text-center">#</th>
                                                    <th class="text-center">Pembelian</th>
                                                    <th class="text-center">Tanggal Review</th>
                                                    <th class="text-center">Nilai Review</th>
                                                    <th class="text-center">Review</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($transaction->transaction_details as $transaction_detail)
                                                <tr class="text-dark fw-semibold">
                                                    <td class="text-center" width=5%>{{ $loop->iteration }}</td>
                                                    <td class="text-center" width=20%>{{ $transaction_detail->parent?->name }}</td>
                                                    <td class="text-center" width=20%>{{ $transaction_detail->parent?->review?->created_at == true ? date('d F Y H:i', strtotime($transaction_detail->parent?->review?->created_at)) : "-" }}</td>
                                                    <td class="text-center" width=15%>{{ $transaction_detail->parent?->review?->star ?? "-" }}</td>
                                                    <td class="text-center" width=40%>{{ $transaction_detail->parent?->review?->review ?? "-" }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                        <div id="kt_content_container">
                            <div class="card">
                                @foreach ($transaction->transaction_details as $item)
                                    @if ($item->membership_shop_product_merchandise)
                                        @php
                                            $membership_shop_product_merchandise = $item->membership_shop_product_merchandise;
                                        @endphp

                                        <div class="card-body">
                                            <div class="d-flex gap-5 align-items-center">
                                                <h1 class="text-capitalize fs-4 fw-500 mb-5">Membership Merchandise</h1>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered" rules="none">
                                                    <tr height=40px>
                                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Pembelian Membership</td>
                                                        <td style="width: 1%">:</td>
                                                        <td class="text-label">{{ $item->parent->name }}</td>
                                                    </tr>
                                                    @if ($membership_shop_product_merchandise->is_extra_month_membership)
                                                        <tr height=40px>
                                                            <td style="width: 36%" class="fw-semibold text-label text-muted">Merchandise</td>
                                                            <td style="width: 1%">:</td>
                                                            <td class="text-label">Extra +{{ $membership_shop_product_merchandise->extra_month_membership }} Bulan Membership</td>
                                                        </tr>
                                                    @else
                                                        <tr height=40px>
                                                            <td style="width: 36%" class="fw-semibold text-label text-muted">Merchandise</td>
                                                            <td style="width: 1%">:</td>
                                                            <td class="text-label">
                                                                <ul>
                                                                    @foreach ($transaction->transaction_details->first()->parent->membership_shop_product as $membership_shop_product)
                                                                        <li>{{ $membership_shop_product->shop_product->name . " - " . $membership_shop_product->quantity . " pcs" }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    <tr height=40px>
                                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Status</td>
                                                        <td style="width: 1%">:</td>
                                                        <td class="text-label">
                                                            @if ($membership_shop_product_merchandise->is_taken)
                                                                <span class='badge text-white bg-info'>Merchandise Telah Diambil</span>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modal_merchandise_taken" onclick="merchandiseTaken('{{ $membership_shop_product_merchandise->id }}')">
                                                                    Ambil Merchandise
                                                                </button>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr height=40px>
                                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Merchandise Diambil</td>
                                                        <td style="width: 1%">:</td>
                                                        <td class="text-label">{{ $membership_shop_product_merchandise->taken_at ? date('d F Y H:i', strtotime($membership_shop_product_merchandise->taken_at)) : "-" }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
            <div id="kt_content_container">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex gap-5 align-items-center">
                            <h1 class="text-capitalize fs-4 fw-500 mb-5">Aktifitas Update Status Transaksi</h1>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0" rules="all">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th class="text-center">#</th>
                                        <th class="text-center">Waktu Aktifitas</th>
                                        <th class="text-center">Admin</th>
                                        <th class="text-center">Status Lama</th>
                                        <th class="text-center">Status Baru</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transaction->transaction_status_change_logs as $log)
                                    <tr class="text-dark fw-semibold">
                                        <td class="text-center" width=5%>{{ $loop->iteration }}</td>
                                        <td class="text-center" width=20%>{{ date('d F Y H:i', strtotime($log->created_at)) }}</td>
                                        <td class="text-center" width=20%>{{ $log->admin?->name }}</td>
                                        <td class="text-center" width=15%>{{ $log->old_status }}</td>
                                        <td class="text-center" width=40%>{{ $log->new_status }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('transaction.update_membership_shop_product_merchandise', $transaction->id) }}" method="POST" enctype="multipart/form-data" id="form_merchandise_taken">
    @csrf
    <div class="modal fade" tabindex="-1" id="modal_merchandise_taken">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Ambil Merchandise</h3>
                </div>
    
                <div class="modal-body">
                    <div class="row mb-6">
                        <!--begin::Label-->
                        <label for="taken_at" class="form-label fw-semibold fs-6">
                            <span class="required">Tanggal Merchandise Diambil</span>
                            <span class="ms-1" data-bs-toggle="tooltip" title="Masukkan Tanggal Merchandise Diambil">
                                <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <input type="hidden" name="membership_shop_product_merchandise_id" id="membership_shop_product_merchandise_id">
                        <input type="datetime-local" name="taken_at" class="form-control"
                            placeholder="Masukkan Tanggal Merchandise Diambil"
                            value="{{ @$user->taken_at ?? old('taken_at', date('d/m/Y H:i')) }}" required />
                        <!--end::Col-->
                    </div>
                </div>
    
                <div class="modal-footer">
                    <button class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</form>



<div class="modal fade" id="modal-payment-receipt-popup" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Bukti Pembayaran</h5>
        <button onclick="closeModal()" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body mx-auto">
        <img class="modal-content-payment-receipt" id="img01" alt="">
      </div>
    </div>
  </div>
</div>

{{-- form modal upload image --}}
<form action="{{ route('transaction.upload_payment_receipt', $transaction->id) }}" enctype="multipart/form-data" method="POST">
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

<div class="modal fade" id="modal_term_condition" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title me-2">Term & Condition</h5>
                <div class="d-flex align-items-center">
                    <div class="form-check form-switch form-check-custom form-check-solid me-5">
                        <input class="form-check-input" type="radio" name="language" value="id" id="id" checked/>
                        <label class="form-check-label" for="id">ID</label>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-solid me-5">
                        <input class="form-check-input" type="radio" name="language" value="en" id="en"/>
                        <label class="form-check-label" for="en">EN</label>
                    </div>
                    <div class="form-check form-switch form-check-custom form-check-solid">
                        <input class="form-check-input" type="radio" name="language" value="cn" id="cn"/>
                        <label class="form-check-label" for="cn">CN</label>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="term_id" class="term-content">
                    <?= $term_and_condition->content; ?>
                    <div style="margin-top: 2rem !important; text-align: right;">
                        <p>Disetujui pada: {{ date('d F Y H:i', strtotime($transaction->created_at)) }}</p>
                        <p>Oleh: {{ $transaction->user->name ?? $transaction->guest->name }}</p>
                    </div>
                </div>
                <div id="term_en" class="term-content" style="display: none;">
                    <?= $term_and_condition->content_en; ?>
                    <div style="margin-top: 2rem !important; text-align: right;">
                        <p>Approved on: {{ date('d F Y H:i', strtotime($transaction->created_at)) }}</p>
                        <p>By: {{ $transaction->user->name ?? $transaction->guest->name }}</p>
                    </div>
                </div>
                <div id="term_cn" class="term-content" style="display: none;">
                    <?= $term_and_condition->content_cn; ?>
                    <div style="margin-top: 2rem !important; text-align: right;">
                        <p>批准日期: {{ date('d F Y H:i', strtotime($transaction->created_at)) }}</p>
                        <p>批准人: {{ $transaction->user->name ?? $transaction->guest->name }}</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" onclick="printTermCondition()">Cetak</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
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

        function merchandiseTaken(id) {
            $('#membership_shop_product_merchandise_id').val(id);
            $("#modal_merchandise_taken").modal("show")
        }

        // Handle language switch
        $('input[name="language"]').change(function() {
            $('.term-content').hide();
            $('#term_' + $(this).val()).show();
        });

        // Print function
        function printTermCondition() {
            var printContents = $('.term-content:visible').html();
            var originalContents = document.body.innerHTML;
            
            document.body.innerHTML = `
                <div style="padding: 20px; color: #000000 !important;">
                    ${printContents}
                </div>
            `;
            
            window.print();
            document.body.innerHTML = originalContents;
            
            // Reinitialize any necessary scripts
            location.reload();
        }

        $(document).on('click', '.btn-update-status', function() {
            var button = $(this); // Store reference to 'this'
            var id = button.data('id');

            Swal.fire({
                title: 'Ubah Status Transaksi ',
                input: "select",
                inputOptions: {
                    PAID: "PAID",
                    CANCELLED: "CANCELLED",
                    EXPIRED: "EXPIRED",
                    PENDING: "PENDING"
                },
                inputClass: "form-control",
                inputPlaceholder: "Pilih Status Transaksi",
                inputAttributes: {
                    required: true
                },
                showCancelButton: true,
                confirmButtonText: 'Simpan Status',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-sm fw-semibold btn-primary',
                    cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
                },
                inputValidator: (value) => {
                    return new Promise((resolve, reject) => {
                        $.ajax({
                            url: "{{ route('transaction.update-status', ':id') }}".replace(':id', id),
                            type: 'PUT',
                            data: {status: value},
                            success: function(response) {
                                if (response.status === 'success') {
                                    location.reload(); // Reload the page
                                    Swal.fire({
                                        title: 'Berhasil!',
                                        text: response.message,
                                        icon: 'success',
                                        confirmButtonText: 'Tutup',
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Gagal!',
                                        text: response.message,
                                        icon: 'error',
                                        confirmButtonText: 'Tutup',
                                    });
                                }
                            },
                            error: function() {
                                Swal.fire({
                                    title: 'Error!',
                                    text: 'Terjadi kesalahan pada server.',
                                    icon: 'error',
                                    confirmButtonText: 'Tutup',
                                });
                            }
                        });
                    });
                }
            });
        });
</script>
@endpush