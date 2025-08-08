@extends('layouts.master', ['title' => 'Data Loker', 'main' => 'Dashboard'])
@push('css')
<style>
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        background-color: transparent !important;
        color: var(--bs-primary) !important;
        font-weight: 500;
        border-bottom: 2px solid var(--bs-primary);
        border-radius: 0;
    }

    .nav-pills .nav-link {
        border-radius: 0;
        color: var(--bs-gray-600) !important;
        padding-bottom: 1rem !important;
        width: 100% !important;
        font-size: 1.25rem !important;
    }

    .nav-pills .nav-item {
        width: 25% !important;
        min-width: 200px !important;
        max-width: 250px !important;
    }


    /* box loker */
    .box_loker {
        width: 100%;
        height: 100%;
        border: 1px solid var(--bs-gray-300);
        border-radius: 8px;
        padding: 2rem 1rem 1rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }

    .box_loker .action {
        display: flex;
        gap: 1.5rem;
        justify-content: flex-end !important;
        align-items: center !important;
        width: 100%;
        margin-top: 1rem !important;
    }

    @media only screen and (min-width: 992px) {
        .nav-pills .nav-link {
            font-size: 1.125rem !important;
        }

        .box_loker h4 {
            font-size: 1.125rem !important;
        }

        .nav-pills .nav-item {
            width: 25% !important;
            min-width: 200px !important;
            max-width: 250px !important;
        }
    }

    @media only screen and (max-width: 991.98px) {
        .box_loker h4 {
            font-size: 1rem !important;
        }

        .box_loker {
            padding: 1.5rem !important;
        }

        .box_loker .action {
            margin-top: .5rem !important;
        }
    }
