@push('css')
@if (!auth()->guard('web')->user()?->roles()?->first()?->is_allowed_delete)
<style>
    .btn-delete-membership {
        display: none !important;
    }

    .btn-delete-bundling {
        display: none !important;
    }
</style>
@endif
@endpush
<x-alert.alert-validation />
<span class="mb-3">
    <h4>Membership Aktif</h4>
    <a onclick="addMembership()" type="button" class="badge badge-primary btn-superadmin">Tambah
        Membership</a>
</span>
<div class="row">
    @foreach ($activeMemberships as $activeMembership)
    <div class="col-sm-3 card m-1">
        <img src="{{ $activeMembership->member?->thumbnail }}" style="max-height:250px" class="img img-thumbnail"
            alt="">
        <strong>{{ $activeMembership->member?->name }}</strong>
        <strong>{{ $activeMembership->club_type == 'ALL' ? 'All Club' : 'Single Club - ' . $activeMembership->member?->gym_place?->name }}</strong>
        <i><small>Start {{ $activeMembership->start_active_date ?? '' }}</small></i></br>
        <i> <small>Expired {{ $activeMembership->expiry_date }}</small></i>
        {{-- <i> <small>{{ $activeMembership?->remaining_session ? $activeMembership->remaining_session . ' Sesi' :
                'Lifetime' }}</small></i> --}}
        {{ $activeMembership->getRawOriginal('is_active') ? 'Aktif' : 'Non Aktif' }}
        {{ $activeMembership->is_timeoff ? '(Cuti)' : '' }}
        <div class="d-flex justify-content-between">
            <i class="fa fa-edit text-primary btn-superadmin" role="button"
                data-membership-id="{{ $activeMembership->id }}"
                data-start-active-date="{{ $activeMembership->start_active_date }}"
                data-expiry-date="{{ $activeMembership->expiry_date }}" onclick="editMembership(this)"></i>
            <form action="{{ route('membership-user.delete', $activeMembership->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <i class="fa fa-trash text-danger btn-delete-membership btn-superadmin" role="button"
                    onclick="deleteMembership(this)"></i>
            </form>
        </div>
    </div>
    @endforeach
</div>

<span class="mb-3">
    <h4 class="mt-4">Paket Coach Plus Aktif</h4> <a onclick="addBundling()" type="button"
        class="badge badge-primary btn-superadmin">Tambah
        Paket PT</a>
</span>
<div class="row">
    @foreach ($activePtPlus as $ptPlus)
    <div class="col-sm-3 card m-1">
        <img style="max-height:250px" src="{{ $ptPlus->gym_class_bundling?->thumbnail }}" class="img img-thumbnail"
            alt="">
        <strong>{{ $ptPlus->gym_class_bundling?->name }}</strong>
        <strong>{{ $ptPlus->club_type == 'ALL' ? 'All Club' : 'Single Club - ' . $ptPlus->gym_class_bundling?->gym_place?->name }}</strong>
        <i><small>Start {{ $ptPlus->start_active_date ?? '' }}</small></i></br>
        {{-- <i> <small>Expired {{ $ptPlus->expiry_date == null ? 'Lifetime' : $ptPlus->expiry_date }}</small></i> --}}
        <i> <small>Expired {{ $ptPlus->expiry_date }}</small></i>
        <div class="d-flex justify-content-between">
            <i class="fa fa-edit text-primary btn-superadmin" role="button" data-bundling-id="{{ $ptPlus->id }}"
                data-start-active-date="{{ $ptPlus->start_active_date }}" data-expiry-date="{{ $ptPlus->expiry_date }}"
                onclick="editBundling(this)"></i>
            <form action="{{ route('bundling-user.delete', $ptPlus->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <i class="fa fa-trash text-danger btn-delete-bundling btn-superadmin" role="button"
                    onclick="deleteBundling(this)"></i>
            </form>
        </div>
    </div>
    @endforeach
</div>

<span class="mb-3">
    <h4 class="mt-4">Coach Aktif</h4> <a onclick="addPersonalTrainer()" type="button"
        class="badge badge-primary btn-superadmin">Tambah
        Coach</a>
