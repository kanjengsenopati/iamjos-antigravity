@extends('layouts.master', ['title' => 'Scan Qrcode', 'main' => 'Dashboard'])
@push('css')
    <style>
        .modal-cs-lg {
            --bs-modal-width: 940px;
        }
    </style>
@endpush
@section('content')
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid pt-6">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card card-flush">
                <!--begin::Card header-->
                <div class="card-header mt-6">
                    <!--begin::Card toolbar-->
                    <div class="ms-auto">
                        <div class="row">
                            <div class="col-md-12 pt-2 text-center" style="border: 1px dashed;">
                                <div class="row d-flex justify-content-center">
                                    <div class="col-auto">
                                        <div id="qr-reader" class="mx-auto text-center"></div>
                                    </div>
                                </div>
                                <div class="row mb-3 mt-3 gap-2 gap-md-0 d-flex justify-content-center">
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-primary"
                                            onclick="giftPermission()">Permission</button>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-primary" onclick="stopCamera()">Stop
                                            Kamera</button>
                                    </div>
                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-primary" onclick="scanBackCamera()">Mulai
                                            Scan</button>
                                    </div>
                                </div>
                                <div class="row mb-3 mt-3 gap-2 gap-md-0 d-flex mt-2">
                                    <div class="col-auto">
                                        <input type="text" class="form-control" id="qrcode" onchange="checkData()"
                                            autofocus>
                                    </div>

                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-success" onchange="checkData()">Proses</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card toolbar-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-0 ">
                    <div class="mb-2 d-flex align-items-center flex-wrap justify-content-between gap-3 border-0 pt-6">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1 online-only">List Riwayat Scan</span>
                        </h3>
                        <!--end::Card title-->
                        <div class="d-flex flex-wrap gap-3">
                            <div class="mt-1">
                                <a type="button" class="btn btn-sm btn-primary text-nowrap online-only"
                                    onclick="addCheckIn()">
                                    <i class="fa fa-plus"></i>
                                    Checkin
                                </a>
                            </div>
                            <div class="mt-1">
                                <a type="button" class="btn btn-sm btn-primary text-nowrap online-only"
                                    onclick="importCheckinCheckout()">
                                    <i class="ki-duotone ki-exit-down fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    Import
                                </a>
                            </div>
                            <div class="online-only d-flex align-items-center">
                                @if(Auth::user()->is_show_all_gymplace)
                                <div class="me-2">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                        <option value="">Semua Gym Place</option>
                                        @foreach ($gym_places as $gym_place)
                                        <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <div class="me-2">
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
                                        class="btn btn-sm btn-light text-dark fw-600 d-flex align-items-center px-4 mb-0">
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
                                </div>
                                <input type="text" id="start_date" hidden>
                                <input type="text" id="end_date" hidden>
                            </div>
                        </div>
                    </div>
                    <!-- HTML -->
                    <div id="cardCheckinTableOffline" class="d-none">
                        <h4>Riwayat Checkin Membership Offline</h4>
                        <p>Ada data checkin belum di sinkronisasi, klik sinkronkan sekarang</p> <a
                            class="btn btn-sm btn-primary" onclick="syncCheckinData()">Sinkronkan</a>
                        <table class="table table-hover align-middle table-row-dashed" id="checkinTableOffline">
                            <thead>
                                <tr>
                                    <td>Member ID</td>
                                    <th>Checkin</th>
                                    <th>Checkout</th>
                                    {{-- <th>Locker</th> --}}
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="table-responsive online-only">
                        <!--begin::Table-->
                        <table id="datatable" class="table table-hover align-middle table-row-dashed online-only">
                            <thead>
                                <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                    <th style="width: 5%">No</th>
                                    <th>Nama User</th>
                                    <th>Note</th>
                                    <th>Waktu Scan</th>
                                    <th>Status</th>
                                    <th>Checkin type</th>
                                    <th class="text-center min-w-100px">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="text-dark fw-semibold"></tbody>
                        </table>
                        <!--end::Table-->
                    </div>
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->

    @include('admins.qrcode_scan.modal')
@endsection
@include('admins.qrcode_scan.script')
