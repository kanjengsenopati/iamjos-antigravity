@extends('layouts.pt-master', ['title' => 'Dashboard'])
@push('css')
    <!--end::Fonts-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="{{ asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet" type="text/css" />

    <style>
        [data-bs-theme="light"] {
            --color-gray-10: #F1F1F2;
            --color-gray-20: #e1e1ef;
            --color-gray-6: #262626;
            --color-white: #262626;
        }

        [data-bs-theme="dark"] {
            --color-gray-10: #262626;
            --color-gray-20: #e1e1ef;
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

        .fc-h-event,
        .fc-event-main {
            border: none;
            cursor: pointer;
        }

        .fc-daygrid-event {
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

        .btn-all:hover {
            background: var(--color-gray-20);
        }

        .card.min {
            min-height: 21rem;
        }

        .bg-success {
            background-color: #3B61FF !important;
        }

        .bg-primary {
            background-color: #99CD15 !important;
        }

        .btn-active-primary {
            color: var(--color-white);
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

        .green {
            color: var(--salem-add-1500, #0ABF70);
        }

        .cursor-pointer,
        .fc-daygrid-day-events {
            cursor: pointer;
        }

        .text-grey {
            color: var(--color-gray-6);
        }

        .symbol-group .symbol {
            margin-left: -18px !important;
        }

        .number-member {
            position: absolute;
            right: 30%;
            top: 30%;
        }

        .last-member {
            filter: brightness(0.7);
        }

        .radius-xl {
            border-radius: 4rem;
        }

        .radius-l {
            border-radius: 1rem !important;
        }

        .bg-dark {
            background-color: #262626 !important;
        }

        .fc-event-time,
        .fc-event-title {
            color: white;
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
                        <h2 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Dashboard </h2>
                        <p><span class="text-primary">Dashboard</p>
                        <!--end::Title-->
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->

                    <!--end::Actions-->
                </div>
                <!--end::Container-->
            </div>
            <!--end::Toolbar-->
            <div class="row mt-4">
                @if (!$isPasswordSafe)
                    <div class="container-fluid">
                        <div class="alert alert-danger fade show mt-4 mb-2" role="alert">
                            <strong>Perhatian!</strong> Password Anda tidak memenuhi kriteria keamanan. Segera ganti
                            password Anda
                            <a href="{{ url('trainer/profile') }}" class="text-primary">Ganti
                                Password
                                Sekarang</a>.
                        </div>
                    </div>
                @endif
                <div class="col-md-6 mb-6 mb-lg-0">
                    <div class="card min">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h1 class="title">{{ $totalMemberActive }}</h1>
                                    <h6>Member Aktif</h6>
                                </div>
                                <a href="{{ route('personal-trainer.membership.index') }}">
                                    <button class="btn-all">Lihat semua</button>
                                </a>
                            </div>
                            <div class="symbol-group symbol-hover d-flex flex-wrap justify-content-start">
                                @foreach ($memberActives->slice(0, -1) as $index => $member)
                                    @if (file_exists($member->avatar))
                                        <div class="symbol symbol-45px symbol-md-50px symbol-circle"
                                            data-bs-toggle="tooltip" title="{{ $member->name }}">
                                            <img class="obj-fit-cover" alt="{{ $member->name }}"
                                                src="{{ asset($member->avatar) }}" />
                                        </div>
                                    @else
                                        <div class="symbol symbol-45px symbol-md-50px symbol-circle"
                                            data-bs-toggle="tooltip" title="{{ $member->name }}">
                                            <span
                                                class="symbol-label bg-warning text-inverse-warning fw-bold text-capitalize">{{ substr($member->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                @endforeach

                                @if ($memberActives->isNotEmpty())
                                    @php $lastMember = $memberActives->last(); @endphp
                                    @if (file_exists($lastMember->avatar))
                                        <a href="{{ route('personal-trainer.membership.index') }}"
                                            class="symbol symbol-45px symbol-md-50px symbol-circle last-member"
                                            data-bs-toggle="tooltip" title="{{ $lastMember->name }}">
                                            <img class="obj-fit-cover" alt="{{ $lastMember->name }}"
                                                src="{{ asset($lastMember->avatar) }}" />
                                            <span class="text-white fs-5 number-member">+{{ $totalMemberActive }}</span>
                                        </a>
                                    @else
                                        <a href="{{ route('personal-trainer.membership.index') }}"
                                            class="symbol symbol-45px symbol-md-50px symbol-circle last-member"
                                            data-bs-toggle="tooltip" title="{{ $lastMember->name }}">
                                            <span
                                                class="symbol-label bg-warning text-inverse-warning fw-bold text-capitalize"></span>
                                            <span class="text-white fs-5 number-member">+{{ $totalMemberActive }}</span>
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card min">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <h1 class="title">{{ $totalMemberActive + $totalMemberFinish }}</h1>
                                    <h6>Total Member</h6>
                                </div>
                                <a href="{{ route('personal-trainer.membership.index') }}">
                                    <button class="btn-all">Lihat semua</button>
                                </a>
                            </div>
                            <div class="pt-2 row d-flex align-items-center">
                                <div class="col-sm-5 px-0">
                                    <!--begin::Chart-->
                                    <div id="container"></div>
                                    <!--end::Chart-->
                                </div>
                                <div class="col-sm-6">
                                    <!--begin::Labels-->
                                    <div class="d-flex flex-column content-justify-center flex-row-fluid">
                                        <!--begin::Label-->
                                        <div class="d-flex fw-semibold align-items-center">
                                            <!--begin::Bullet-->
                                            <div class="bullet w-8px h-3px rounded-2 bg-success me-3"></div>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-500 flex-grow-1 me-4">Member aktif</div>
                                            <!--end::Label-->
                                            <!--begin::Stats-->
                                            <div class="fw-bolder text-gray-700 text-xxl-end">{{ $totalMemberActive }}
                                            </div>
                                            <!--end::Stats-->
                                        </div>
                                        <!--end::Label-->
                                        <!--begin::Label-->
                                        <div class="d-flex fw-semibold align-items-center my-3">
                                            <!--begin::Bullet-->
                                            <div class="bullet w-8px h-3px rounded-2 bg-primary me-3"></div>
                                            <!--end::Bullet-->
                                            <!--begin::Label-->
                                            <div class="text-gray-500 flex-grow-1 me-4">Member selesai</div>
                                            <!--end::Label-->
                                            <!--begin::Stats-->
                                            <div class="fw-bolder text-gray-700 text-xxl-end">{{ $totalMemberFinish }}
                                            </div>
                                            <!--end::Stats-->
                                        </div>
                                        <!--end::Label-->

                                    </div>
                                </div>
                                <!--end::Labels-->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- calendar date --}}
            @include('personal-trainers.components.schedule')
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.anychart.com/releases/8.12.0/js/anychart-base.min.js" type="text/javascript"></script>
    <script src="{{ asset('assets/js/widgets.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/custom/widgets.js') }}"></script>
    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    <script src="{{ asset('assets/js/custom/apps/chat/chat.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/create-app.js') }}"></script>
    <script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script>

    <script>
        $(document).ready(function() {
            const chartMember = () => {
                var data = anychart.data.set([{
                        x: "A",
                        value: @json($totalMemberActive)
                    },
                    {
                        x: "B",
                        value: @json($totalMemberFinish)
                    },
                ]);

                var chart = anychart.pie(data)
                chart.background(false)
                // Set width bound
                chart.width('100%');
                chart.innerRadius('70%');

                // Set height bound
                chart.height('110%');
                chart.legend(false)

                chart.labels().enabled(true);

                chart.tooltip().anchor("bottomLeft");

                var colors = ["#3B61FF", "#99CD15"];
                chart.palette(colors);

                chart.container('container');

                chart.draw();
            }

            chartMember()
        });
    </script>
@endpush
