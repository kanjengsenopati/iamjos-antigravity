@extends('layouts.master', ['title' => 'Detail Cuti Membership','main' => 'List Cuti Membership'])
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
                                        <span class="card-label fw-bold fs-3 mb-1">Cuti Membership Detail</span>
                                    </div>
                                </div>
                                <div class="table_header">
                                    <table class="table table-sm table-bordered" rules="none" type="button" onclick="window.location = '{{ $userTimeoff->transaction ? route('transaction.show', $userTimeoff->transaction?->id) : '#' }}'">
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Kode Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $userTimeoff->transaction?->payment_code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Tanggal Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">{{ $userTimeoff->transaction ?  date('d F Y H:i', strtotime($userTimeoff->transaction?->created_at)) : "" }}</td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" width=36%>Status Transaksi</td>
                                            <td class="border-bottom-0" width=1%>:</td>
                                            <td class="border-bottom-0">
                                                @if ($userTimeoff->transaction?->status == "PAID")
                                                <span class='badge text-white bg-success'>LUNAS</span>
                                                @elseif ($userTimeoff->transaction?->status == "PENDING")
                                                <span class='badge text-white bg-warning'>PENDING</span>
                                                @else
                                                <span class='badge text-white bg-danger'>{{ $userTimeoff->transaction?->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <table class="table table-sm table-bordered" rules="none">
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Nama User</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label" type="button" onclick="window.location = '{{ route('user.show', $userTimeoff->user?->id) }}'">{{ $userTimeoff->user?->name }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Pengajuan</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ date('d F Y H:i', strtotime($userTimeoff->created_at)) }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Periode Cuti</td>
                                        <td style="width: 1%">:</td>
                                        @php
                                            $month = round($userTimeoff->duration / 30);
                                        @endphp
                                        <td class="text-label">Cuti Membership {{ $month }} Bulan</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Mulai Cuti</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ date('d F Y', strtotime($userTimeoff->start_date)) }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Akhir Cuti</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ date('d F Y', strtotime($userTimeoff->end_date)) }}</td>
                                    </tr>
                                    @php
                                        $memberships = $userTimeoff->user->membership_histories()
                                            ->where(function ($query) use ($userTimeoff) {
                                                $query->where('start_active_date', '<=', $userTimeoff->start_date)
                                                    ->where('expiry_date', '>=', $userTimeoff->start_date);
                                            });
                                        $original_membership = $memberships->first()?->getAttributes();
                                    @endphp
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Expired</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $original_membership && $original_membership['expiry_date'] ? \Carbon\Carbon::parse($original_membership['expiry_date'])->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Expired Setelah Cuti</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">{{ $original_membership && $original_membership['expiry_date'] ? \Carbon\Carbon::parse($original_membership['expiry_date'])->addDays($userTimeoff->duration)->format('d-m-Y') : '-' }}</td>
                                    </tr>
                                    <tr height=40px>
                                        <td style="width: 36%" class="fw-semibold text-label text-muted">Status Cuti</td>
                                        <td style="width: 1%">:</td>
                                        <td class="text-label">
                                            @if ($userTimeoff->is_active)
                                                <span class="badge badge-success">Aktif</span>
                                            @else
                                                <span class="badge badge-danger">Tidak Aktif</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                                <div class="row">
                                    <div class="col-xl-6">
                                        <h4 class="mt-4">Daftar Membership</h4>
                                        <table class="table table-sm table-bordered mt-2" border="1">
                                            <thead>
                                                <tr>
                                                    <th>Nama Membership</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($memberships->get() as $membership_name)
                                                <tr>
                                                    <td>{{ $membership_name->member?->name }}</td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="col-xl-6">
                                        <h4 class="mt-4">Coach</h4>
                                        @php
                                            $personal_trainers = $userTimeoff->user->personal_trainer_packet_session_histories()
                                            ->where(function ($query) use ($userTimeoff) {
                                                $query->where('start_active_date', '<=', $userTimeoff->start_date)
                                                    ->where('expiry_date', '>=', $userTimeoff->start_date);
                                            })->get();
                                        @endphp
                                        <table class="table table-sm table-bordered mt-2" border="1">
                                            <thead>
                                                <tr>
                                                    <th>Coach</th>
                                                    <th>Remaining Session</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($personal_trainers as $personal_trainer)
                                                <tr>
                                                    <td>{{ $personal_trainer->personal_trainer?->name }}</td>
                                                    <td>{{ $personal_trainer->remaining_session }}</td>
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
            
        </div>
    </div>
</div>
@endsection