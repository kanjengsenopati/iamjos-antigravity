@extends('layouts.master', ['title' => 'Report', 'main' => 'Dashboard'])

@push('css')
<style>
    .w-170px {

        width: 170px;
    }

    #myTab.nav-tabs .nav-link.active {
        color: var(--bs-primary) !important;
        font-weight: 500;
    }

    .modal-cs-lg {
        --bs-modal-width: 940px;
    }
</style>
@endpush
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-6">
                        <div
                            class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Report</span>
                            </h3>
                            <div class="d-flex flex-wrap gap-4 align-items-center">
                                @if(Auth::user()->is_show_all_gymplace)
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                        @foreach ($gym_places as $gym_place)
                                        <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" disabled>
                                        @php
                                        $userGymPlace = Auth::user()->gym_place;
                                        @endphp
                                        @if($userGymPlace)
                                        <option value="{{ $userGymPlace->id }}" selected>{{ $userGymPlace->name }}</option>
                                        @else
                                        <option value="">Tidak ada Gym Place</option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="gym_place_id" value="{{ $userGymPlace->id ?? '' }}">
                                </div>
                                @endif
                                <div>
                                    <label for="dateRange"
                                        class="btn btn-sm btn-light text-dark fw-600 d-flex align-items-center px-4">
                                        <input placeholder="Pick date rage"
                                            class="bg-transparent text-dark fw-600 cursor-pointer" id="dateRange" />
                                        <i class="ki-duotone ki-calendar fs-1 ms-0 me-0">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                            <span class="path4"></span>
                                            <span class="path5"></span>
                                            <span class="path6"></span>
                                        </i>
                                    </label>
                                    <input type="text" id="start_date" hidden>
                                    <input type="text" id="end_date" hidden>
                                </div>
                            </div>
                        </div>
                        <!--begin::Table-->
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark {{ !request()->tab ? 'active' : '' }}" id="internal-tab" data-bs-toggle="tab"
                                    data-bs-target="#internal-tab-pane" type="button" role="tab"
                                    aria-controls="internal-tab-pane" aria-selected="false">Coach Internal</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark {{ request()->tab == "physiotherapy" ? 'active' : '' }}" id="physiotherapy-tab" data-bs-toggle="tab"
                                    data-bs-target="#physiotherapy-tab-pane" type="button" role="tab"
                                    aria-controls="physiotherapy-tab-pane" aria-selected="false">Fisioterapi</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark {{ request()->tab == "external" ? 'active' : '' }}" id="external-tab" data-bs-toggle="tab"
                                    data-bs-target="#external-tab-pane" type="button" role="tab"
                                    aria-controls="external-tab-pane" aria-selected="true">Coach External</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark {{ request()->tab == "help" ? 'active' : '' }}" id="help-tab" data-bs-toggle="tab"
                                    data-bs-target="#help-tab-pane" type="button" role="tab"
                                    aria-controls="help-tab-pane" aria-selected="true">Ready To Help</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark" id="selling-tab" data-bs-toggle="tab"
                                    data-bs-target="#selling-tab-pane" type="button" role="tab"
                                    aria-controls="selling-tab-pane" aria-selected="true">Selling Coach</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark" id="conduct-tab" data-bs-toggle="tab"
                                    data-bs-target="#conduct-tab-pane" type="button" role="tab"
                                    aria-controls="conduct-tab-pane" aria-selected="true">Conduct Coach</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark" id="commission-internal-tab" data-bs-toggle="tab"
                                    data-bs-target="#commission-internal-tab-pane" type="button" role="tab"
                                    aria-controls="commission-internal-tab-pane" aria-selected="true">Commission Coach
                                    Internal</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link text-dark" id="commission-external-tab" data-bs-toggle="tab"
                                    data-bs-target="#commission-external-tab-pane" type="button" role="tab"
                                    aria-controls="commission-external-tab-pane" aria-selected="true">Commission Coach
                                    External</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade {{ !request()->tab ? ' show active' : '' }}" id="internal-tab-pane" role="tabpanel"
                                aria-labelledby="internal-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.export') }}" method="GET"
                                            enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="i_filter_start_date" name="start_date" hidden>
                                            <input type="text" id="i_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="i_filter_gym_place_id" name="gym_place_id" hidden>
                                            <input type="text" name="type" value="internal" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_personal_trainer"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Avatar</th>
                                                <th width=20%>Nama Coach</th>
                                                <th width=10%>level</th>
                                                <th width=20%>Training</th>
                                                <th width=20%>Kelas</th>
                                                <th width=5%>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{ request()->tab == "physiotherapy" ? ' show active' : '' }}" id="physiotherapy-tab-pane" role="tabpanel"
                                aria-labelledby="physiotherapy-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.export') }}" method="GET"
                                            enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="p_filter_start_date" name="start_date" hidden>
                                            <input type="text" id="p_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="p_filter_gym_place_id" name="gym_place_id" hidden>
                                            <input type="text" name="type" value="physiotherapy" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_physiotherapy"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Avatar</th>
                                                <th width=20%>Nama Fisio</th>
                                                <th width=20%>Total Sesi</th>
                                                <th width=5%>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade {{ request()->tab == "external" ? ' show active' : '' }}" id="external-tab-pane" role="tabpanel"
                                aria-labelledby="external-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.export') }}" method="GET"
                                            enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="e_filter_start_date" name="start_date" hidden>
                                            <input type="text" id="e_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="e_filter_gym_place_id" name="gym_place_id" hidden>
                                            <input type="text" name="type" value="external" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                        {{-- <a href="{{ route('personal-trainer.recap.export') }}"
                                            class="btn btn-primary btn-sm text-nowrap"> --}}

                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_personal_trainer_external"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Avatar</th>
                                                <th width=25%>Nama Coach</th>
                                                <th width=20%>Training</th>
                                                <th width=25%>Kelas</th>
                                                <th width=5%>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="help-tab-pane" role="tabpanel"
                                aria-labelledby="help-tab" tabindex="0">
                                <div>
                                    <table id="datatable_personal_trainer_help"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Avatar</th>
                                                <th width=25%>Nama Coach</th>
                                                <th width=20%>Total Help</th>
                                                <th width=5%>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="selling-tab-pane" role="tabpanel"
                                aria-labelledby="selling-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.coach-selling.export') }}"
                                            method="GET" enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="selling_filter_start_date" name="start_date" hidden>
                                            <input type="text" id="selling_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="selling_filter_gym_place_id" name="gym_place_id"
                                                hidden>
                                            <input type="text" name="type" value="selling" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                        {{-- <a href="{{ route('personal-trainer.recap.export') }}"
                                            class="btn btn-primary btn-sm text-nowrap"> --}}

                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_personal_trainer_selling"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Contract Number</th>
                                                <th width=25%>Service Type</th>
                                                <th width=20%>Template Type</th>
                                                <th width=25%>Member Number</th>
                                                <th width=25%>Club ID</th>
                                                <th width=25%>Home Club</th>
                                                <th width=25%>Customer Name</th>
                                                <th width=25%>Total Sessions</th>
                                                <th width=25%>Contract Date</th>
                                                <th width=25%>Expiration Date</th>
                                                <th width=25%>Paid Today</th>
                                                <th width=25%>Dept</th>
                                                <th width=25%>Status Member Coach</th>
                                                <th width=25%>Gender</th>
                                                <th width=25%>Coach Name</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="conduct-tab-pane" role="tabpanel"
                                aria-labelledby="conduct-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.coach-conduct.export') }}"
                                            method="GET" enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="conduct_filter_start_date" name="start_date" hidden>
                                            <input type="text" id="conduct_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="conduct_filter_gym_place_id" name="gym_place_id"
                                                hidden>
                                            <input type="text" name="type" value="conduct" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                        <form
                                            action="{{ route('personal-trainer.recap.coach-conduct-grouped.export') }}"
                                            method="GET" enctype="multipart/form-data">
                                            @method('GET')
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Coach Conduct Grouped Excel
                                            </button>
                                        </form>
                                        {{-- <a href="{{ route('personal-trainer.recap.export') }}"
                                            class="btn btn-primary btn-sm text-nowrap"> --}}

                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_personal_trainer_conduct"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Contract Number</th>
                                                <th width=10%>Service Type</th>
                                                <th width=10%>Visit Date</th>
                                                <th width=10%>Membership ID</th>
                                                <th width=10%>Member Name</th>
                                                <th width=10%>Transaction Date</th>
                                                <th width=10%>Item Description</th>
                                                <th width=10%>Coach Name</th>
                                                <th width=10%>Role</th>
                                                <th width=10%>Paid Today</th>
                                                <th width=10%>Session Buy</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="commission-internal-tab-pane" role="tabpanel"
                                aria-labelledby="commission-internal-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.commission-coach.export') }}"
                                            method="GET" enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="commission_filter_start_date" name="start_date"
                                                hidden>
                                            <input type="text" id="commission_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="commission_filter_gym_place_id" name="gym_place_id"
                                                hidden>
                                            <input type="text" name="type" value="internal" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                        {{-- <a href="{{ route('personal-trainer.recap.export') }}"
                                            class="btn btn-primary btn-sm text-nowrap"> --}}

                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_personal_trainer_commission_internal"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Avatar</th>
                                                <th width=20%>Nama Coach</th>
                                                <th width=10%>level</th>
                                                <th width=20%>Training</th>
                                                <th width=20%>Kelas</th>
                                                <th width=20%>Fitness Assessment</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="commission-external-tab-pane" role="tabpanel"
                                aria-labelledby="commission-external-tab" tabindex="0">
                                <div
                                    class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                    <div>
                                        <h4>&nbsp;</h4>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <form action="{{ route('personal-trainer.recap.commission-coach.export') }}"
                                            method="GET" enctype="multipart/form-data">
                                            @method('GET')
                                            <input type="text" id="commission_e_filter_start_date" name="start_date"
                                                hidden>
                                            <input type="text" id="commission_e_filter_end_date" name="end_date" hidden>
                                            <input type="text" id="commission_e_filter_gym_place_id" name="gym_place_id"
                                                hidden>
                                            <input type="text" name="type" value="external" hidden>
                                            <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                                <i class="ki-duotone ki-exit-up fs-3">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                </i>
                                                Export Excel
                                            </button>
                                        </form>
                                        {{-- <a href="{{ route('personal-trainer.recap.export') }}"
                                            class="btn btn-primary btn-sm text-nowrap"> --}}

                                    </div>
                                </div>
                                <div>
                                    <table id="datatable_personal_trainer_commission_external"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th width=5%>No</th>
                                                <th width=10%>Avatar</th>
                                                <th width=20%>Nama Coach</th>
                                                <th width=20%>Training</th>
                                                <th width=20%>Kelas</th>
                                                <th width=20%>Fitness Assessment</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <!--end::Table-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->

