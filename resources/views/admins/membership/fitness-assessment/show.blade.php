@extends('layouts.master', ['title' => 'Detail Membership Free Fitness Assessment', 'main' => 'Membership Free Fitness Assessment'])

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
        /* font-family: 'Gothic A1'; */
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
        font-size: 16px;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    .text-sub-title {
        color: #B5B5C3;
        /* font-family: 'Gothic A1'; */
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
        /* font-family: 'Gothic A1' !important; */
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
                            <!--begin::Card body--> 
                            <div class="card-body">
                                <div class="d-flex gap-2 align-items-center mb-2">
                                    <a href="{{ route('membership.free.fitness-assessment.index') }}" class="mt-1">
                                        <span class="menu-icon back pt-1">
                                            <i class="ki-duotone ki-arrow-left">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                            </i>
                                        </span>
                                    </a>
                                    <h1 class="text-capitalize mb-0">Detail Membership Free Fitness Assessment</h1>
                                </div>
                                <hr class="mt-8 mb-3">
                                <div class="p-3">
                                    <table class="table table-sm table-bordered" rules="none">
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Nama User</td>
                                            <td class="text-label" type="button" onclick="window.location = '{{ route('user.show', $membershipFitnessAssessment->user?->id) }}'">{{ $membershipFitnessAssessment->user?->name }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Tanggal Pembelian Membership</td>
                                            <td class="text-label">{{ date('d F Y H:i', strtotime($membershipFitnessAssessment->created_at)) }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Pembelian Membership</td>
                                            <td class="text-label">
                                                {{ $membershipFitnessAssessment->transactionDetail->parent->name }}
                                                <a href="{{ route('transaction.show', $membershipFitnessAssessment->transactionDetail->transaction->id) }}">Lihat Transaksi</a>
                                            </td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Bonus Sesi</td>
                                            <td class="text-label">{{ $membershipFitnessAssessment->session }} Sesi {{ $membershipFitnessAssessment->personalTrainerPacketSession->name }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Sesi digunakan</td>
                                            <td class="text-label">{{ $membershipFitnessAssessment->session - $membershipFitnessAssessment->personalTrainerPacketSessionHistory?->remaining_session }} Sesi</td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Personal Trainer</td>
                                            <td class="text-label">{{ $membershipFitnessAssessment->personalTrainer?->name ?? '-' }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Masa Berlaku</td>
                                            <td class="text-label">
                                                @if ($membershipFitnessAssessment->personalTrainerPacketSessionHistory)
                                                {{ $membershipFitnessAssessment->personalTrainerPacketSessionHistory?->start_active_date . " - " . $membershipFitnessAssessment->personalTrainerPacketSessionHistory?->expiry_date }}
                                                @else
                                                -
                                                @endif
                                            </td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Batas Klaim Fitness Assessment</td>
                                            <td class="text-label">{{ date('d F Y', strtotime($membershipFitnessAssessment->expired_at)) }}</td>
                                        </tr>
                                        <tr height=40px>
                                            <td style="width: 35%" class="fw-semibold text-label text-muted">Status</td>
                                            <td class="text-label">
                                                @if (! $membershipFitnessAssessment->is_used && $membershipFitnessAssessment->expired_at >= now())
                                                    <span class="badge badge-success">Belum digunakan oleh user</span>
                                                @elseif (! $membershipFitnessAssessment->is_used && $membershipFitnessAssessment->expired_at < now())
                                                    <span class="badge badge-danger">Expired</span>
                                                @elseif ($membershipFitnessAssessment->is_used && $membershipFitnessAssessment->remaining_session > 0)
                                                    <span class="badge badge-success">Aktif - Sisa : {{ $membershipFitnessAssessment->remaining_session . " Sesi" }}</span>
                                                @else
                                                    <span class="badge badge-warning">Non-Aktif - Sesi Habis Digunakan</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                             <!--end::Card body-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
</div>
@endsection
