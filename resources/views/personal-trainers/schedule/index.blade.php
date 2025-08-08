@extends('layouts.pt-master', ['title' => 'Schedule'])
@push('css')
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />

<style>
    [data-bs-theme="light"] {
        --color-gray-10: #F1F1F2;
        --color-gray-6: #262626;
        --color-white: #262626;
    }

    [data-bs-theme="dark"] {
        --color-gray-10: #262626;
        --color-gray-6: #BFBFBF;
        --color-white: #FFFFFF;
    }

    a {
        text-decoration: none;
    }

    .tab {
        font-weight: 500;
        color: #8C8C8C;
    }

    .tab.active {
        border-bottom: 1.5px solid #B18D41;
        color: var(--color-white);
        padding-bottom: 0.6rem;
        font-weight: 600;
    }

    .btn-active-primary {
        color: var(--color-white);
    }

    .fc-h-event,
    .fc-event-main {
        border: none;
        cursor: pointer;
    }

    .btn-all {
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.375rem;
        /* 157.143% */
        letter-spacing: -0.00006rem;
        border-radius: var(--radius-xl, 1.25rem);
        background: var(--color-gray-10);
        outline: none;
        border: none;
        padding: 0.5rem 1rem
    }

    /* .card {
        min-height: 21rem;
    } */

    .bg-success {
        background-color: #3B61FF !important;
    }

    .bg-primary {
        background-color: #99CD15 !important;
    }

    h1.title {
        font-size: 1.9375rem !important;
    }

    .fc-button {
        text-transform: capitalize !important;
    }

    .fc .fc-button {
        padding: 0.75rem 1.25rem !important;
        box-shadow: none !important;
        border: 0 !important;
        border-radius: 0.475rem;
        vertical-align: middle;
        font-weight: 500;
        text-transform: capitalize;
    }

    .fc .fc-toolbar-title {
        font-size: 1.3rem !important;
    }

    .status {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.125rem;
        /* 150% */
        border-radius: var(--radius-m, 0.75rem);
        background: var(--color-gray-10);
        padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
        justify-content: center;
        align-items: center;
    }

    .violet {
        color: var(--fuchsia-add-2500, #C366CF);
    }

    .radius-xl {
        border-radius: 4rem;
    }

    .bg-dark {
        background-color: var(--color-gray-10) !important;
    }

    .fc-event-time,
    .fc-event-title {
        color: white;
    }

    .fc-event {
        cursor: pointer !important;
    }

    .fw-600 {
        font-weight: 600 !important;
    }

    .fw-500 {
        font-weight: 500 !important;
    }

    .bg-dark {
        background-color: var(--color-gray-10) !important;
        border: none !important;
        position: relative;
        overflow: hidden;
        cursor: pointer;
    }

    .bg-red {
        background-color: #D83C15;
    }

    .bg-blue {
        background-color: #1C7EFF;
    }

    .bg-gray,
    .bg-gray:hover {
        background: var(--color-gray-10);
    }

    .bg-orange {
        background-color: #E27900;
    }

    .bg-green {
        background-color: #74A00C;
    }


    .bg-red {
        background-color: #D83C15;
    }

    .bg-green100 {
        background-color: #EBFEF3;
    }

    .bg-purple100 {
        background-color: #FCF6FD;
    }

    .bg-dark.purple:before {
        content: '';
        position: absolute;
        background-color: #C366CF;
        width: 4px;
        height: 100%;
        bottom: 0;
        left: -1px;
        top: 0;
    }

    .border-radius-xxl {
        border-radius: 1.25rem !important;
    }

    .bg-dark.green:before {
        content: '';
        position: absolute;
        background-color: #0EC776;
        width: 4px;
        height: 100%;
        bottom: 0;
        left: -1px;
        top: 0;
    }

    .img_qr {
        width: 12rem;
    }

    .type {
        width: 1.5rem;
        height: 1.5rem;
    }

    .type img {
        width: 1rem;
        height: 1rem;
    }

    .border-radius-xxl {
        border-radius: 1.25rem !important;
    }

    .border-radius-xxxl {
        border-radius: 2rem !important;
    }

    .text-grey {
        color: #BFBFBF !important;
    }

    .text-grey2 {
        color: #8C8C8C !important;
    }

    .text-blue {
        color: #2896FF;
    }

    .text-green {
        color: #99CD15;
    }

    .text-orange {
        color: #FFA100;
    }

    .text-red {
        color: #D83C15;
    }

    .status {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.125rem;
        /* 150% */
        border-radius: var(--radius-m, 0.75rem);
        background: var(--color-gray-10);
        padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
        justify-content: center;
        align-items: center;
    }

    .violet {
        color: var(--fuchsia-add-2500, #C366CF);
    }

    .green {
        color: var(--salem-add-1500, #0ABF70);
    }
</style>
@endpush
@section('content')
<div class="content pt pt-5 d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="app-container container-xxl">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <!--begin::Container-->
            <div id="kt_toolbar_container" class="d-flex flex-stack">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                    data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                    class="page-title d-flex flex-column flex-wrap me-3 mb-5 mb-lg-0">
                    <!--begin::Title-->
                    <h2 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Jadwal </h2>
                    <p class="text-grey"><span class="text-primary">Home</span> - Jadwal</p>
                    <!--end::Title-->
                </div>
                <!--end::Page title-->
                <!--begin::Actions-->

                <!--end::Actions-->
            </div>
            <!--end::Container-->
        </div>
        <!--end::Toolbar-->
        @include('personal-trainers.components.schedule')
        
    </div>
</div>
<!--end::Container-->
@endsection
@push('js')
<script src="{{asset('assets/js/widgets.bundle.js')}}"></script>
<script src="{{asset('assets/js/custom/widgets.js')}}"></script>
<script src="{{asset('assets/js/custom/utilities/modals/upgrade-plan.js')}}"></script>
<script src="{{asset('assets/js/custom/utilities/modals/create-app.js')}}"></script>
<script src="{{asset('assets/js/custom/utilities/modals/users-search.js')}}"></script>
@endpush