</span>
<div class="row">
    @foreach ($activePersonalTrainerPacketSessions as $ptActive)
    <div class="col-sm-3 card m-1">
        <img style="max-height:250px" src="{{ $ptActive->packet?->thumbnail ?? '' }}" class="img img-thumbnail" alt="">
        <strong>{{ $ptActive->getPacketAttribute()?->name ?? '' }}</strong>
        <strong>{{ $ptActive->personal_trainer?->name ?? '' }}</strong>
        <i><small>Start {{ $ptActive->start_active_date ?? '' }}</small></i></br>
        <i> <small>Expired {{ $ptActive->expiry_date == null ? 'Lifetime' : $ptActive->expiry_date }}</small></i>
        <i>
            <small>
                @php
                $remainingSession = $ptActive?->remaining_session;
                @endphp
                @switch($remainingSession)
                @case(0)
                Sesi Habis
                @break

                @default
                {{ $remainingSession . ' Sesi' }}
                @endswitch
            </small>
        </i>
        {{-- <i> <small>{{ $ptActive?->remaining_session ? $ptActive->remaining_session . ' Sesi' : 'Lifetime'
                }}</small></i> --}}
        @if ($ptActive->personal_trainer)
        <i> <small>Level
                {{ $ptActive->personal_trainer?->personal_trainer_level?->name ?? '' }}</small></i>
        <span>Pengalaman {{ $ptActive->personal_trainer?->experience_year ?? '' }}
            Tahun</span>
        <span>Total Member {{ $ptActive->personal_trainer?->total_member ?? '' }}</span>
        {{-- add icon star --}}
        <span> Rating <i class="fa fa-star text-warning"></i>
            {{ $ptActive->personal_trainer?->total_rating ?? '' }}</span>
        @else
        <span class="text-danger">Coach Belum Dipilih</span>
        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#selectPersonalTrainerModal{{ $ptActive->id }}">
            Pilih Coach
        </button>

        <!-- Modal -->
        <div class="modal fade" id="selectPersonalTrainerModal{{ $ptActive->id }}" tabindex="-1" aria-labelledby="selectPersonalTrainerModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="selectPersonalTrainerModalLabel">Pilih Coach</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="coachForm{{ $ptActive->id }}" method="POST" action="{{ route('personal-trainer.packet-sesion-history.assign-coach', $ptActive->id) }}">
                        @csrf
                        <div class="modal-body">
                            <input type="hidden" name="packet_session_history_id" value="{{ $ptActive->id }}">
                            <select class="form-select" id="coachSelect{{ $ptActive->id }}" name="personal_trainer_id" required>
                                <option value="">Pilih Coach</option>
                                @foreach($ptActive['personal_trainer_lists'] as $coach)
                                    <option value="{{ $coach->id }}">{{ $coach->name }} (Level: {{ $coach->personal_trainer_level?->name ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @endif
        <div class="d-flex justify-content-between">
            @if ($ptActive->personal_trainer)
            <i class="fa fa-edit text-primary btn-superadmin" role="button"
                data-personal-trainer-id="{{ $ptActive->personal_trainer_id }}"
                data-personal-trainer-packet-session-id="{{ $ptActive->packet->id }}"
                data-personal-trainer-packet-session-history-id="{{ $ptActive->id }}"
                data-start-active-date="{{ date('Y-m-d', strtotime($ptActive->start_active_date)) }}"
                data-expiry-date="{{ date('Y-m-d', strtotime($ptActive->expiry_date)) }}" onclick="editPT(this)"></i>
            @endif

            <form action="{{ route('personal-trainer-packet-user.delete', $ptActive->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <i class="fa fa-trash text-danger btn-delete-bundling btn-superadmin" role="button"
                    onclick="deleteBundling(this)"></i>
            </form>
        </div>
    </div>
    @endforeach
</div>

<span class="mb-3">
    <h4 class="mt-4">Fisioterapi Aktif</h4> 
    <a onclick="addPhysiotherapy()" type="button" class="badge badge-primary btn-superadmin">
        Tambah Fisioterapi
    </a>
</span>
<div class="row">
    @foreach ($activeFisioterapiPacketSessions as $fisioActive)
    <div class="col-sm-3 card m-1">
        <img style="max-height:250px" src="{{ $fisioActive->packet?->thumbnail ?? '' }}" class="img img-thumbnail" alt="">
        <strong>{{ $fisioActive->physiotherapy_packet_session?->name ?? '' }}</strong>
        <strong>{{ $fisioActive->employee?->name ?? '' }}</strong>
        <i><small>Start {{ $fisioActive->start_active_date ?? '' }}</small></i></br>
        <i> <small>Expired {{ $fisioActive->expiry_date }}</small></i>
        <i>
            <small>
                @php
                $remainingSession = $fisioActive?->remaining_session;
                @endphp
                @switch($remainingSession)
                @case(0)
                Sesi Habis
                @break

                @default
                {{ $remainingSession . ' Sesi' }}
                @endswitch
            </small>
        </i>
        <i> <small>Level {{ $fisioActive->employee?->physiotherapies?->personal_trainer_level?->name ?? '' }}</small></i>
        <span>Pengalaman {{ $fisioActive->employee?->physiotherapies?->start_experience_year ? (int) date('Y') - $fisioActive->employee?->physiotherapies?->start_experience_year : '' }} Tahun</span>
        
        <div class="d-flex justify-content-between">
            <i class="fa fa-edit text-primary btn-superadmin" role="button"
                data-physiotherapy-id="{{ $fisioActive->physiotherapy_id }}"
                data-physiotherapy-packet-session-id="{{ $fisioActive->packet?->id }}"
                data-physiotherapy-packet-session-history-id="{{ $fisioActive->id }}"
                data-start-active-date="{{ date('Y-m-d', strtotime($fisioActive->start_active_date)) }}"
                data-expiry-date="{{ date('Y-m-d', strtotime($fisioActive->expiry_date)) }}" 
                data-employee-id="{{ $fisioActive->employee_id }}"
                onclick="editPhysiotherapy(this)"></i>

            <form action="{{ route('physio-packet-history.destroy', $fisioActive->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <i class="fa fa-trash text-danger btn-delete-bundling btn-superadmin" role="button"
                    onclick="deletePhysiotherapy(this)"></i>
            </form>
        </div>
    </div>
    @endforeach
</div>
<!--end::Fisioterapi-->

<!--begin::Cuti-->
<!--begin::cuti Account-->
<div class="card mt-6">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse"
        data-bs-target="#kt_account_deactivate" aria-expanded="true" aria-controls="kt_account_deactivate">
        <div class="card-title m-0">
            <h3 class="fw-bold m-0">Cuti Pengguna</h3>
        </div>
    </div>
    <!--end::Card header-->
    <!--begin::Content-->
    <div id="kt_account_settings_timeoff" class="collapse show">
        <!--begin::Form-->
        <form id="kt_account_timeoff_form" class="form" action="{{ route('user.timeoff') }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input hidden name="id" value="{{ @$user->id }}">
            <input hidden name="start_date" id="deactive_start_date">
            <input hidden name="duration" id="deactive_duration">
            <input hidden name="end_date" id="deactive_end_date">
            <!--begin::Card body-->
            <div class="card-body border-top p-9">
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                    <i class="ki-duotone ki-information fs-2tx text-warning me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <!--end::Icon-->
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-grow-1">
                        <!--begin::Content-->
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Peringatan:
                                Mengubah
                                Pengguna Menjadi Cuti</h4>
                            <div class="fs-6 text-gray-700">Pastikan untuk
                                mengecek
                                dengan cermat sebelum mengubah status pengguna
                                ini
                                menjadi
                                cuti. Tindakan ini akan memblokir akses pengguna
                                ke
                                platform. Harap periksa kembali alasan untuk
                                mengubah status
                                ini dan pastikan bahwa langkah ini diambil
                                dengan
                                pertimbangan matang sesuai dengan kebijakan.
                                Terima kasih
                                atas perhatiannya.</div>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Notice-->
                @if ($user->is_timeoff)
                <!--begin::Notice-->
                <div class="notice d-flex bg-light-danger rounded border-danger border border-dashed mb-9 p-6">
                    <!--begin::Icon-->
                    <i class="ki-duotone ki-information fs-2tx text-danger me-4">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                    <!--end::Icon-->
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-stack flex-grow-1">
                        <!--begin::Content-->
                        <div class="fw-semibold">
                            <h4 class="text-gray-900 fw-bold">Peringatan:
                                User Telah Cuti pada
                                {{ date('d/m/Y', strtotime($user->timeoff_start)) }}~{{ date('d/m/Y',
                                strtotime($user->timeoff_end)) }}
                            </h4>
                        </div>
                        <!--end::Content-->
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Notice-->
                @endif
            </div>
            <!--end::Card body-->
            <!--begin::Card footer-->
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                @if (@$user->is_timeoff == 0)
                <button id="kt_account_deactivate_account_submit" type="submit"
                    class="btn btn-danger btn-sm fw-semibold btn-timeoff btn-superadmin">Ubah
                    Menjadi Cuti</button>
                @else
                <button id="kt_account_deactivate_account_submit" type="submit"
                    class="btn btn-success btn-sm fw-semibold btn-detimeoff btn-superadmin">Aktifkan
                    Akun</button>
                @endif
            </div>
            <!--end::Card footer-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Content-->
</div>
<!--end::cuti Account-->
@push('js')
<script>
    $('.btn-timeoff').on('click', function(e) {
            e.preventDefault();
            var form = $(this).parents('form');
            Swal.fire({
                title: 'Anda akan set Cuti akun ini??',
                icon: 'warning',
                html: `
                        <div class="form-group">
                            <label>Tanggal Mulai Cuti</label>
                            <input type="date" id="selectStartDate" placeholder="Tanggal mulai cuti" class="form-control form-control-sm mt-1">
                        </div>
                        <div class="form-group mt-2">
                            <label>Durasi Cuti (Bulan)</label>
                            <select id="selectDuration" class="form-control form-control-sm mt-1">
                                <option value="">Pilih Durasi</option>
                                <option value="1">1 Bulan</option>
                                <option value="2">2 Bulan</option>
                                <option value="3">3 Bulan</option>
                                <option value="4">4 Bulan</option>
                                <option value="5">5 Bulan</option>
                                <option value="6">6 Bulan</option>
                                <option value="7">7 Bulan</option>
                                <option value="8">8 Bulan</option>
                                <option value="9">9 Bulan</option>
                                <option value="10">10 Bulan</option>
                                <option value="11">11 Bulan</option>
                                <option value="12">12 Bulan</option>
                            </select>
                        </div>
                        <div class="form-group mt-2">
                            <label>Tanggal Selesai Cuti</label>
                            <input type="date" id="selectEndDate" placeholder="Tanggal selesai cuti" class="form-control form-control-sm mt-1">
                        </div>
                    `,
                didOpen: () => {
                    const startDateInput = Swal.getPopup().querySelector('#selectStartDate');
                    const durationSelect = Swal.getPopup().querySelector('#selectDuration');
                    const endDateInput = Swal.getPopup().querySelector('#selectEndDate');

                    startDateInput.addEventListener('change', () => {
                        updateEndDate();
                    });

                    durationSelect.addEventListener('change', () => {
                        updateEndDate();
                    });

                    function updateEndDate() {
                        const startDate = new Date(startDateInput.value);
                        const duration = parseInt(durationSelect.value);

                        if (!isNaN(startDate.getTime()) && duration) {
                            const endDate = new Date(startDate);
                            endDate.setMonth(endDate.getMonth() + duration);
                            endDateInput.value = endDate.toISOString().split('T')[0];
                        } else {
                            endDateInput.value = '';
                        }
                    }
                },
                confirmButtonText: 'Simpan',
                showCancelButton: true,
                cancelButtonText: 'Batal',
                focusConfirm: false,
                preConfirm: () => {
                    const startDate = Swal.getPopup().querySelector('#selectStartDate').value
                    const duration = Swal.getPopup().querySelector('#selectDuration').value
                    const endDate = Swal.getPopup().querySelector('#selectEndDate').value
                    if (!startDate || !endDate) {
                        Swal.showValidationMessage(
                            `Silakan pilih tanggal mulai dan selesai cuti terlebih dahulu`)
                    }
                    return {
                        startDate,
                        duration,
                        endDate
                    }
                }
            }).then((result) => {
                if (result.value.startDate) {
                    $('#deactive_start_date').val(result.value.startDate)
                    $('#deactive_duration').val(result.value.duration)
                    $('#deactive_end_date').val(result.value.endDate)
                    form.submit();
                }
            })
        });
        $('.btn-detimeoff').on('click', function(e) {
            e.preventDefault();
            var form = $(this).parents('form');
            Swal.fire({
                title: 'Apakah anda yakin?',
                text: "Anda akan mengaktifkan akun ini dari cuti??",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Aktifkan!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function(result) {
                if (result.value) {
                    form.submit();
                }
            });
        });
</script>
@endpush