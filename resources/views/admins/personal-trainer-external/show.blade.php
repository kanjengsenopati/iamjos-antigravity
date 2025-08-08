@extends('layouts.master', ['title' => 'Detail Coach External','main' => 'List Coach External'])
@push('css')
<style>
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
</style>
@endpush
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div id="kt_content_container" class="app-container container-xxl">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card body-->
            <div class="card-body">
                <div class="d-flex gap-5 align-items-center">
                    <h1 class="text-capitalize mb-0">{{$personalTrainerExternal->name}}</h1>
                    <a href="{{route('gym-class.edit', $personalTrainerExternal->id)}}">
                        <i class="ki-duotone ki-notepad-edit fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </a>
                </div>
                <hr class="mt-8 mb-3">
                <div class="row">
                    <div class="col-sm-4">
                        <label class="text-label text-muted">Avatar Image</label>
                        <p class="text-label">
                            <img src="{{ asset($personalTrainerExternal->avatar) }}" alt="" width="200px">
                        </p>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-label text-muted">Nama</label>
                        <p class="text-label">{{ $personalTrainerExternal->name }}</p>
                    </div>
                    <div class="col-sm-4">
                        <label class="text-label text-muted">Bio</label>
                        <p class="text-label" style="white-space: pre-line">{{ $personalTrainerExternal->bio }}</p>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>
    </div>
</div>
@endsection