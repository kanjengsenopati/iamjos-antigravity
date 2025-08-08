<form action="{{ route('membership.add-to-user') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-add-membership-user" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Tambahkan Membership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    <div class="row mb-2">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label fw-semibold fs-6">
                            <span>Filter By Gym Place</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select id="membership_gym_place_id" class="form-control">
                                <option value="">Pilih Gym Place</option>
                                @foreach ($gym_places as $gym_place)
                                <option value="{{ $gym_place->id }}">{{ $gym_place->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row mb-2">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Membership</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="membership_id" id="membership_lists" class="form-control" required>
                                @foreach ($memberships as $membership)
                                <option value="{{ $membership->id }}">{{ $membership->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row">
                        <!--begin::Label-->
                        <label for="start_date" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Tanggal Mulai</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="date" name="start_active_date" class="form-control" id="start_date" required>
                        </div>
                        <!--end::Col-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('membership-user.update', ':id') }}" method="post" enctype="multipart/form-data"
    id="edit-membership-user-form">
    @csrf
    @method('PUT')
    <div class="modal fade" id="modal-edit-membership-user" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit Membership</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    <div class="row">
                        <!-- Begin::Col -->
                        <div class="col-lg-12">
                            <!-- Start Active Date -->
                            <div class="form-group mb-3">
                                <label for="start_active_date" class="col-form-label required fw-semibold fs-6">
                                    Tanggal Mulai
                                </label>
                                <input type="date" name="start_active_date" class="form-control" id="start_active_date"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <!-- Expiry Date -->
                            <div class="form-group mb-3">
                                <label for="expiry_date" class="col-form-label required fw-semibold fs-6">
                                    Tanggal Selesai
                                </label>
                                <input type="date" name="expiry_date" class="form-control" id="expiry_date">
                            </div>
                        </div>
                        <!-- End::Col -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('gym-class-bundling.add-to-user') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-add-bundling-user" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Tambahkan PT Plus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    <div class="row mb-2">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label fw-semibold fs-6">
                            <span>Filter By Gym Place</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select id="gym_class_bundling_gym_place_id" class="form-control">
                                <option value="">Pilih Gym Place</option>
                                @foreach ($gym_places as $gym_place)
                                <option value="{{ $gym_place->id }}">{{ $gym_place->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row mb-2">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Paket PT Plus</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="gym_class_bundling_id" id="gym_class_bundling_lists" class="form-control" required>
                                @foreach ($gymClassBundlings as $gymClassBundling)
                                <option value="{{ $gymClassBundling->id }}">{{ $gymClassBundling->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row">
                        <!--begin::Label-->
                        <label for="start_date" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Tanggal Mulai</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="date" name="start_active_date" class="form-control" id="start_date" required>
                        </div>
                        <!--end::Col-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('bundling-user.update', ':id') }}" method="post" enctype="multipart/form-data"
    id="edit-bundling-user-form">
    @csrf
    @method('PUT')
    <div class="modal fade" id="modal-edit-bundling-user" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit PT Plus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    <div class="row">
                        <!-- Begin::Col -->
                        <div class="col-lg-12">
                            <!-- Start Active Date -->
                            <div class="form-group mb-3 row">
                                <label for="bundling_start_active_date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">
                                    Tanggal Mulai
                                </label>
                                <div class="col-lg-8">
                                    <input type="date" name="start_active_date" class="form-control"
                                        id="bundling_start_active_date" required>
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div class="form-group mb-3 row">
                                <label for="bundling_expiry_date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">
                                    Tanggal Selesai
                                </label>
                                <div class="col-lg-8">
                                    <input type="date" name="expiry_date" class="form-control"
                                        id="bundling_expiry_date">
                                </div>
                            </div>
                        </div>
                        <!-- End::Col -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('personal-trainer.add-to-user') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-add-personal-trainer" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Tambahkan Personal Trainer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    <div class="row">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Paket Sesi Personal Trainer</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="personal_trainer_packet_session_id" class="form-control"
                                onchange="levelPt(this.value)" required>
                                <option value="">Pilih Paket Sesi Personal Trainer</option>
                                @foreach ($personal_trainer_packet_sessions as $packet_session)
                                <option value="{{ $packet_session->id }}">{{ $packet_session->name }} - {{$packet_session->personal_trainer_level->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Personal Trainer</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="personal_trainer_id" id="personal_trainer_packet_session_id"
                                class="form-control" required>
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>




<form id="edit-pt-form" method="post" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal fade" id="modal-edit-pt" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit Personal Trainer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!--begin::Label-->
                        <label for="edit_personal_trainer_id" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Personal Trainer</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="personal_trainer_id" id="edit_personal_trainer_id" class="form-control"
                                required>
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row mt-3">
                        <!-- Begin::Col -->
                        <div class="col-lg-12">
                            <!-- Start Active Date -->
                            <div class="form-group mb-3 row">
                                <label for="edit_pt_start_active_date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">
                                    Tanggal Mulai
                                </label>
                                <div class="col-lg-8">
                                    <input type="date" name="start_active_date" class="form-control"
                                        id="edit_pt_start_active_date" required>
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div class="form-group mb-3 row">
                                <label for="edit_pt_expiry_date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">
                                    Tanggal Selesai
                                </label>
                                <div class="col-lg-8">
                                    <input type="date" name="expiry_date" class="form-control" id="edit_pt_expiry_date">
                                </div>
                            </div>
                        </div>
                        <!-- End::Col -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan</button>
                </div>
            </div>
        </div>
    </div>
</form>



<form action="{{ route('notes.store') }}" method="POST" enctype="multipart/form-data" id="notes-form">
    @csrf
    <input type="text" name="_method" value="POST" id="notes-method" hidden>

    <div class="modal fade" tabindex="-1" id="modal-notes">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="notes-title">Create Notes</h3>
                </div>

                <div class="modal-body">
                    <input type="hidden" name="user_id" value="{{ @$user->id }}">
                    <div class="fv-row mb-6">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label mt-3" for="description">
                            <span class="required text-dark">Deskripsi</span>
                            <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                title="Input Deskripsi"></i>
                        </label>
                        <!--end::Label-->
                        <!--begin::Input-->
                        <textarea class="form-control" id="notes-description" name="description" required></textarea>
                        <!--end::Input-->
                    </div>
                    <!--begin::Input group-->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form action="{{ route('session-user.store') }}" method="POST" enctype="multipart/form-data" id="session-form">
    @csrf

    <div class="modal fade" tabindex="-1" id="modal-session">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="session-title">Create Session</h3>
                </div>

                @if ($activePersonalTrainerPacketSessions->count() > 0)
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="{{ @$user->id }}">
                        <input type="hidden" name="personal_trainer_id" id="personal_trainer_id">
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="description">
                                <span class="required text-dark">Sesi Coach</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="personal_trainer_packet_session_history_id" id="personal_trainer_packet_session_history_id" onchange="getPersonalTrainerHistory(this.value)" class="form-control">
                                <option value="">Pilih Sesi Coach</option>
                                @foreach ($activePersonalTrainerPacketSessions as $packet_session)
                                    <option value="{{ $packet_session->id }}">{{ $packet_session->name . ", Coach : " . $packet_session->personal_trainer?->name ?? "Belum Memilih Coach" }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <div class="row mb-6">
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Coach</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="" id="personal_trainer" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Total Sesi</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="" id="remaining_session" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Aktif</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="date" name="" id="session_start_active_date" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Expired</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="date" name="" id="session_expiry_date" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="">
                                <span class="required text-dark">Tanggal Sesi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="" class="form-control" id="personal_trainer_schedule" onchange="getPersonalTrainerSchedule(this.value, $('#personal_trainer_id').val())"></select>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="">
                                <span class="required text-dark">Waktu Sesi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="personal_trainer_schedule_id" class="form-control" id="personal_trainer_schedule_time"></select>
                            <!--end::Input-->
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan Sesi Coach</button>
                    </div>
                @else
                    <div class="modal-body">
                        <p class="text-center">Member ini belum memiliki session</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                @endif

            </div>
        </div>
    </div>
</form>

<form action="{{ route('physiotherapy-history.store') }}" method="POST" enctype="multipart/form-data" id="session-form">
    @csrf

    <div class="modal fade" tabindex="-1" id="modal-physiotherapy-session">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="session-title">Create Session</h3>
                </div>

                @if ($physiotherapy_packet_session_histories->count() > 0)
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="{{ @$user->id }}">
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="description">
                                <span class="required text-dark">Paket Fisioterapi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="physiotherapy_packet_session_history_id" id="physiotherapy_packet_session_history_id" class="form-control" onchange="fillPhysiotherapyData(this)">
                                <option value="">Pilih Paket Fisioterapi</option>
                                @foreach ($physiotherapy_packet_session_histories as $physiotherapy_packet_session_history)
                                    <option value="{{ $physiotherapy_packet_session_history?->id }}" 
                                        data-employee-id="{{ $physiotherapy_packet_session_history?->employee_id }}"
                                        data-physiotherapist="{{ $physiotherapy_packet_session_history?->employee?->name }}"
                                        data-start-date="{{ $physiotherapy_packet_session_history?->start_active_date }}"
                                        data-expiry-date="{{ $physiotherapy_packet_session_history?->expiry_date }}"
                                        data-remaining-session="{{ $physiotherapy_packet_session_history?->remaining_session }}">
                                        {{ $physiotherapy_packet_session_history?->physiotherapy_packet_session->name . ", Physioterapist : " . $physiotherapy_packet_session_history?->employee?->name }}
                                    </option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>
                        <div class="row mb-6">
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Physioterapist</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="" id="physiotherapy" value="" class="form-control form-control-solid" readonly>
                                <input type="hidden" name="" id="employee_id" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Total Sesi Tersedia</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="text" name="" id="physiotherapy_remaining_session" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Aktif</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="date" name="" id="physiotherapy_session_start_active_date" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                            <div class="col-6 fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="required text-dark">Expired</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <input type="date" name="" id="physiotherapy_session_expiry_date" value="" class="form-control form-control-solid" readonly>
                                <!--end::Input-->
                            </div>
                        </div>
                        <hr class="my-4">
                        {{-- <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="">
                                <span class="required text-dark">Phiotherapist</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="" class="form-control" id="physiotherapist" onchange="getPhysiotherapySchedule(this.value, 'physiotherapist', '')">
                                <option value="">Pilih Physiotherapist</option>
                                @foreach ($physiotherapies as $physiotherapist)
                                    <option value="{{ $physiotherapist->id }}">{{ $physiotherapist->name }}</option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div> --}}
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="">
                                <span class="required text-dark">Tanggal Sesi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="" class="form-control" id="physiotherapy_schedule" onchange="getPhysiotherapySchedule($('#employee_id').val(), 'schedule', this.value)"></select>
                            <!--end::Input-->
                        </div>
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="">
                                <span class="required text-dark">Waktu Sesi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="physiotherapy_schedule_id" class="form-control" id="physiotherapy_trainer_schedule_time"></select>
                            <!--end::Input-->
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Simpan Sesi Coach</button>
                    </div>
                @else
                    <div class="modal-body">
                        <p class="text-center">Member ini belum memiliki session</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                @endif

            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal-referral-code" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Informasi Referral Kode</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-none" id="referral-code-bonus-filled">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <h5 class="mb-2">Detail Referral Kode</h5>
                        <table class="table table-sm table-bordered" rules="none">
                            <tr height=30px>
                                <td style="width: 29%" class="fw-semibold text-label text-muted">Tanggal Register User</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_register"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 29%" class="fw-semibold text-label text-muted">Kode Referral</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 29%" class="fw-semibold text-label text-muted">User Pemilik Kode Referral</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_register_owner"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 29%" class="fw-semibold text-label text-muted">User Pengguna Kode Referral</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_register_user"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 29%" class="fw-semibold text-label text-muted">Tanggal Pembelian</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_buyed_date"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 29%" class="fw-semibold text-label text-muted">Program di Beli</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_program"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Bonus Pemilik Kode Referral</h5>
                        <table class="table table-sm table-bordered" rules="none">
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">User</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_owner_username"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">Bonus</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_owner_bonus"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">Status Bonus</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_owner_status"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Claim</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_owner_claim"></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h5>Bonus Pengguna Kode Referral</h5>
                        <table class="table table-sm table-bordered" rules="none">
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">User</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_user_username"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">Bonus</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_user_bonus"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">Status Bonus</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_user_status"></td>
                            </tr>
                            <tr height=30px>
                                <td style="width: 36%" class="fw-semibold text-label text-muted">Tanggal Claim</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label" id="referral_code_user_claim"></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-body d-none" id="referral-code-bonus-empty">
                <div class="text-center">
                    <h3>Belum Ada Bonus</h3>
                    <p>Belum ada bonus dari pembelian yang menggunakan kode referral</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal"
                    id="cancel-confirm-event">Tutup</button>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('physio-packet-history.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-add-physiotherapy" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Tambahkan Fisioterapi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="user_id" value="{{ $user->id }}" hidden>
                    <div class="row">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Paket Sesi Fisioterapi</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="physiotherapy_packet_session_id" class="form-control" required>
                                <option value="">Pilih Paket Sesi Fisioterapi</option>
                                @foreach ($physiotherapy_packet_sessions as $packet_session)
                                <option value="{{ $packet_session->id }}">
                                    {{ $packet_session->name }} - {{ $packet_session->personal_trainer_level->name }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row mt-3">
                        <!--begin::Label-->
                        <label for="routine_exercise" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Fisioterapis</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="employee_id" id="physiotherapy_employee_id" class="form-control" required>
                                <option value="">Pilih Fisioterapis</option>
                                @foreach ($physiotherapies as $physiotherapy)
                                <option value="{{ $physiotherapy->id }}">{{ $physiotherapy->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row mt-3">
                        <!--begin::Label-->
                        <label for="start_active_date" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Tanggal Mulai</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <input type="date" name="start_active_date" id="start_active_date" class="form-control" required>
                        </div>
                        <!--end::Col-->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="edit-physiotherapy-form" method="post" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    <div class="modal fade" id="modal-edit-physiotherapy" tabindex="-1" data-bs-backdrop="static" 
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Edit Fisioterapi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!--begin::Label-->
                        <label for="edit_physiotherapy_employee_id" class="col-lg-4 col-form-label required fw-semibold fs-6">
                            <span>Fisioterapis</span>
                        </label>
                        <!--end::Label-->
                        <!--begin::Col-->
                        <div class="col-lg-8 fv-row">
                            <select name="employee_id" id="edit_physiotherapy_employee_id" class="form-control" required>
                                <option value="">Pilih Fisioterapis</option>
                            </select>
                        </div>
                        <!--end::Col-->
                    </div>
                    <div class="row mt-3">
                        <!-- Begin::Col -->
                        <div class="col-lg-12">
                            <!-- Start Active Date -->
                            <div class="form-group mb-3 row">
                                <label for="edit_physiotherapy_start_active_date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">
                                    Tanggal Mulai
                                </label>
                                <div class="col-lg-8">
                                    <input type="date" name="start_active_date" class="form-control"
                                        id="edit_physiotherapy_start_active_date" required>
                                </div>
                            </div>

                            <!-- Expiry Date -->
                            <div class="form-group mb-3 row">
                                <label for="edit_physiotherapy_expiry_date"
                                    class="col-lg-4 col-form-label required fw-semibold fs-6">
                                    Tanggal Selesai
                                </label>
                                <div class="col-lg-8">
                                    <input type="date" name="expiry_date" class="form-control" 
                                        id="edit_physiotherapy_expiry_date">
                                </div>
                            </div>
                        </div>
                        <!-- End::Col -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('js')
<script>
        function addMembership() {
            $('#modal-add-membership-user').modal('show');
        }

        function editMembership(element) {
            var membershipId = element.getAttribute('data-membership-id');
            var startActiveDate = element.getAttribute('data-start-active-date');
            var expiryDate = element.getAttribute('data-expiry-date');
            // add id to form
            $('#edit-membership-user-form').attr('action', "{{ route('membership-user.update', ':id') }}".replace(':id',
                membershipId));

            function convertDateFormat(date) {
                var parts = date.split('-');
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }

            var formattedStartActiveDate = convertDateFormat(startActiveDate);
            var formattedExpiryDate = convertDateFormat(expiryDate);

            $('#start_active_date').val(formattedStartActiveDate);
            $('#expiry_date').val(formattedExpiryDate);

            $('#modal-edit-membership-user').modal('show');
        }

        function addBundling() {
            $('#modal-add-bundling-user').modal('show');
        }

        function editBundling(element) {
            var bundlingId = element.getAttribute('data-bundling-id');
            var startActiveDate = element.getAttribute('data-start-active-date');
            var expiryDate = element.getAttribute('data-expiry-date');
            // add id to form
            $('#edit-bundling-user-form').attr('action', "{{ route('bundling-user.update', ':id') }}".replace(':id',
                bundlingId));

            function convertDateFormat(date) {
                var parts = date.split('-');
                return parts[2] + '-' + parts[1] + '-' + parts[0];
            }

            var formattedStartActiveDate = convertDateFormat(startActiveDate);
            var formattedExpiryDate = convertDateFormat(expiryDate);

            $('#bundling_start_active_date').val(formattedStartActiveDate);
            $('#bundling_expiry_date').val(formattedExpiryDate);

            $('#modal-edit-bundling-user').modal('show');
        }

        function addPersonalTrainer() {
            $('#modal-add-personal-trainer').modal('show');
        }

        function levelPt(id, personal_trainer_id = '', personal_trainer_level_id = '') {
            $.ajax({
                url: "{{ url('user/get-personal-trainer/by-level?packet_session_id=:id') }}".replace(':id', id),
                data: {
                    personal_trainer_id,
                    personal_trainer_level_id
                },
                method: 'get',
                success: function(data) {
                    $('#personal_trainer_packet_session_id').html(data);
                    $('#edit_personal_trainer_id').html(data);
                }
            });
        }

        function createNotes() {
            $("#modal-notes").modal("show")
        }

        function editNotes(id) {
            $.ajax({
                url: "{{ url('notes') }}/" + id,
                method: 'get',
                type: 'json',
            }).done(function(data) {
                $("#notes-method").val('PUT');
                $("#notes-form").attr('action', "{{ route('notes.update', ':id') }}".replace(':id', id));
                $("#notes-title").text('Edit Notes')
                $('#notes-description').val(data.description);
                $("#modal-notes").modal("show")
            });
        }
        
        function createSession() {
            $("#modal-session").modal("show")
        }

        function getPersonalTrainerHistory(id) {
            let action = "{{ route('session-user.history', ':id') }}".replace(':id', id);
            axios.get(action)
                .then(function(response) {
                    if (response.data.status == "failed") {
                        alert(response.data.message);
                        $("#modal-session").modal("hide");
                    }
                    if (!response.data.personal_trainer) {
                        alert('Personal Trainer tidak ditemukan. Silahkan tambahkan personal trainer terlebih dahulu.');
                        $("#modal-session").modal("hide");
                    }
                    
                    $('#personal_trainer_id').val(response.data.personal_trainer_id)
                    $('#personal_trainer').val(response.data.personal_trainer)
                    $('#remaining_session').val(response.data.remaining_session)
                    $('#session_start_active_date').val(response.data.start_active_date)
                    $('#session_expiry_date').val(response.data.expiry_date)
                    $('#personal_trainer_schedule').val(response.data.personal_trainer_schedule)
                    
                    let personalTrainerSchedule = response.data.personal_trainer_schedule;
                    let options = '<option value="">Pilih Jadwal</option>';
                    personalTrainerSchedule.forEach(function(schedule) {
                        let date = new Date(schedule);
                        let day = date.toLocaleDateString('id-ID', { weekday: 'long' });
                        let formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                        options += `<option value="${schedule}">${day}, ${formattedDate}</option>`;
                    });
                    $('#personal_trainer_schedule').html(options);

                    $('#personal_trainer_schedule_time').html('<option value="">Pilih Waktu</option>');

                })
                .catch(function(error) {
                    console.error(error);
                    toastr.error(error.message)
                });
        }

        function getPersonalTrainerSchedule(date, personal_trainer_id){
            let action = "{{ route('session-user.coach.schedule', ['date' => '__DATE__', 'personal_trainer_id' => '__TRAINER_ID__']) }}"
                .replace('__DATE__', encodeURIComponent(date))
                .replace('__TRAINER_ID__', encodeURIComponent(personal_trainer_id)).replace('amp;', '');
            axios.get(action).then(function(response) {
                let options = '<option value="">Pilih Waktu</option>';
                response.data.forEach(schedule => {
                    options += `<option value="${schedule.id}"> ${schedule.start_time} - ${schedule.end_time}</option>`;
                });

                $('#personal_trainer_schedule_time').html(options);

            })
            .catch(function(error) {
                console.error(error);
                toastr.error(error.message)
            });
        }

        function createPhisiotherapySession() {
            $("#modal-physiotherapy-session").modal("show")
        }

        function fillPhysiotherapyData(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (selectedOption.value) {
                $('#physiotherapy').val(selectedOption.dataset.physiotherapist);
                $('#physiotherapy_session_start_active_date').val(selectedOption.dataset.startDate);
                $('#physiotherapy_session_expiry_date').val(selectedOption.dataset.expiryDate);
                $('#physiotherapy_remaining_session').val(selectedOption.dataset.remainingSession);
                $('#employee_id').val(selectedOption.dataset.employeeId);
                getPhysiotherapySchedule(selectedOption.dataset.employeeId, 'physiotherapist', '');
            } else {
                // Reset values if no option selected
                $('#physiotherapy').val('');
                $('#physiotherapy_session_start_active_date').val('');
                $('#physiotherapy_session_expiry_date').val('');
                $('#physiotherapy_remaining_session').val('');
                $('#employee_id').val('');
                getPhysiotherapySchedule(selectedOption.dataset.employeeId, 'physiotherapist', '');
            }

        }

        function getPhysiotherapySchedule(id, type, date) {
            let action = "{{ route('physiotherapy-schedule') }}?type=:type&id=:id&date=:date"
                .replace(':type', type)
                .replace(':id', id)
                .replace(':date', date);

            axios.get(action)
                .then(function(response) {
                    if (type == 'physiotherapist') {
                        let physiotherapySchedule = response.data;
                        let options = '<option value="">Pilih Jadwal</option>';
                        physiotherapySchedule.forEach(function(schedule) {
                            let date = new Date(schedule);
                            let day = date.toLocaleDateString('id-ID', { weekday: 'long' });
                            let formattedDate = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                            options += `<option value="${schedule}">${day}, ${formattedDate}</option>`;
                        });
                        $('#physiotherapy_schedule').html(options);

                        $('#physiotherapy_trainer_schedule_time').html('<option value="">Pilih Waktu</option>');
                    } else {
                        let options = '<option value="">Pilih Waktu</option>';
                        response.data.forEach(schedule => {
                            options += `<option value="${schedule.id}"> ${schedule.start_time} - ${schedule.end_time}</option>`;
                        });

                        $('#physiotherapy_trainer_schedule_time').html(options);
                    }
                })
                .catch(function(error) {
                    console.error(error);
                    toastr.error(error.message)
                });
        }

        function editPT(e) {
            let personalTrainerPacketSessionId = e.getAttribute('data-personal-trainer-packet-session-id');
            let personalTrainerId = e.getAttribute('data-personal-trainer-id');
            let personalTrainerPacketSessionHistoryId = e.getAttribute('data-personal-trainer-packet-session-history-id');
            let startActiveDate = e.getAttribute('data-start-active-date');
            let expiryDate = e.getAttribute('data-expiry-date');
            $('#edit_pt_start_active_date').val(startActiveDate);
            $('#edit_pt_expiry_date').val(expiryDate);
            $('#modal-edit-pt').modal('show');
            levelPt(personalTrainerPacketSessionId, personalTrainerId)
            $("#edit-pt-form").attr('action', "{{ route('personal-trainer.update-to-user', ':id') }}".replace(':id',
                personalTrainerPacketSessionHistoryId));
        }

        function showDetailReferral(id) {
            let action = "{{ route('referral-code.user.detail', ':id') }}".replace(':id', id);
            axios.get(action)
                .then(function(response) {
                    if (response.data.status == 200) {
                        $('#referral_code_register').text(response.data.data.referral_code_register)
                        $('#referral_code').text(response.data.data.referral_code)
                        $('#referral_code_register_user').text(response.data.data.referral_code_register_user)
                        $('#referral_code_register_owner').text(response.data.data.referral_code_register_owner)
                        $('#referral_code_buyed_date').text(response.data.data.referral_code_buyed_date)
                        $('#referral_code_program').text(response.data.data.referral_code_program)
                        $('#referral_code_owner_bonus').text(response.data.data.referral_code_owner_bonus)
                        $('#referral_code_owner_status').html(response.data.data.referral_code_owner_status)
                        $('#referral_code_owner_claim').text(response.data.data.referral_code_owner_claim)
                        $('#referral_code_user_bonus').text(response.data.data.referral_code_user_bonus)
                        $('#referral_code_user_status').html(response.data.data.referral_code_user_status)
                        $('#referral_code_user_claim').text(response.data.data.referral_code_user_claim)
                        $('#referral_code_owner_username').html(response.data.data.referral_code_register_owner)
                        $('#referral_code_user_username').text(response.data.data.referral_code_register_user)

                        $('#referral-code-bonus-filled').removeClass('d-none');
                        $('#referral-code-bonus-empty').addClass('d-none');
                    } else if (response.data.status == 404) {
                        $('#referral-code-bonus-filled').addClass('d-none');
                        $('#referral-code-bonus-empty').removeClass('d-none');
                    }

                    $("#modal-referral-code").modal("show")
                })
                .catch(function(error) {
                    console.error(error);
                    toastr.error(error.message)
                });
        }

    function addPhysiotherapy() {
        $('#modal-add-physiotherapy').modal('show');
    }

    function editPhysiotherapy(element) {
        let physiotherapyPacketSessionId = element.getAttribute('data-physiotherapy-packet-session-id');
        let physiotherapyId = element.getAttribute('data-physiotherapy-id');
        let physiotherapyPacketSessionHistoryId = element.getAttribute('data-physiotherapy-packet-session-history-id');
        let startActiveDate = element.getAttribute('data-start-active-date');
        let expiryDate = element.getAttribute('data-expiry-date');
        let EmployeeId = element.getAttribute('data-employee-id');
        console.log(EmployeeId);
        console.log('EmployeeId:', EmployeeId);


        $('#edit_physiotherapy_start_active_date').val(startActiveDate);
        $('#edit_physiotherapy_expiry_date').val(expiryDate);
        
        // Populate physiotherapists dropdown and select the correct option
        $('#edit_physiotherapy_employee_id').html(`
            <option value="">Pilih Fisioterapis</option>
            @foreach ($physiotherapies as $physiotherapy)
            <option value="{{ $physiotherapy->id }}" ${EmployeeId == '{{ $physiotherapy->id }}' ? 'selected' : ''}>
                {{ $physiotherapy->name }}
            </option>
            @endforeach
        `);
        
        $('#modal-edit-physiotherapy').modal('show');

        $("#edit-physiotherapy-form").attr('action', 
            "{{ route('physio-packet-history.update', ':id') }}".replace(':id', physiotherapyPacketSessionHistoryId)
        );
    }
    
    function deletePhysiotherapy(element) {
        var form = $(element).closest('form');
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda akan menghapus paket fisioterapi ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
</script>
<script>
    $(document).ready(function() {
        $('#membership_gym_place_id').change(function() {
            var gymPlaceId = $(this).val();
            var userId = "{{ $user->id }}";
            
            if(gymPlaceId) {
                $.ajax({
                    url: "{{ route('user.show', ':id') }}".replace(':id', userId),
                    type: 'GET',
                    data: {
                        type: 'membership',
                        gym_place_id: gymPlaceId
                    },
                    success: function(response) {
                        var membershipSelect = $('#membership_lists');
                        membershipSelect.empty();
                        
                        if(response.length > 0) {
                            $.each(response, function(index, membership) {
                                membershipSelect.append(
                                    $('<option></option>')
                                        .val(membership.id)
                                        .text(membership.name)
                                );
                            });
                        } else {
                            membershipSelect.append(
                                $('<option></option>')
                                    .val('')
                                    .text('Tidak ada membership tersedia')
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            } else {
                var membershipSelect = $('#membership_lists');
                membershipSelect.empty();
                
                @foreach ($memberships as $membership)
                    membershipSelect.append(
                        $('<option></option>')
                            .val("{{ $membership->id }}")
                            .text("{{ $membership->name }}")
                    );
                @endforeach
            }
        });
        
        $('#gym_class_bundling_gym_place_id').change(function() {
            var gymPlaceId = $(this).val();
            var userId = "{{ $user->id }}";
            
            if(gymPlaceId) {
                $.ajax({
                    url: "{{ route('user.show', ':id') }}".replace(':id', userId),
                    type: 'GET',
                    data: {
                        type: 'gym-class-bundling',
                        gym_place_id: gymPlaceId
                    },
                    success: function(response) {
                        var gymClassBundlingSelect = $('#gym_class_bundling_lists');
                        gymClassBundlingSelect.empty();
                        
                        if(response.length > 0) {
                            $.each(response, function(index, membership) {
                                gymClassBundlingSelect.append(
                                    $('<option></option>')
                                        .val(membership.id)
                                        .text(membership.name)
                                );
                            });
                        } else {
                            gymClassBundlingSelect.append(
                                $('<option></option>')
                                    .val('')
                                    .text('Tidak ada membership tersedia')
                            );
                        }
                    },
                    error: function(xhr) {
                        console.error('Error:', xhr.responseText);
                    }
                });
            } else {
                var gymClassBundlingSelect = $('#gym_class_bundling_lists');
                gymClassBundlingSelect.empty();
                
                @foreach ($memberships as $membership)
                    gymClassBundlingSelect.append(
                        $('<option></option>')
                            .val("{{ $membership->id }}")
                            .text("{{ $membership->name }}")
                    );
                @endforeach
            }
        });
    });
</script>
@endpush