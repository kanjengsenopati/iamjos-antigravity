@extends('layouts.master', ['title' => 'Referral Kode Setting', 'main' => 'Dashboard'])

@push('css')
<style>
	.w-170px {

		width: 170px;
	}
</style>
@endpush
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body">
                        <div
                            class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Referral Code Setting</span>
                            </h3>
                            @if(Auth::user()->is_show_all_gymplace)
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                        <option value="">Semua Gym Place</option>
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
                        </div>
                        <div class="hover-scroll-x mt-4">
                            <div class="d-grid">
                                <ul class="nav nav-tabs flex-nowrap text-nowrap">
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary active"
                                            id="nav_tab_referral_setting" data-bs-toggle="tab" href="#tab_referral_setting">
                                            Bonus Referral Setting
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                            id="nav_tab_referral_code_history" data-bs-toggle="tab"
                                            href="#tab_referral_code_history">
                                            Riwayat Referral Kode
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <div class="card card-flush mt-6">
                    <div class="card-body">
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade show active" id="tab_referral_setting" role="tabpanel">
                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link text-dark active" id="membership-tab" data-bs-toggle="tab" data-bs-target="#membership-tab-pane"
                                            type="button" role="tab" aria-controls="membership-tab-pane" aria-selected="false">List Membership</button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link text-dark" id="coach-plus-tab" data-bs-toggle="tab" data-bs-target="#coach-plus-tab-pane"
                                            type="button" role="tab" aria-controls="coach-plus-tab-pane" aria-selected="true">List Coach Plus</button>
                                    </li>
                                </ul>
                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="membership-tab-pane" role="tabpanel" aria-labelledby="membership-tab" tabindex="0">
                                        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                                            <h4>List Membership</h4>
                                        </div>
                                        <!--begin::Table-->
                                        <div>
                                            <div class="d-flex">
                                                <button type="submit" class="btn btn-sm btn-success me-3" id="membership_id_active">Active</button>
                                                <button type="submit" class="btn btn-sm btn-warning" id="membership_id_nonactive">Non-Active</button>
                                            </div>
                                            <table id="datatable-membership" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                                <thead>
                                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                        <th><input type="checkbox" onchange="checkAll(this)"></th>
                                                        <th style="width: 5%">No</th>
                                                        <th>Thumbnail</th>
                                                        <th class="min-w-100px">Nama Kelas</th>
                                                        <th>Bonus Maksimal<br>per User</th>
                                                        <th>Bonus Pemilik Referral</th>
                                                        <th>Bonus Pengguna Referral</th>
                                                        <th>Status</th>
                                                        <th class="text-center min-w-100px">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-dark fw-semibold"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade " id="coach-plus-tab-pane" role="tabpanel" aria-labelledby="coach-plus-tab" tabindex="0">
                                        <div class="border-0 pt-6 gap-4 d-flex mb-3 flex-wrap justify-content-between align-items-center">
                                            <h4>List Coach Plus</h4>
                                        </div>
                                        <div>
                                            <div class="d-flex">
                                                <button type="submit" class="btn btn-sm btn-success me-3" id="coach_plus_id_active">Active</button>
                                                <button type="submit" class="btn btn-sm btn-warning" id="coach_plus_id_nonactive">Non-Active</button>
                                            </div>
                                            <table id="datatable-coach-plus"
                                                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                                <thead>
                                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                        <th><input type="checkbox" onchange="checkAllCoachPlus(this)"></th>
                                                        <th style="width: 5%">No</th>
                                                        <th>Thumbnail</th>
                                                        <th class="min-w-100px">Nama Kelas</th>
                                                        <th>Bonus Maksimal<br>per User</th>
                                                        <th>Bonus Pemilik Referral</th>
                                                        <th>Bonus Pengguna Referral</th>
                                                        <th>Status</th>
                                                        <th class="text-center min-w-100px">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-dark fw-semibold"></tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="tab_referral_code_history" role="tabpanel">
                                <div class="border-0 pt-6 gap-4 d-flex mb-3 flex-wrap justify-content-between align-items-center">
                                    <h4>Riwayat Pendaftaran Menggunakan Referral Kode</h4>
                                </div>
                                <div>
                                    <table id="datatable-referral-code-history"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th style="width: 5%">No</th>
                                                <th>Tanggal Register</th>
                                                <th class="min-w-100px">Referral Kode</th>
                                                <th class="min-w-100px">Nama User Pengguna</th>
                                                <th class="min-w-100px">Nama User Pemilik</th>
                                                <th class="min-w-50px">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
			<!--end::Container-->
		</div>
		<!--end::Post-->
	</div>
