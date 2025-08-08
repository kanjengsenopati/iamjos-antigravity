@extends('layouts.master', ['title' => 'Detail Membership Annual/Lifetime','main' => 'List Membership Annual/Lifetime'])
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
</style>
@endpush
@section('content')
<div class="content d-flex flex-column flex-column-fluid" id="kt_content">
    <div id="kt_content_container" class="app-container container-xxl">
        <div class="row">
            <div class="col-sm-12">
                <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
                    <div id="kt_content_container">
                        <div class="card">
                            <div class="card-body">
                                <div class="mb-4 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                                    <div class="d-flex align-items-start gap-3 mb-0">
                                        <a href="{{ route('user-timeoff.index') }}">
                                            <span class="menu-icon back pt-1">
                                                <i class="ki-duotone ki-arrow-left">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                            </span>
                                        </a>
                                        <span class="card-label fw-bold fs-3 mb-1">Membership Annual/Lifetime Detail</span>
                                    </div>
                                </div>
                                <div class="table_header">
                                    @if ($annualPayment->transaction_id)
                                    <table class="table table-sm table-bordered" rules="none" type="button" onclick="window.location = '{{ route('transaction.show', $annualPayment->transaction?->id) }}'">
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Kode Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $annualPayment->transaction?->payment_code ?? '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Tanggal Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $annualPayment->transaction ? date('d F Y H:i', strtotime($annualPayment->transaction?->created_at)) : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Status Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @if ($annualPayment->transaction && $annualPayment->transaction?->status == "PAID")
                                                <span class='badge text-white bg-success'>LUNAS</span>
                                                @elseif ($annualPayment->transaction && $annualPayment->transaction?->status == "PENDING")
                                                <span class='badge text-white bg-warning'>PENDING</span>
                                                @elseif ($annualPayment->transaction && $annualPayment->transaction?->status == "CANCELED" || $annualPayment->transaction?->status == "EXPIRED")
                                                <span class='badge text-white bg-danger'>{{ $annualPayment->transaction?->status }}</span>
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    @else
                                    <div class="fv-row mb-6 d-flex justify-content-center">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                            Buat Pembayaran
                                        </button>
                                    </div>

                                    <!-- Modal -->
                                    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg"> <!-- Mengubah ukuran modal menjadi large -->
                                            <div class="modal-content">
                                                <form action="{{ route('annual-payment.process-payment', $annualPayment->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <input type="hidden" name="annual_payment_id" value="{{ $annualPayment->id }}">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="paymentModalLabel">Pembayaran Annual/Lifetime</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="fv-row mb-6">
                                                                    <label class="fs-6 fw-bold form-label" for="type">
                                                                        <span>Tipe Annual/Lifetime</span>
                                                                    </label>
                                                                    <input type="text" class="form-control" id="type" value="{{ $annualPayment->membership_history_id ? 'Membership' : 'Paket Coach Plus' }}" readonly>
                                                                </div>
                                                                <div class="fv-row mb-6">
                                                                    <label class="fs-6 fw-bold form-label" for="period">
                                                                        <span>Periode Annual/Lifetime</span>
                                                                    </label>
                                                                    <input type="text" class="form-control" id="period" value="{{ $annualPayment->period_lifetime }}" readonly>
                                                                </div>
                                                                <div class="fv-row mb-6">
                                                                    <label class="fs-6 fw-bold form-label" for="total_amount">
                                                                        <span class="required">Total Pembayaran</span>
                                                                    </label>
                                                                    <input type="number" class="form-control" id="total_amount" name="total_amount" value="{{ $annual_price }}" required>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="fv-row mb-6">
                                                                    <label class="fs-6 fw-bold form-label mt-3" for="payment_method">
                                                                        <span class="required">Pilih Metode Pembayaran</span>
                                                                    </label>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="QRIS" id="QRIS" name="payment_method" required>
                                                                        <label class="form-check-label fw-semibold fs-6" for="QRIS">QRIS</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="DEBIT BNI" id="DEBITBNI" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="DEBITBNI">DEBIT BNI</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="CREDIT BNI" id="CREDITBNI" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="CREDITBNI">CREDIT BNI</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="QRIS BNI" id="QRISBNI" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="QRISBNI">QRIS BNI</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="DEBIT OCBC" id="DEBITOCBC" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="DEBITOCBC">DEBIT OCBC</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="CREDIT OCBC" id="CREDITOCBC" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="CREDITOCBC">CREDIT OCBC</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="QRIS OCBC" id="QRISOCBC" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="QRISOCBC">QRIS OCBC</label>
                                                                    </div>
                                                                    <div class="form-check mb-2">
                                                                        <input class="form-check-input" type="radio" value="TRANSFER BCA" id="TRANSFERBCA" name="payment_method">
                                                                        <label class="form-check-label fw-semibold fs-6" for="TRANSFERBCA">TRANSFER BCA</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" type="button" onclick="window.location = '{{ route('user.show', $annualPayment->user?->id) }}'">{{ $annualPayment->user?->name }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tipe Annual/Lifetime</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $annualPayment->membership_history_id ? 'Membership' : 'Paket Coach Plus' }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal dibuat Annual Payment</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ date('d F Y H:i', strtotime($annualPayment->created_at)) }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Periode Annual/Lifetime</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $annualPayment->period_lifetime }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal di Non Aktif</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ date('d F Y', strtotime($annualPayment->date_off_at)) }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Status Cuti</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">
                                            @if ($annualPayment->status == "ACTIVE")
                                                <span class="badge badge-success">Aktif</span>
                                            @elseif ($annualPayment->status == "INACTIVE")
                                                <span class="badge badge-danger">Tidak Aktif</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
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
    </div>
</div>
@endsection