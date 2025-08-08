@push('css')
    <style>
        .modal-cs-lg {
            --bs-modal-width: 940px;
        }
    </style>
@endpush
<div class="table-responsive">
    <table id="datatable" class="table table-hover align-middle table-row-dashed">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Note</th>
                <th>Waktu Scan</th>
                <th>Status</th>
                <th class="text-center min-w-100px">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>

{{-- modal detail --}}
<div class="modal fade" id="modal-confirm-membership" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="membership_check_in_type"></h5>
                <button type="button" class="btn-close" id="close-checkin" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Membership Detail</h5>
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <td>Tempat Gym</td>
                                    <td id="gym_place_membership"></td>
                                </tr>
                                <tr>
                                    <td>Avatar</td>
                                    <td id="avatar_user"></td>
                                </tr>
                                <tr>
                                    <td>Nama User</td>
                                    <td id="user_membership"></td>
                                </tr>
                                <tr>
                                    <td>Membership ID</td>
                                    <td id="user_member_id"></td>
                                </tr>
                                <tr>
                                    <td>Membership</td>
                                    <td id="name_membership"></td>
                                </tr>
                                <tr>
                                    <td>Personal trainer</td>
                                    <td id="name_personal_trainer"></td>
                                </tr>
                                <tr>
                                    <td>Sisa Sesi</td>
                                    <td id="remaining_session_personal_trainer"></td>
                                </tr>
                                {{-- <tr>
                                    <td>Total Sesi</td>
                                    <td id="session_membership"></td>
                                </tr>
                                <tr>
                                    <td>Sesi Dipakai</td>
                                    <td id="taken_session_membership"></td>
                                </tr>
                                <tr>
                                    <td>Sisa Sesi</td>
                                    <td id="remaining_session_membership"></td>
                                </tr> --}}
                                <tr>
                                    <td>Loker</td>
                                    <td id="locker">
                                        <select name="locker_id" id="locker_id" class="form-select">
                                            <option value="">--Pilih Loker--</option>
                                        </select>
                                        <div id="info-locker"></div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div id="gates">
                            <h5>Gates Activity</h5>
                            <table class="table table-sm table-striped table-bordered" id="table-gates">
                            </table>
                        </div>
                        <h5>Notes</h5>
                        <table class="table table-sm table-striped table-bordered" id="table-notes">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- modal gym class --}}
