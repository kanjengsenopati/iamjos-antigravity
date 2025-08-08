@extends('layouts.master', ['title' => 'Detail Coach', 'main' => 'Coach'])
@section('content')
    <div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <x-alert.alert-validation />
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body--> 
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <a href="{{ route('gym-place.show', $personalTrainer->gym_place_id) }}" class="mt-1">
                                <span class="menu-icon back pt-1">
                                    <i class="ki-duotone ki-arrow-left">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </a>
                            <h3 class="text-capitalize mb-0">{{ $personalTrainer->name }}</h3>
                            <a href="{{ route('personal-trainer.edit', $personalTrainer->id) }}">
                                <i class="ki-duotone ki-notepad-edit fs-3 mt-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>
                        </div>
                        <span class="text-sub-title">Menjadi PT Sejak {{ $personalTrainer->start_experience_year }}
                            ({{ $personalTrainer->experience_year }} Tahun)</span>
                        <div class="hover-scroll-x mt-5">
                            <div class="d-grid">
                                <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_information" data-bs-toggle="tab" href="#tab_information">
                                            Informasi Coach
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_personal_trainer_schedule" data-bs-toggle="tab"
                                            href="#tab_personal_trainer_schedule">
                                            Jadwal
                                        </a>
                                    </li> --}}
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_kt_tab_schedule" data-bs-toggle="tab"
                                            href="#kt_tab_schedule">
                                            Jadwal
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_personal_trainer_packet_session" data-bs-toggle="tab"
                                            href="#tab_personal_trainer_packet_session">
                                            Paket Sesi
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <div class="card mt-6">
                    <div class="card-body v2">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade active" id="tab_information" role="tabpanel">
                                @include('admins.personal_trainer.tab.information')
                            </div>
                            <div class="tab-pane fade" id="tab_personal_trainer_schedule" role="tabpanel">
                                @include('admins.personal_trainer.tab.personal_trainer_schedule')
                            </div>
                            <div class="tab-pane fade" id="kt_tab_schedule" role="tabpanel">
                                @include('admins.personal_trainer.tab.schedule')
                            </div>
                            <div class="tab-pane fade" id="tab_personal_trainer_packet_session" role="tabpanel">
                                @include('admins.personal_trainer.tab.personal_trainer_packet_session')
                            </div>
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Post-->
                </div>
            </div>
        </div>
        <!--end::Wrapper-->
    @endsection
    @push('js')
        @if (request()->tab)
            <script>
                $('#tab_{{ request()->tab }}').addClass('show active')
                $('#nav_tab_{{ request()->tab }}').addClass('active')
            </script>
        @else
            <script>
                $('#tab_information').addClass('show active')
                $('#nav_tab_information').addClass('active')
            </script>
        @endif
    @endpush