</style>
@endpush
@section('content')
<!--end::Toolbar-->
<!--begin::Content-->
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header mt-4">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Data Loker</span>
                        </h3>
                        <div class="d-flex gap-4">
                            @if(Auth::user()->is_show_all_gymplace)
                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    <div>
                                        <select name="gym_place_id" id="gym_place_id"
                                            class="form-select form-select-sm w-170px"
                                            onchange="loadLockerData()">
                                            <option value="">Semua Gym Place</option>
                                            @foreach ($gym_places as $gym_place)
                                                <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div class="d-flex flex-wrap gap-4 align-items-center">
                                    <div>
                                        <select name="gym_place_id" id="gym_place_id"
                                            class="form-select form-select-sm w-170px" disabled>
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
                                </div>
                            @endif
                            <div class="card-toolbar">
                                <a type="button" class="btn btn-primary btn-sm text-nowrap btn-create"
                                    href="{{ route('locker.create', ['gym_place_id' => Auth::user()->gym_place_id]) }}">
                                    <i class="ki-duotone ki-plus fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>Loker
                                </a>
                            </div>
                        </div>
                        <!--end::Card toolbar-->
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-0">
                        <div>
                            <div class="nav nav-pills mb-3 w-100 d-flex justify-content-center" id="pills-tab">
                                <div class="nav-item">
                                    <button class="nav-link active" id="pills-male-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-male" type="button" role="tab" aria-controls="pills-male"
                                        aria-selected="true">Loker Laki-Laki</button>
                                </div>
                                <div class="nav-item">
                                    <button class="nav-link" id="pills-female-tab" data-bs-toggle="pill"
                                        data-bs-target="#pills-female" type="button" role="tab"
                                        aria-controls="pills-female" aria-selected="false">Loker Perempuan</button>
                                </div>
                            </div>

                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="pills-male" aria-labelledby="pills-male-tab">
                                    <div id="locker" class="row mt-5 pt-5">
                                        <div id="loading-animation-male"
                                            class="d-flex justify-content-center align-items-center">
                                            <div class="spinner-border text-primary">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="pills-female" aria-labelledby="pills-female-tab">
                                    <div id="locker-list-female" class="row mt-5 pt-5">
                                        <div id="loading-animation-female"
                                            class="d-flex justify-content-center align-items-center">
                                            <div class="spinner-border text-primary">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    // show loading animation
    const showLoading = () => {
        $('#loading-animation-male').show();
        $('#loading-animation-female').show();
    }

    // hide loading animation
    const hideLoading = () => {
        $('#loading-animation-male').hide();
        $('#loading-animation-female').hide();
    }

    // load loker data
    function loadLockerData() {
        showLoading();
        $.ajax({
            url: "{{ route('locker.index') }}",
            type: 'GET',
            dataType: 'json',
            headers: {
                'Accept': 'application/json'
            },
            data: {
                gym_place_id: $('#gym_place_id').val(),
            },
            success: function(response) {
                $('#locker').empty();
                $('#locker-list-female').empty();

                if (response.data.length === 0) {
                    const emptyMessage = `
                        <div class="col-12 text-center">
                            <div class="alert alert-primary">
                                <i class="ki-duotone ki-information-5 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <p class="mt-2">Tidak ada data loker yang tersedia</p>
                            </div>
                        </div>
                    `;
                    $('#locker').append(emptyMessage);
                    $('#locker-list-female').append(emptyMessage);
                    return;
                }

                let hasMaleLocker = false;
                let hasFemaleLocker = false;

                response.data.forEach(function(item) {
                    const checkedAttribute = item.is_available ? 'checked' : '';
                    var lockerHTML = `
                        <div class="col-lg-3 mb-5">
                            <div class="box_loker">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" id="flexSwitchCheckDefault${item.id}"
                                        ${checkedAttribute}>
                                </div>
                                <h4 class="mt-3" id="loker_name">${item.name}</h4>
                                <div class="action">
                                    <a href="/locker/${item.id}" type="button" class="d-block">
                                        <i class="ki-duotone ki-eye fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </a>

                                    <a href="/locker/${item.id}/edit" type="button" class="d-block btn-edit">
                                        <i class="ki-duotone ki-notepad-edit fs-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </a>
                                    <a type="button" class="d-block btn-deleteLoker" data-id="formDelete${item.id}" id="btnDelete${item.id}">
                                        <i class="ki-duotone ki-basket fs-2" data-id="formDelete${item.id}">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3" data-id="formDelete${item.id}"></span>
                                        </i>
                                    </a>
                                    <form class="d-none" id="formDelete${item.id}" action="/locker/${item.id}" method="post">
                                        @csrf
                                        @method('delete')
                                    </form>
                                </div>
                            </div>
                        </div>
                    `;

                    if (item.gender === 'MALE') {
                        $('#locker').append(lockerHTML);
                        hasMaleLocker = true;
                    } else if (item.gender === 'FEMALE') {
                        $('#locker-list-female').append(lockerHTML);
                        hasFemaleLocker = true;
                    }
                });

                if (!hasMaleLocker) {
                    const emptyMaleMessage = `
                        <div class="col-12 text-center">
                            <div class="alert alert-primary">
                                <i class="ki-duotone ki-information-5 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <p class="mt-2">Tidak ada data loker laki-laki yang tersedia</p>
                            </div>
                        </div>
                    `;
                    $('#locker').append(emptyMaleMessage);
                }

                if (!hasFemaleLocker) {
                    const emptyFemaleMessage = `
                        <div class="col-12 text-center">
                            <div class="alert alert-primary">
                                <i class="ki-duotone ki-information-5 fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                                <p class="mt-2">Tidak ada data loker perempuan yang tersedia</p>
                            </div>
                        </div>
                    `;
                    $('#locker-list-female').append(emptyFemaleMessage);
                }

                $('.btn-deleteLoker').click(function(e) {
                    e.preventDefault();
                    var formId = $(this).data('id');
                    var form = $("#" + formId);

                    Swal.fire({
                        title: 'Hapus Data',
                        text: 'Anda yakin akan menghapus data ini? Data yang telah dihapus tidak dapat dikembalikan.',
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal',
                        customClass: {
                            confirmButton: 'btn btn-sm fw-semibold btn-primary',
                            cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            },
            complete: function() {
                hideLoading();
            }
        });
    }

    $(document).ready(function() {
        $(document).on('change', 'input[type="checkbox"]', function() {
            var lockerId = $(this).attr('id').replace('flexSwitchCheckDefault', '');
            var isChecked = $(this).prop('checked');
            changeLockerStatus(lockerId, isChecked);
        });
        
        // change loker status
        function changeLockerStatus(lockerId, isChecked) {
            $.ajax({
                url: "{{ route('locker.change-status') }}",
                type: 'POST',
                data: {
                    locker_id: lockerId,
                    is_checked: isChecked,
                },
                success: function(response) {
                },
                error: function(xhr, status, error) {
                }
            });
        }
        
        // Call function to load locker data when the document is ready
        loadLockerData();
    });
</script>
@endpush