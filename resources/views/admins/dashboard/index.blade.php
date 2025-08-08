@extends('layouts.master', ['title' => 'Dashboard', 'main' => 'Dashboard'])
@push('css')
    <style>
        [data-bs-theme="light"] {
            --color-gray-9: #8C8C8C;
            --color-gray-10: #F1F1F2;
            --color-white: #262626;
            --color-black-2: #FFFFFF;
        }

        [data-bs-theme="dark"] {
            --color-gray-9: #434343;
            --color-gray-10: #262626;
            --color-white: #FFFFFF;
            --color-black-2: #1C1C1C;
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
            padding: 0.5rem 1rem;
            text-decoration: none;
            color: var(--color-white);
        }

        .text-white2 {
            color: var(--color-white) !important;
        }

        .page-item .page-link {
            border-radius: 50%;
            height: 2.625rem;
            min-width: 2.625rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .input-wrap {
            color: #8C8C8C;
            border-radius: 0.625rem !important;
            background: var(--color-gray-10);
            box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.12), 0px 0px 2px 0px rgba(0, 0, 0, 0.12);
        }

        .form-select,
        .form-control,
        .form-control:focus {
            color: #8C8C8C;
            border-radius: 0.625rem;
            background-color: var(--color-gray-10);
            border: none;

        }

        img.search {
            position: absolute;
            left: 1.2rem;
            top: 1rem;
        }

        .input-wrap input,
        .input-wrap input[type=text]:focus {
            border: none;
            outline: none;
            margin-left: 2.2rem;
            background-color: var(--color-gray-10);
            border-radius: 0.625rem;
            color: #8C8C8C;
            font-weight: 400;
        }

        .input-wrap input::placeholder {
            color: #8C8C8C;
        }

        .btn.type {
            border-radius: 50%;
            width: 2.5rem;
            height: 3.1rem;
            background: #262626;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .btn.type.active {
            background-color: #434343;
        }

        .text-grey {
            color: #BFBFBF !important;
        }

        .text-grey2 {
            color: #8C8C8C !important;
        }

        th,
        .fw-400 {
            font-weight: 400 !important;
        }

        .btn-status {
            font-size: 0.75rem;
            outline: none;
            border: none;
            border-radius: var(--radius-m, 0.75rem);
            padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
        }

        .bg-dark {
            background-color: var(--color-black-2) !important;
            border: none !important;
            position: relative;
            overflow: hidden;
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

        .type {
            width: 1.5rem;
            height: 1.5rem;
        }

        .type img {
            width: 1rem;
            height: 1rem;
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

        td {
            color: white !important;
            font-weight: 400 !important;
        }

        .border-radius-xxl {
            border-radius: 1.25rem !important;
        }

        .border-radius-xxxl {
            border-radius: 2rem !important;
        }

        .fw-600 {
            font-weight: 600 !important;
        }

        .fw-500 {
            font-weight: 500 !important;
        }

        .wrap-btn button {
            border: none;
            outline: none;
        }

        .border-dash {
            border-radius: var(--radius-s, 0.5rem);
            border: var(--spacing-00, 1px) dashed var(--color-gray-9, #434343);
        }

        hr {
            border-top: 1px solid #434343;
        }

        a {
            text-decoration: none;
        }

        .tab {
            font-weight: 500;
            color: #8C8C8C;
        }

        .tab.active {
            color: var(--color-white);
            border-bottom: 1px solid #B18D41;
            padding-bottom: 0.6rem;
            font-weight: 600;
        }

        .fs-big {
            font-size: 2.4375rem;
        }

        .wrap-button {
            border-radius: var(--radius-xl, 1.25rem);
            background: var(--color-gray-10);
            padding: var(--spacing-02, 0.25rem) var(--spacing-04, 0.75rem) var(--spacing-02, 0.25rem) var(--spacing-02, 0.25rem);
        }

        .btn-gray {
            font-weight: 500;
            text-decoration: none;
            font-size: 0.875rem;
            color: #8C8C8C;
            margin: 0.5rem 0;
            padding: var(--spacing-03, 0.5rem) var(--spacing-05, 1rem);
        }

        .btn-gray.active {
            color: white;
            border-radius: var(--radius-xl, 1.25rem);
            background: var(--color-gray-9, );
        }

        .modal-content {
            border-radius: 1rem !important;
            background-color: #1C1C1C !important;

        }

        .btn-active-primary {
            color: var(--color-white);
        }



        @media screen and (max-width: 768px) {
            .fs-big {
                font-size: 1.8rem;
            }
        }

        @media (min-width: 992px) {
            .wrap-card {
                height: 120vh;
                overflow: scroll;
            }
        }
    </style>
@endpush
@section('content')
    <div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    @if (!$isPasswordSafe)
                        <div class="container-fluid">
                            <div class="alert alert-danger fade show mt-4 mb-2" role="alert">
                                <strong>Perhatian!</strong> Password Anda tidak memenuhi kriteria keamanan. Segera ganti
                                password Anda
                                <a href="{{ route('profile-admin.edit') }}" class="text-primary">Ganti
                                    Password
                                    Sekarang</a>.
                            </div>
                        </div>
                    @endif
                    <!--end::Col-->
                </div>
                <!--end::Row-->
            </div>
            <!--end::Content container-->
        </div>
    </div>
    </div>
    <!--end::Container-->
@endsection