<div class="modal fade" id="modal-show-gym-class" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="check_in_type">Kelas Gym</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>Kelas Detail</h5>
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <td class="w-25">Tempat Gym</td>
                                    <td id="gym_place_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Avatar</td>
                                    <td id="avatar_user_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Nama User</td>
                                    <td id="user_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Membership ID</td>
                                    <td id="user_member_id_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Nama Kelas</td>
                                    <td id="name_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td id="date_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Personal trainer</td>
                                    <td id="name_personal_trainer_gym_class"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('js')
    <script>
        $(function() {
            var start = moment().startOf('year');
            var end = moment().endOf('year');

            function cbTransaction(start, end) {
                $('#dateRangeTransaction span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
                var start = start.format('YYYY-MM-DD');
                var end = end.format('YYYY-MM-DD');
                $('#transaction_start_date').val(start);
                $('#transaction_end_date').val(end);
                tableTransaction()
            }

            $('#dateRangeTransaction').daterangepicker({
                startDate: start,
                endDate: end,
                ranges: {
                    'Hari Ini': [moment(), moment()],
                    'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                    'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1,
                        'month').endOf('month')],
                    '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                    '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                    'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                    'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1,
                        'year').endOf('year')],
                }
            }, cbTransaction);
            cbTransaction(start, end);

            var id = "{{ $user->id }}";
            var table = $('#datatable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('user.checkin-history', ':id') }}".replace(':id', id),
                    type: 'GET',
                    data: {
                        start_date: $("#start_date").val(),
                        end_date: $("#end_date").val(),
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
                    {
                        data: 'name',
                        name: 'name',
                        sortable: false,
                        // searchable: false,
                    },
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            let time = '';
                            if (row.type == 'Membership' && !row.check_out) {
                                time = `Checkin: ${row.check_in}`;
                            } else if (row.type == 'Membership' && row.check_out) {
                                time = `Checkin: ${row.check_in} <br /> Checkout:${row.check_out}`;
                            } else if (row.type == 'GymClass') {
                                time = `Checkin: ${row.check_in}`;
                            } else if (row.type == 'Payment') {
                                time = `${row.created_at}`;
                            } else if (row.type == 'Merchandise') {
                                time = `${row.created_at}`;
                            } else if (row.type == 'Gate') {
                                if (!row.check_out) {
                                    time = `Checkin: ${row.check_in}`;
                                } else {
                                    time = `Checkin: ${row.check_in} <br /> Checkout:${row.check_out}`;
                                }
                            }

                            return time;
                        }
                    },
                    {
                        "data": null,
                        "sortable": false,
                        "searchable": false,
                        render: function(data, type, row, meta) {
                            let status = '';
                            if (row.type == 'Membership') {
                                if (row.check_out != '-') {
                                    status = 'CHECKOUT MEMBERSHIP';
                                } else {
                                    status = 'CHECKIN MEMBERSHIP';
                                }
                            } else if (row.type == 'GymClass') {
                                if (row.status == 'FINISHED') {
                                    status = 'CHECKOUT KELAS';
                                } else {
                                    status = 'CHECKIN KELAS';
                                }
                            } else if (row.type == 'Payment') {
                                status = 'PEMBAYARAN';
                            } else if (row.type == 'Shop') {
                                status = 'PENGAMBILAN BARANG';
                            } else if (row.type == 'Merchandise') {
                                status = 'PENGAMBILAN MERCHANDISE';
                            } else if (row.type == 'Gate') {
                                if (row.check_out != '-') {
                                    status = 'CHECKOUT GATE';
                                } else {
                                    status = 'CHECKIN GATE';
                                }
                            }

                            return `<span class='badge badge-light-success'>${status}</span>`;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -1
                    },
                ]
            });
        });

        function showConfirmMembership(data) {
            let actionUrl = '{{ route('membership.checkin-checkout', ':id') }}'.replace(':id', data.id);
            $('#form-confirm-membership').attr('action', actionUrl);
            // $('#gym_place_membership').text(data.membership.gym_place.name ?? data.gym_class_bundling.gym_place.name);
            $('#gym_place_membership').text(
                (data.membership?.gym_place?.name) ?? (data.gym_class_bundling?.gym_place?.name) ?? ''
            );
            $('#user_membership').text(data.user.name)
            // Periksa apakah URL avatar pengguna ada
            if (data.user.avatar) {
                // Jika ada, tampilkan avatar pengguna
                $('#avatar_user').html(`<img src="${data.user.avatar}" class="img-fluid" style="width: 100px;">`);
            } else {
                // Jika tidak, tampilkan avatar default
                $('#avatar_user').html(
                    `<img src="/assets/media/avatars/blank.png" class="img-fluid" style="width: 100px;">`);
            }
            $('#user_member_id').text(data.user.membership_user.member_id)
            $('#name_membership').html(
                data.membership ?
                (data.membership.name + data.membership_expiry_date) :
                (data.gym_class_bundling ? (data.gym_class_bundling.name + " - " + data.membership_expiry_date) : '')
            );
            $('#name_personal_trainer').html(data.personal_trainer + " - " + data.personal_trainer_packet + data
                .personal_trainer_expired_date)
            // $('#session_membership').text(data.membership.total_session ?? 'Tak Terbatas')
            if (data.membership) {
                $('#session_membership').text(data.membership?.total_session ?? 'Tak Terbatas')
                $('#remaining_session_membership').text(data.membership.total_session ? data.membership.total_session - data
                    .taken_session : 'Tak Terbatas')
            } else {
                $('#session_membership').text(data.gym_class_bundling?.total_session ?? 'Tak Terbatas')
                $('#remaining_session_membership').text(data.gym_class_bundling?.total_session ? data.gym_class_bundling
                    .total_session - data.taken_session : 'Tak Terbatas')
            }
            $('#taken_session_membership').text(data.taken_session ?? 0)
            $('#remaining_session_personal_trainer').text(data.remaining_session_personal_trainer ?? 'Tak Terbatas')

            let table = document.getElementById("table-notes");
            let notes = data.notes;
            let html =
                "<tr>\
                    <th class='text-center'>No</th>\
                    <th class='text-center'>Tanggal</th>\
                    <th class='text-center'>Catatan</th>\
                </tr>";
            let i = 1;
            if (notes.length > 0) {
                notes.forEach(function(item) {
                    html += "<tr>";
                    html += "<td class='text-center' style='width: 5%'>" + (i++) + "</td>";
                    html += "<td class='text-center' style='width: 25%'>" + item.date + "</td>";
                    html += "<td>" + item.description + "</td>";
                    html += "</tr>";
                });
                table.innerHTML = html;
            } else {
                html += "<tr>";
                html += "<td colspan='3' class='text-center'>-- Tidak Ada Data Notes--</td>";
                html += "</tr>";
                table.innerHTML = html;
            }

            $("#modal-confirm-membership").modal("show")
            checkLocker(data.user_activity_id, data.user_id)
            // getLocker(data.user_id, data.user.user_locker?.locker_id)
        }

        function showConfirmGate(data) {
            let actionUrl = '{{ route('gate-activity.checkin-checkout', ':id') }}'.replace(':id', data.user_activity_id);
            $('#form-confirm-membership').attr('action', actionUrl);
            // $('#gym_place_membership').text(data.membership.gym_place.name ?? data.gym_class_bundling.gym_place.name);
            $('#gym_place_membership').text(
                (data.membership?.gym_place?.name) ?? (data.gym_class_bundling?.gym_place?.name) ?? ''
            );
            $('#user_membership').text(data.user.name)
            // Periksa apakah URL avatar pengguna ada
            if (data.user.avatar) {
                // Jika ada, tampilkan avatar pengguna
                $('#avatar_user').html(`<img src="${data.user.avatar}" class="img-fluid" style="width: 100px;">`);
            } else {
                // Jika tidak, tampilkan avatar default
                $('#avatar_user').html(
                    `<img src="/assets/media/avatars/blank.png" class="img-fluid" style="width: 100px;">`);
            }
            $('#user_member_id').text(data.user.membership_user.member_id)
            $('#name_membership').html(
                data.membership ?
                (data.membership.name + data.membership_expiry_date) :
                (data.gym_class_bundling ? (data.gym_class_bundling.name + " - " + data.membership_expiry_date) : '')
            );
            $('#name_personal_trainer').html(data.personal_trainer + " - " + data.personal_trainer_packet + data
                .personal_trainer_expired_date)
            // $('#session_membership').text(data.membership.total_session ?? 'Tak Terbatas')
            if (data.membership) {
                $('#session_membership').text(data.membership?.total_session ?? 'Tak Terbatas')
                $('#remaining_session_membership').text(data.membership.total_session ? data.membership.total_session - data
                    .taken_session : 'Tak Terbatas')
            } else {
                $('#session_membership').text(data.gym_class_bundling?.total_session ?? 'Tak Terbatas')
                $('#remaining_session_membership').text(data.gym_class_bundling?.total_session ? data.gym_class_bundling
                    .total_session - data.taken_session : 'Tak Terbatas')
            }
            $('#taken_session_membership').text(data.taken_session ?? 0)
            $('#remaining_session_personal_trainer').text(data.remaining_session_personal_trainer ?? 'Tak Terbatas')

            let table = document.getElementById("table-notes");
            let notes = data.notes;
            let html =
                "<tr>\
                    <th class='text-center'>No</th>\
                    <th class='text-center'>Tanggal</th>\
                    <th class='text-center'>Catatan</th>\
                </tr>";
            let i = 1;
            if (notes.length > 0) {
                notes.forEach(function(item) {
                    html += "<tr>";
                    html += "<td class='text-center' style='width: 5%'>" + (i++) + "</td>";
                    html += "<td class='text-center' style='width: 25%'>" + item.date + "</td>";
                    html += "<td>" + item.description + "</td>";
                    html += "</tr>";
                });
                table.innerHTML = html;
            } else {
                html += "<tr>";
                html += "<td colspan='3' class='text-center'>-- Tidak Ada Data Notes--</td>";
                html += "</tr>";
                table.innerHTML = html;
            }

            // jika ada data gate
            if (data.gates && data.gates.length > 0){
                $('#name_membership').text(data.gate_membership.name);
                $('#gym_place_membership').text(data.gate_membership.gym_place);

                let tableGate = document.getElementById("table-gates");
                let gates = data.gates;
                let htmlGate =
                    "<tr>\
                        <th class='text-center'>Gate</th>\
                        <th class='text-center'>Tanggal</th>\
                        <th class='text-center'>Aktifitas</th>\
                        <th class='text-center'>status</th>\
                    </tr>";
                if (gates.length > 0) {
                    gates.forEach(function(item) {
                        htmlGate += "<tr>";
                        htmlGate += "<td class='text-center' style='width: 10%'>" + item.gate + "</td>";
                        htmlGate += "<td class='text-center' style='width: 25%'>" + item.activity_at + "</td>";
                        htmlGate += "<td>" + item.activity + "</td>";
                        htmlGate += "<td>" + item.status + "</td>";
                        htmlGate += "</tr>";
                    });
                    tableGate.innerHTML = htmlGate;
                } else {
                    htmlGate += "<tr>";
                    htmlGate += "<td colspan='4' class='text-center'>-- Tidak Ada Data Notes--</td>";
                    htmlGate += "</tr>";
                    tableGate.innerHTML = htmlGate;
                }

                $("#modal-confirm-membership").modal("show");
                $('#gates').removeClass('d-none');

                // locker
                if (data.user_locker) {
                    checkLocker(data.user_activity_id, data.user_id)
                } else {
                    $('#locker_id').show()
                    $('#info-locker').text(' ')
                    getLocker(data.user_id)
                }
                
                $('#action-confirm-membership').removeClass('d-none');
            }

        }

        function showGymClass(data) {
            $('#gym_place_gym_class').text(data.gym_class.gym_place.name)
            $('#user_gym_class').text(data.user.name)
            $('#date_gym_class').text(data.date);
            // Periksa apakah URL avatar pengguna ada
            if (data.user.avatar) {
                // Jika ada, tampilkan avatar pengguna
                $('#avatar_user_gym_class').html(`<img src="${data.user.avatar}" class="img-fluid" style="width: 100px;">`);
            } else {
                // Jika tidak, tampilkan avatar default
                $('#avatar_user_gym_class').html(
                    `<img src="/assets/media/avatars/blank.png" class="img-fluid" style="width: 100px;">`);
            }
            $('#user_member_id_gym_class').text(data.user.membership_user.member_id)
            $('#name_gym_class').html(data.gym_class.name)
            $('#name_personal_trainer_gym_class').html(data.gym_class.personal_trainer_name)

            $("#modal-show-gym-class").modal("show")
        }

        // check apakah punya loker yang sudah diambil jika ada tampilkan loker dengan return
        function checkLocker(user_activity_id, user_id = '') {
            axios.get("{{ route('locker.check-user') }}", {
                    params: {
                        user_activity_id: user_activity_id
                    }
                })
                .then(function(response) {
                    if (response.data.is_user_activity_exist) {
                        // hide select locker
                        $('#locker_id').hide()
                        $('#additional_facility').hide()

                        // check apakah ada data locker
                        // $('#action-confirm-membership').removeClass('d-none');
                        if (response.data.locker) {
                            $('#info-locker').text(response.data.locker.locker.name)
                        } else {
                            $('#info-locker').text('Tanpa Loker')
                        }
                        // $('#info-locker').text(response.data.locker.locker.name)
                    } else {
                        $('#locker_id').show()
                        $('#info-locker').text(' ')
                        getLocker(user_id)
                    }
                })
                .catch(function(error) {
                    toastr.error(error.message)
                });
        }

        // get data list locker with request user_id
        function getLocker(user_id, locker_id = null) {
            axios.get("{{ route('locker.search') }}", {
                    params: {
                        user_id: user_id
                    }
                })
                .then(function(response) {
                    if (response.data.length > 0) {
                        let lockers = response.data;
                        let options = '<option value="">Tanpa Loker</option>';
                        for (locker of lockers) {
                            options +=
                                `<option value="${locker.id}" ${locker_id == locker.id ? 'selected' : ''}>${locker.name}</option>`;
                        }
                        $('#locker_id').html(options);
                    } else {
                        toastr.error(response.data)
                    }
                })
                .catch(function(error) {
                    // console.log(error);
                    toastr.error(error.message)
                    refocusQrcode()
                });
        }

        // check apakah punya loker yang sudah diambil jika ada tampilkan loker dengan return
        function checkLocker(user_activity_id, user_id = '') {
            axios.get("{{ route('locker.check-user') }}", {
                    params: {
                        user_activity_id: user_activity_id
                    }
                })
                .then(function(response) {
                    if (response.data.is_user_activity_exist) {
                        // hide select locker
                        $('#locker_id').hide()
                        // check apakah ada data locker
                        // $('#action-confirm-membership').removeClass('d-none');
                        if (response.data.locker) {
                            $('#info-locker').text(response.data.locker.locker.name)
                        } else {
                            $('#info-locker').text('Tanpa Loker')
                        }
                        // $('#info-locker').text(response.data.locker.locker.name)
                    } else {
                        $('#locker_id').show()
                        $('#info-locker').text(' ')
                        getLocker(user_id)
                    }
                })
                .catch(function(error) {
                    toastr.error(error.message)
                });
        }

        function showDetail(id) {
            let action = "{{ route('qrcode-scan.show', ':id') }}".replace(':id', id);
            axios.get(action)
                .then(function(response) {
                    if (response.data.type == 'Membership') {
                        showConfirmMembership(response.data.data);
                        // $('#locker_id').attr('disabled',
                        //     'disabled');
                        $('#membership_check_in_type').text(' ');
                        $('#action-confirm-membership').addClass('d-none')
                    } else if (response.data.type == 'GymClass') {
                        showGymClass(response.data.data);
                    } else if (response.data.type == 'Gate') {
                        showConfirmGate(response.data.data);
                    }
                    // console.log(response.data);
                })
                .catch(function(error) {
                    console.error(error);
                    toastr.error(error.message)
                    refocusQrcode()
                });
        }
    </script>
@endpush