</div>

{{-- modal --}}
<div class="modal fade" id="modal-referral-code" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
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
{{-- end modal --}}
@endsection
@push('js')
<script>
    // Panggil fungsi tableMembership saat halaman pertama kali dimuat
        $(document).ready(function() {
            tableMembership();
        });

        $('#membership-tab').on('click', function() {
            tableMembership();
        });

        $('#coach-plus-tab').on('click', function() {
            tableX();
        });

        $('#nav_tab_referral_code_history').on('click', function() {
            tableReferralCode();
        });

        var tableMembershipInstance;
        var tableXInstance;
        var tableReferralCodeInstance;

        $('#gym_place_id').on('change', function() {
            if($('#membership-tab').hasClass('active')) {
                tableMembership();
            }
            if($('#coach-plus-tab').hasClass('active')) {
                tableX();
            }
        });

        function tableMembership() {
            tableMembershipInstance = $('#datatable-membership').DataTable({
                ordering: true,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('referral-code.index', 'data=Membership') }}",
                    type: 'GET',
                    data: {
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
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
					{
						data: 'thumbnail',
						name: 'thumbnail',
                        sortable: false,
						render: function(data, type, row, meta) {
							if (row.thumbnail) {
								return `<img src="${row.thumbnail}" alt="image" class="w-75px h-50px object-fit-cover" />`;
							} else {
								return '';
							}
						}
					},
                    { data: 'name', name: 'name', orderable: true, searchable: true, },
                    { data: 'referral_owner_bonus_max', name: 'referral_owner_bonus_max', sortable: false },
                    { data: 'bonus_owner', name: 'bonus_owner', sortable: false },
                    { data: 'bonus_user', name: 'bonus_user', sortable: false },
                    { data: 'status', name: 'status', sortable: true },
                    {
                        data: 'action',
                        name: 'action',
                        sortable: false,
                        searchable: false,
                        responsivePriority: -1
                    },
                ]
            });
            
            // Add click event listeners to buttons
            $('#membership_id_active').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda akan mengaktifkan Bonus Referral Code Pada Membership ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Aktifkan",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleButtonClick('active');
                    }
                });
            });
        
            $('#membership_id_nonactive').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda akan menonaktifkan Bonus Referral Code Pada Membership ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Nonaktifkan",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleButtonClick('nonactive');
                    }
                });
            });

            // Function to handle button click events
            function handleButtonClick(value) {
                const rows = tableMembershipInstance.rows({ search: 'applied' }).nodes();
                const selectedValues = $(rows).find('input[type="checkbox"]:checked').map((_, el) => $(el).val()).get();

                $.ajax({
                    url: '{{ route("referral-code.membership.update-status") }}',
                    method: 'POST',
                    data: { membership_id: selectedValues, value },
                    success: function(response) {
                        tableMembershipInstance.ajax.reload();
                        Swal.fire({
                            title: 'Sukses!',
                            text: response,
                            icon: 'success',
                            confirmButtonText: 'Tutup'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }

            tableMembershipInstance.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-membership tbody').empty();
            });

            tableMembershipInstance.on('draw.dt', function() {
                $('#datatable-membership').fadeIn();
            });
        }

        function tableX() {
            tableXInstance = $('#datatable-coach-plus').DataTable({
                ordering: true,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('referral-code.index', 'data=CoachPlus') }}",
                    type: 'GET',
                    data: {
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
                columns: [
                    { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
						data: 'thumbnail',
						name: 'thumbnail',
                        sortable: false,
						render: function(data, type, row, meta) {
							if (row.thumbnail) {
								return `<img src="${row.thumbnail}" alt="image" class="w-75px h-50px object-fit-cover" />`;
							} else {
								return '';
							}
						}
					},
                    { data: 'name', name: 'name', orderable: true, searchable: true, },
                    { data: 'referral_owner_bonus_max', name: 'referral_owner_bonus_max', sortable: false },
                    { data: 'bonus_owner', name: 'bonus_owner', sortable: false },
                    { data: 'bonus_user', name: 'bonus_user', sortable: false },
                    { data: 'status', name: 'status', sortable: true },
                    {
                        data: 'action',
                        name: 'action',
                        sortable: false,
                        searchable: false,
                        responsivePriority: -1
                    },
                ]
            });

            // Add click event listeners to buttons
            $('#coach_plus_id_active').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda akan mengaktifkan Bonus Referral Code Pada Coach Plus ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Aktifkan",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleButtonClick('active');
                    }
                });
            });
        
            $('#coach_plus_id_nonactive').on('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: "Apakah Anda Yakin?",
                    text: "Anda akan menonaktifkan Bonus Referral Code Pada Coach Plus ini?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Ya, Nonaktifkan",
                    cancelButtonText: "Batal",
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleButtonClick('nonactive');
                    }
                });
            });

            // Function to handle button click events
            function handleButtonClick(value) {
                const rows = tableXInstance.rows({ search: 'applied' }).nodes();
                const selectedValues = $(rows).find('input[type="checkbox"]:checked').map((_, el) => $(el).val()).get();

                $.ajax({
                    url: '{{ route("referral-code.coach-plus.update-status") }}',
                    method: 'POST',
                    data: { coach_plus_id: selectedValues, value },
                    success: function(response) {
                        tableXInstance.ajax.reload();
                        Swal.fire({
                            title: 'Sukses!',
                            text: response,
                            icon: 'success',
                            confirmButtonText: 'Tutup'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                    }
                });
            }

            tableXInstance.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-coach-plus tbody').empty();
            });

            tableXInstance.on('draw.dt', function() {
                $('#datatable-coach-plus').fadeIn();
            });
        }

        function tableReferralCode() {
            if (tableReferralCodeInstance) {
                tableReferralCodeInstance.ajax.reload();
                return;
            }

            tableReferralCodeInstance = $('#datatable-referral-code-history').DataTable({
                ordering: true,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('referral-code.index', 'data=ReferralCodeHistory') }}"
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
                    { data: 'created_at', name: 'created_at', sortable: true },
                    { data: 'referral_code', name: 'referral_code', sortable: false },
                    { data: 'user.name', name: 'user.name', orderable: true, searchable: true },
                    { data: 'owner.name', name: 'owner.name', orderable: true, searchable: true },
                    {
                        data: 'action',
                        name: 'action',
                        sortable: false,
                        searchable: false,
                        responsivePriority: -1
                    },
                ]
            });

            tableXInstance.on('preXhr.dt', function(e, settings, data) {
                $('#datatable-coach-plus tbody').empty();
            });

            tableXInstance.on('draw.dt', function() {
                $('#datatable-coach-plus').fadeIn();
            });
        }

        function checkAll(source) {
            checkboxes = document.getElementsByName('membership_id[]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function checkAllCoachPlus(source) {
            checkboxes = document.getElementsByName('gym_class_bundling_id[]');
            for (var i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }      

        function showDetail(id) {
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
</script>
@endpush