</div>

<div class="modal fade" tabindex="-1" id="modal-personal-trainer-internal">
    <div class="modal-dialog mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="personal_trainer_name">Coach ....</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <h4 class="mb-6" id="period"></h4>
                <div class="row">
                    <div class="col-6">
                        <h3>Total Sesi</h3>
                        <div class="mb-2">
                            <label class="text-muted">Total Sesi</label>
                            <p class="text-label" id="total-session"></p>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted">Paket Sesi</label>
                            <p class="text-label" id="package-data"></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <h3>Total Kelas</h3>
                        <div class="mb-2">
                            <label class="text-muted">Total Kelas</label>
                            <p class="text-label" id="total-class"></p>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted">Paket Kelas</label>
                            <p class="text-label" id="class-data"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="modal-physiotherapy">
    <div class="modal-dialog mw-900px">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="physiotherapy_name">Fisio ....</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <h4 class="mb-6" id="period"></h4>
                <div class="row">
                    <div class="col-6">
                        <h3>Total Sesi</h3>
                        <div class="mb-2">
                            <label class="text-muted">Total Sesi</label>
                            <p class="text-label" id="physiotherapy-total-session"></p>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted">Paket Sesi</label>
                            <p class="text-label" id="physiotherapy-package-data"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" tabindex="-1" id="modal-personal-trainer-external">
    <div class="modal-dialog modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="ex_personal_trainer_name">Coach ....</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <h4 class="mb-6" id="ex-period"></h4>
                <div class="row">
                    <div class="col-6">
                        <h3>Total Sesi</h3>
                        <div class="mb-2">
                            <label class="text-muted">Total Sesi</label>
                            <p class="text-label" id="ex-total-session"></p>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted">Paket Sesi</label>
                            <p class="text-label" id="ex-package-data"></p>
                        </div>
                    </div>
                    <div class="col-6">
                        <h3>Total Kelas</h3>
                        <div class="mb-2">
                            <label class="text-muted">Total Kelas</label>
                            <p class="text-label" id="ex-total-class"></p>
                        </div>
                        <div class="mb-2">
                            <label class="text-muted">Paket Kelas</label>
                            <p class="text-label" id="ex-class-data"></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" id="modal-personal-trainer-help">
    <div class="modal-dialog modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="help_personal_trainer_name">Coach ....</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                    aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <h4 class="mb-6" id="help-period"></h4>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama User</th>
                                <th>Waktu</th> 
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="help-data">
                            <!-- Data akan diisi via axios -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
    $(function() {
        var start = moment().startOf('month');
        var end = moment().endOf('month');

        function cb(start, end) {
            $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
            var start = start.format('YYYY-MM-DD');
            var end = end.format('YYYY-MM-DD');
            $('#start_date').val(start);
            $('#end_date').val(end);

            if($('#internal-tab').hasClass('active')) {
                table();
            }

            if ($('#physiotherapy-tab').hasClass('active')) {
                table_physiotherapy();
            }

            if($('#external-tab').hasClass('active')) {
                table_external();
            }

            if($('#help-tab').hasClass('active')) {
                table_ready_to_help();
            }

            if($('#selling-tab').hasClass('active')) {
                table_selling();
            }
            
            if($('#conduct-tab').hasClass('active')) {
                table_conduct();
            }
            
            if($('#commission-internal-tab').hasClass('active')) {
                table_commission_internal();
            }

            if($('#commission-external-tab').hasClass('active')) {
                table_commission_external();
            }
        }

        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Semua Waktu': [moment().subtract(5, 'years'), moment()],
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            }
        }, cb);
        cb(start, end);
    });
