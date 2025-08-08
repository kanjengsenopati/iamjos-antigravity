@extends('layouts.master', ['title' => 'Detail Coach Recap', 'main' => 'Coach'])
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
                            <h3 class="text-capitalize mb-0">Sesi Coach {{ $personal_trainer->name }}</h3>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <div class="card mt-6">
                    <div class="card-body v2">
                        <h4 class="mb-3">Periode {{ $date }}</h4>
                        <div class="row">
                            <div class="col-6">
                                <h3>Total Sesi</h3>
                                <div class="mb-2">
                                    <label class="text-muted">Total Sesi</label>
                                    <p class="text-label">{{ $sesi }}</p>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted">Paket Sesi</label>
                                    <p class="text-label"><?= $data_package; ?></p>
                                </div>
                            </div>
                            <div class="col-6">
                                <h3>Total Kelas</h3>
                                <div class="mb-2">
                                    <label class="text-muted">Total Kelas</label>
                                    <p class="text-label">{{ $class }}</p>
                                </div>
                                <div class="mb-2">
                                    <label class="text-muted">Paket Kelas</label>
                                    <p class="text-label"><?= $data_classes; ?></p>
                                </div>
                            </div>
                        </div>
                        <!--end::Container-->
                    </div>
                    <!--end::Post-->
                </div>
            </div>
        </div>
        <!--end::Wrapper-->
    </div>
@endsection
