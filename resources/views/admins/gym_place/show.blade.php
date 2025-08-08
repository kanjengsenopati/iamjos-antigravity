@extends('layouts.master', ['title' => 'Detail Tempat Gym', 'main' => 'Dashboard'])
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <x-alert.alert-validation />
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body">
                        <div class="d-flex gap-2 align-items-center mb-2">
                            <a href="{{ route('gym-place.index') }}" class="mt-1">
                                <span class="menu-icon back pt-1">
                                    <i class="ki-duotone ki-arrow-left">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </a>
                            @if(Auth::user()->is_show_all_gymplace)
                                <select class="form-select w-auto" style="min-width: 250px; max-width: 400px;" onchange="redirectGymPlace(this)">
                                    @foreach($allGymPlaces as $place)
                                        <option value="{{ $place->id }}" {{ $place->id == $gymPlace->id ? 'selected' : '' }}>
                                            {{ $place->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                @php
                                    // Ambil gym_place_id dari user jika ada, jika tidak fallback ke $gymPlace->id
                                    $defaultGymPlaceId = Auth::user()->gym_place_id ?? $gymPlace->id;
                                    $defaultGymPlaceName = '';
                                    foreach($allGymPlaces as $place) {
                                        if($place->id == $defaultGymPlaceId) {
                                            $defaultGymPlaceName = $place->name;
                                            break;
                                        }
                                    }
                                @endphp
                                <select class="form-select w-auto" style="min-width: 250px; max-width: 400px;" disabled>
                                    <option value="{{ $defaultGymPlaceId }}" selected>{{ $defaultGymPlaceName }}</option>
                                </select>
                            @endif
                            @can('gym-place')
                            <a class="d-flex align-items-center btn-edit"
                                href="{{ route('gym-place.edit', $gymPlace->id) }}">
                                <i class="ki-duotone ki-notepad-edit fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </a>
                            @endcan
                        </div>
                        <span class="text-sub-title">{{ $gymPlace->description }}</span>
                        <div class="hover-scroll-x mt-4">
                            <div class="d-grid">
                                <ul class="nav nav-tabs flex-nowrap text-nowrap">

                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_information" data-bs-toggle="tab" href="#tab_information">
                                            Informasi Tempat Gym
                                        </a>
                                    </li>
                                    @can('gym-place')
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_gym_place_operational" data-bs-toggle="tab"
                                            href="#tab_gym_place_operational">
                                            Jadwal Operasional
                                        </a>
                                    </li>
                                    <li class=" nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_membership" data-bs-toggle="tab" href="#tab_membership">
                                            Membership
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_gym_class" data-bs-toggle="tab" href="#tab_gym_class">
                                            Kelas
                                        </a>
                                    </li> --}}
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_gym_class_bundling" data-bs-toggle="tab"
                                            href="#tab_gym_class_bundling">
                                            {{-- Bundling --}}
                                            {{-- PT Plus --}}
                                            Coach Plus
                                        </a>
                                    </li>
                                    @endcan
                                    @canany(['gym-place', 'coach'])
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_personal_trainer" data-bs-toggle="tab"
                                            href="#tab_personal_trainer">
                                            {{-- Personal Trainer --}}
                                            Coach
                                        </a>
                                    </li>
                                    @endcan
                                    @can('gym-place')
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_personal_trainer_packet_session" data-bs-toggle="tab"
                                            href="#tab_personal_trainer_packet_session">
                                            {{-- Paket Personal Trainer --}}
                                            Paket Coach
                                        </a>
                                    </li>

                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_personal_trainer_packet_session_2" data-bs-toggle="tab"
                                            href="#tab_personal_trainer_packet_session_2">
                                            {{-- Paket Personal Trainer --}}
                                            Fisioterapi
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_locker" data-bs-toggle="tab" href="#tab_locker">
                                            Loker
                                        </a>
                                    </li> --}}
                                    {{-- <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_membership_history" data-bs-toggle="tab"
                                            href="#tab_membership_history">
                                            Riwayat Membership
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_gym_class_bundling_history" data-bs-toggle="tab"
                                            href="#tab_gym_class_bundling_history">
                                            Riwayat PT Plus
                                        </a>
                                    </li> --}}
                                    @endcan
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <div class="card card-flush mt-6">
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade" id="tab_information" role="tabpanel">
                                @include('admins.gym_place.tab.information')
                            </div>
                            @can('gym-place')
                            <div class="tab-pane fade" id="tab_gym_place_operational" role="tabpanel">
                                @include('admins.gym_place.tab.gym_place_operational')
                            </div>
                            <div class="tab-pane fade" id="tab_membership" role="tabpanel">
                                @include('admins.gym_place.tab.membership')
                            </div>
                            <div class="tab-pane fade" id="tab_gym_class" role="tabpanel">
                                @include('admins.gym_place.tab.gym_class')
                            </div>
                            <div class="tab-pane fade" id="tab_gym_class_bundling" role="tabpanel">
                                @include('admins.gym_place.tab.gym_class_bundling')
                            </div>
                            @endcan
                            @canany(['gym-place', 'coach'])
                            <div class="tab-pane fade" id="tab_personal_trainer" role="tabpanel">
                                @include('admins.gym_place.tab.personal_trainer')
                            </div>
                            @endcanany
                            @can('gym-place')
                            <div class="tab-pane fade" id="tab_personal_trainer_packet_session" role="tabpanel">
                                @include('admins.gym_place.tab.personal_trainer_packet_session')
                            </div>
                            <div class="tab-pane fade" id="tab_personal_trainer_external" role="tabpanel">
                                @include('admins.gym_place.tab.personal_trainer_external')
                            </div>
                            <div class="tab-pane fade" id="tab_personal_trainer_packet_session_2" role="tabpanel">
                                @include('admins.gym_place.tab.physiotherapy_session')
                            </div>
                            {{-- <div class="tab-pane fade" id="tab_locker" role="tabpanel">
                                @include('admins.gym_place.tab.locker')
                            </div> --}}
                            {{-- <div class="tab-pane fade" id="tab_membership_history" role="tabpanel">
                                @include('admins.gym_place.tab.membership_history')
                            </div>
                            <div class="tab-pane fade" id="tab_gym_class_bundling_history" role="tabpanel">
                                @include('admins.gym_place.tab.gym_class_bundling_history')
                            </div> --}}
                            @endcan
                        </div>
                    </div>
                    <!--end::Post-->
                </div>
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
    <!--end::Wrapper-->
    @include('admins.gym_place.tab.modal')
    @endsection
    @push('js')
    <script>
        function redirectGymPlace(select) {
            var selectedId = select.value;
            // Redirect langsung ke halaman detail gym place yang dipilih
            window.location.href = "{{ url('gym-place') }}/" + selectedId;
        }
    </script>
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