</script>
@endpush

@push('js')
<script>
    // $(document).ready(function() {
    //     table();
    // });
    
    $('#internal-tab').on('click', function() {
        table();
    });

    $('#physiotherapy-tab').on('click', function() {
        table_physiotherapy();
    });

    $('#external-tab').on('click', function() {
        table_external();
    });

    $('#help-tab').on('click', function() {
        table_ready_to_help();
    });

    $('#selling-tab').on('click', function() {
        table_selling();
    });
    
    $('#conduct-tab').on('click', function() {
        table_conduct();
    });

    $('#commission-internal-tab').on('click', function() {
        table_commission_internal();
    });

    $('#commission-external-tab').on('click', function() {
        table_commission_external();
    });

    function package_session(id) {
        $.ajax({
            url: "{{ url('personal-trainer-recap/:id?type=internal&startDate=startDates&endDate=endDates') }}".replace(':id', id).replace('startDates', $("#start_date").val()).replace('endDates', $("#end_date").val()).replace(/&amp;/g, "&"),
            method: 'get',
            dataType: 'json',
            success: function(data) {
                // console.log(data.date)
                $('#modal-personal-trainer-internal').modal('show');
                $("#personal_trainer_name").text('Coach '+data.personal_trainer)
                $("#period").text('Periode '+data.date)
                $("#total-session").text(data.sesi + " Sesi")
                $("#package-data").html(data.data_package)
                $("#total-class").text(data.class + " Kelas")
                $("#class-data").html(data.data_classes)
            }
        });
    }

    function package_session_physiotherapy(id) {
        $.ajax({
            url: "{{ url('personal-trainer-recap/:id?type=physiotherapy&startDate=startDates&endDate=endDates') }}".replace(':id',
            id).replace('startDates', $("#start_date").val()).replace('endDates', $("#end_date").val()).replace(/&amp;/g, "&"),
            method: 'get',
            dataType: 'json',
        success: function(data) {
            // console.log(data.date)
            $('#modal-physiotherapy').modal('show');
            $("#physiotherapy_name").text('Fisio '+data.physiotherapy)
            $("#physiotherapy-period").text('Periode '+data.date)
            $("#physiotherapy-total-session").text(data.sesi + " Sesi")
            $("#physiotherapy-package-data").html(data.data_package)
        }
        });
    }

    function package_session_external(id) {
        $.ajax({
            url: "{{ url('personal-trainer-recap/:id?type=external&startDate=startDates&endDate=endDates') }}".replace(':id', id).replace('startDates', $("#start_date").val()).replace('endDates', $("#end_date").val()).replace(/&amp;/g, "&"),
            method: 'get',
            dataType: 'json',
            success: function(data) {
                // console.log(data.date)
                $('#modal-personal-trainer-external').modal('show');
                $("#ex_personal_trainer_name").text('Coach '+data.personal_trainer)
                $("#ex-period").text('Periode '+data.date)
                $("#ex-total-session").text(data.sesi)
                $("#ex-package-data").html(data.data_package)
                $("#ex-total-class").text(data.class + " Kelas")
                $("#ex-class-data").html(data.data_classes)
            }
        });
    }

    function package_session_help(trainerId) {
        $('#modal-personal-trainer-help').modal('show');
        
        axios.get("{{ route('personal-trainer.recap.ready-to-help') }}", {
            params: {
                data: 'ready_to_help_detail',
                trainer_id: trainerId,
                start_date: $("#start_date").val(),
                end_date: $("#end_date").val()
            }
        })
        .then(function(response) {
            const data = response.data;
            const helpData = data.data;
            
            // Set judul modal
            $('#help_personal_trainer_name').text('Coach ' + data.coach_name);
            $('#help-period').text('Periode: ' + moment($("#start_date").val()).format('D MMM YYYY') + ' - ' + moment($("#end_date").val()).format('D MMM YYYY'));
            
            // Isi tabel
            let html = '';
            helpData.forEach((item, index) => {
                html += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${item.user_name || '-'}</td>
                        <td>${moment(item.created_at).format('D MMM YYYY HH:mm')}</td>
                        <td>${item.status || '-'}</td>
                    </tr>
                `;
            });
            
            $('#help-data').html(html);
        })
        .catch(function(error) {
            console.error(error);
        });
    }
    
    function table() {
        var table = $('#datatable_personal_trainer').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index', 'data=internal') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer tbody').empty(); 
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'avatar', name: 'avatar', responsivePriority: -1},
                { data: 'name', name: 'name', responsivePriority: -2},
                { data: 'personal_trainer_level.name', name: 'personal_trainer_level.name' },
                { data: 'total_session', name: 'total_session'},
                { data: 'total_class', name: 'total_class'},
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });
        document.getElementById('i_filter_start_date').value = $("#start_date").val();
        document.getElementById('i_filter_end_date').value = $("#end_date").val();
        document.getElementById('i_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_personal_trainer tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable_personal_trainer').fadeIn();
        });
    }

    function table_physiotherapy() {
        var tablePhysiotherapy = $('#datatable_physiotherapy').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index', 'data=physiotherapy') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_physiotherapy tbody').empty();
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [{
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'avatar', name: 'avatar', responsivePriority: -1},
                { data: 'name', name: 'name', responsivePriority: -2},
                { data: 'total_session', name: 'total_session'},
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });
        document.getElementById('p_filter_start_date').value = $("#start_date").val();
        document.getElementById('p_filter_end_date').value = $("#end_date").val();
        document.getElementById('p_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        tablePhysiotherapy.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_physiotherapy tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        tablePhysiotherapy.on('draw.dt', function() {
            $('#datatable_physiotherapy').fadeIn();
        });
        
    }
    

    function table_external() {
        var tableEx = $('#datatable_personal_trainer_external').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index', 'data=external') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer_external tbody').empty(); 
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                        },
                        "loadingRecords": "Loading...",
                        "processing": "Processing...",
                    },
                    columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'avatar', name: 'avatar',  responsivePriority: -1},
                    { data: 'name', name: 'name', responsivePriority: -2},
                    { data: 'total_session', name: 'total_session'},
                    { data: 'total_class', name: 'total_class'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true,
                        responsivePriority: -1,
                    },
                ]
            }
        );
        document.getElementById('e_filter_start_date').value = $("#start_date").val();
        document.getElementById('e_filter_end_date').value = $("#end_date").val();
        document.getElementById('e_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        tableEx.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_personal_trainer_external tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        tableEx.on('draw.dt', function() {
            $('#datatable_personal_trainer_external').fadeIn();
        });
    }

    function table_ready_to_help() {
        var tableHelp = $('#datatable_personal_trainer_help').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index', 'data=ready_to_help') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer_help tbody').empty(); 
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                        "previous": "<i class='fa fa-angle-left'>"
                        },
                        "loadingRecords": "Loading...",
                        "processing": "Processing...",
                    },
                    columns: [{
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    { data: 'avatar', name: 'avatar',  responsivePriority: -1},
                    { data: 'name', name: 'name', responsivePriority: -2},
                    { data: 'total_help', name: 'total_help'},
                    {
                        data: 'action',
                        name: 'action',
                        orderable: true,
                        searchable: true,
                        responsivePriority: -1,
                    },
                ]
            }
        );
        document.getElementById('e_filter_start_date').value = $("#start_date").val();
        document.getElementById('e_filter_end_date').value = $("#end_date").val();
        document.getElementById('e_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        tableHelp.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_personal_trainer_help tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        tableHelp.on('draw.dt', function() {
            $('#datatable_personal_trainer_help').fadeIn();
        });
    }

    function table_selling() {
        var tableSelling = $('#datatable_personal_trainer_selling').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index') }}",
                type: 'GET',
                data: {
                    type: 'selling',
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val(),
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer_selling tbody').empty();
                }
            },
            language: {
                "paginate": {
                "next": "<i class='fa fa-angle-right'>",
                "previous": "<i class='fa fa-angle-left'>"
            },
            "loadingRecords": "Loading...",
            "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'contract_number', name : 'contract_number', responsivePriority: -1},
                { data: 'service_type', name: 'service_type', responsivePriority: -2},
                { data: 'template_type', name: 'template_type'},
                { data: 'membership_id', name: 'membership_id'},
                { data: 'club_id', name: 'club_id'},
                { data: 'home_club', name: 'home_club'},
                { data: 'customer_name', name: 'customer_name'},
                { data: 'total_session', name: 'total_session'},
                { data: 'start_date', name: 'start_date'},
                { data: 'end_date', name: 'end_date'},
                { data: 'pay_amount', name: 'pay_amount',
                    render: $.fn.dataTable.render.number(',', '.', 0, 'Rp. ')},
                { data: 'dept', name: 'dept'},
                { data: 'status_member_coach', name: 'status_member_coach'},
                {
                    data: 'gender',
                    name: 'gender'
                },
                {
                    data: 'coach_name',
                    name: 'coach_name'
                },
            ]
        });
        document.getElementById('selling_filter_start_date').value = $("#start_date").val();
        document.getElementById('selling_filter_end_date').value = $("#end_date").val();
        document.getElementById('selling_filter_gym_place_id').value = $("#gym_place_id").val();
    
        // Menyembunyikan tabel selama proses loading
        tableSelling.on('preXhr.dt', function(e, settings, data) {
        $('#datatable_personal_trainer_selling tbody').empty();
        });
    
        // Menampilkan tabel setelah data selesai dimuat
        tableSelling.on('draw.dt', function() {
            $('#datatable_personal_trainer_selling').fadeIn();
        });
    }

    function table_conduct() {
        var tableSelling = $('#datatable_personal_trainer_conduct').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index') }}",
                type: 'GET',
                data: {
                    type: 'conduct',
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val(),
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer_conduct tbody').empty();
                }
            },
            language: {
            "paginate": {
                "next": "<i class='fa fa-angle-right'>",
                "previous": "<i class='fa fa-angle-left'>"
            },
            "loadingRecords": "Loading...",
            "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'contract_number', name : 'contract_number', responsivePriority: -1},
                { data: 'service_type', name: 'service_type', responsivePriority: -2},
                {
                    data: 'membership_id',
                    name: 'membership_id'
                },
                {
                    data: 'visit_date',
                    name: 'visit_date'
                },
                {
                    data: 'user.name',
                    name: 'user.name'
                },
                {
                    data: 'transaction_date',
                    name: 'transaction_date'
                },
                {
                    data: 'personal_trainer_packet_session_history.personal_trainer_packet_session.name',
                    name: 'personal_trainer_packet_session_history.personal_trainer_packet_session.name'
                },
                {
                    data: 'personal_trainer_packet_session_history.personal_trainer.name',
                },
                {
                    data: 'role',
                    name: 'role'
                },
                {
                    data: 'paid_today',
                    name: 'paid_today',
                },
                {
                    data: 'total_session',
                    name: 'total_session',
                }
            ]
        });
        document.getElementById('conduct_filter_start_date').value = $("#start_date").val();
        document.getElementById('conduct_filter_end_date').value = $("#end_date").val();
        document.getElementById('conduct_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        tableSelling.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_personal_trainer_conduct tbody').empty();
        });
        
        // Menampilkan tabel setelah data selesai dimuat
        tableSelling.on('draw.dt', function() {
            $('#datatable_personal_trainer_conduct').fadeIn();
        });
    }

    function table_commission_internal() {
        var table = $('#datatable_personal_trainer_commission_internal').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index', 'data=commission_internal') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer_commission_internal tbody').empty(); 
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'avatar', name: 'avatar', responsivePriority: -1},
                { data: 'name', name: 'name', responsivePriority: -2},
                { data: 'personal_trainer_level.name', name: 'personal_trainer_level.name' },
                { data: 'total_session', name: 'total_session'},
                { data: 'total_class', name: 'total_class'},
                { data: 'total_fitness_assessment', name: 'total_fitness_assessment'},
            ]
        });
        document.getElementById('commission_filter_start_date').value = $("#start_date").val();
        document.getElementById('commission_filter_end_date').value = $("#end_date").val();
        document.getElementById('commission_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_personal_trainer_commission_internal tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable_personal_trainer_commission_internal').fadeIn();
        });
    }
    
    function table_commission_external() {
        var table = $('#datatable_personal_trainer_commission_external').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('personal-trainer.recap.index', 'data=commission_external') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_personal_trainer_commission_external tbody').empty(); 
                }
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            columns: [
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'avatar', name: 'avatar', responsivePriority: -1},
                { data: 'name', name: 'name', responsivePriority: -2},
                { data: 'total_session', name: 'total_session'},
                { data: 'total_class', name: 'total_class'},
                { data: 'total_fitness_assessment', name: 'total_fitness_assessment'},
            ]
        });
        document.getElementById('commission_e_filter_start_date').value = $("#start_date").val();
        document.getElementById('commission_e_filter_end_date').value = $("#end_date").val();
        document.getElementById('commission_e_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_personal_trainer_commission_external tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable_personal_trainer_commission_external').fadeIn();
        });
    }
    
</script>
@endpush