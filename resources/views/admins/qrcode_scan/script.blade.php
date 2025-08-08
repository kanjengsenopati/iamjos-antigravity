@push('js')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/locale/id.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(function() {
            var start = moment();
            var end = moment();
            function cb(start, end) {
                $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
                var start = start.format('YYYY-MM-DD');
                var end = end.format('YYYY-MM-DD');
                $('#start_date').val(start);
                $('#end_date').val(end);
                table();
            }

            $('#dateRange').daterangepicker({ 
                startDate: start,
                endDate: end,
                ranges: {
                    'Semua Waktu': [moment().subtract(5, 'years'), moment()],
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
            }, cb);
            cb(start, end);
        });
    </script>
    <script>
        // add function addCheckIn on click show modal add checkin user
        function addCheckIn() {
            // get all user where not checkin
            axios.get("{{ route('get-user-not-checkin') }}")
                .then((response) => {
                    let users = response.data.data;
                    let userSelect = $('#user_id');
                    console.log()
                    // Hapus semua opsi yang ada
                    userSelect.empty();
                    
                    // Tambahkan opsi default
                    userSelect.append('<option value="">--Pilih User--</option>');
                    
                    // Tambahkan opsi baru dari data
                    users.forEach(user => {
                        // Tambahkan pemeriksaan null/undefined untuk membership_user
                        const membershipId = user.membership_user?.member_id || 'Tidak Ada ID';
                        userSelect.append(`<option value="${user.id}">${user.name} - ${membershipId}</option>`);
                    });
                    
                    // Refresh select2 untuk memperbarui tampilan
                    userSelect.select2('destroy').select2({
                        placeholder: "--Pilih User--",
                        allowClear: true,
                        width: '100%',
                        dropdownParent: $('#modal-add-checkin-user')
                    });
                })
                .catch((error) => {
                    console.error(error);
                    toastr.error('Gagal memuat data pengguna');
                });

            $('#modal-add-checkin-user').modal('show')
        }

        function changeUserTypeCheckin(value) {
            if (value == 'member') {
                $('#user-checkin').removeClass('d-none');
                $('#guest-checkin').addClass('d-none');
                $('#custom-modal-user-checkin').removeClass('modal-xl');
                $('#form-add-checkin-user').attr('action', '{{ route('add-checkin-user') }}');

                $('#user_id').attr('required', '')
                $('#check_in').attr('required', '')

                $('#guest_name').removeAttr('required')
                $('#guest_phone').removeAttr('required')
                $('#guest_gender').removeAttr('required')
                $('#guest_check_in').removeAttr('required')
            } else if (value == 'guest') {
                $('#user-checkin').addClass('d-none');
                $('#guest-checkin').removeClass('d-none');
                $('#custom-modal-user-checkin').addClass('modal-xl');
                $('#form-add-checkin-user').attr('action', '{{ route('add-checkin-guest') }}');

                $('#user_id').removeAttr('required')
                $('#check_in').removeAttr('required')

                $('#guest_name').attr('required', '')
                $('#guest_phone').attr('required', '')
                $('#guest_gender').attr('required', '')
                $('#guest_check_in').attr('required', '')
            }
        }

        $(document).ready(function() {
            $('#user_id').select2({
                placeholder: "--Pilih User--",
                allowClear: true,
                width: '100%',
                dropdownParent: $('#modal-add-checkin-user')
            });
        });

        // Menggunakan jQuery untuk menangani perubahan pada select2
        $('#user_id').on('change', function() {
            let user_id = $(this).val();
            getLocker(user_id);
        });
        // Menggunakan jQuery untuk menangani perubahan pada select gender
        $('#guest_gender').on('change', function() {
            let gender = $(this).val();
            getLocker(null, null, gender);
        });
    </script>
    <script>
        function importCheckinCheckout() {
            $('#modal-import-checkin-checkout').modal('show')
        }
        var failedImports = <?= json_encode(session('failed_import')) ?>;
        $(document).ready(function() {
            // table();
            if (failedImports?.length > 0) {
                $('#modal-failed-import').modal('show')
            }
            displayOfflineData()
        });

        // $('#dateRange').on('change', function() {
        //     table();
        // })

        function table() {
            var table = $('#datatable').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('qrcode-scan.index') }}",
                    type: 'GET',
                    data: {
                        start_date: $("#start_date").val(),
                        end_date: $("#end_date").val(),
                        gym_place_id: $('#gym_place_id').val()
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
                        data: 'user_name',
                        name: 'user_name',
                        responsivePriority: -2
                    },
                    {
                        data: 'name',
                        name: 'name',
                        sortable: false,
                        searchable: false,
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
                        data: 'checkin_type',
                        name: 'checkin_type',
                        orderable: false,
                        searchable: false
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
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>
    <script>
        //read when do action cancel to refocus for form QR Code
        document.getElementById('cancel-checkin').addEventListener('click', refocusQrcode);
        document.getElementById('cancel-confirm-transaction').addEventListener('click', refocusQrcode);
        document.getElementById('cancel-confirm-shop').addEventListener('click', refocusQrcode);

        // document.getElementById('close-checkin').addEventListener('click', refocusQrcode);

        // Select all elements with the class "btn-close"
        const closeButtons = document.querySelectorAll('.btn-close');

        // Add event listener to each close button
        closeButtons.forEach(closeButton => {
            closeButton.addEventListener('click', refocusQrcode);
        });

        function refocusQrcode() {
            // Get the "qrcode" input element
            var qrcodeInput = document.getElementById('qrcode');

            // Clear the value inside the input element
            qrcodeInput.value = '';

            // Refocus the "qrcode" input element
            qrcodeInput.focus();
        }

        function beep() {
            var snd = new Audio(
                "data:audio/wav;base64,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qJrx4KOQjahZxWbcZgztj2c49nKmkId44S71j0c8eV9yDK6uPRzx5X18eDvjvQ6yKo9ZSS6l//8elePK/Lf//IInrOF/FvDoADYAGBMGb7FtErm5MXMlmPAJQVgWta7Zx2go+8xJ0UiCb8LHHdftWyLJE0QIAIsI+UbXu67dZMjmgDGCGl1H+vpF4NSDckSIkk7Vd+sxEhBQMRU8j/12UIRhzSaUdQ+rQU5kGeFxm+hb1oh6pWWmv3uvmReDl0UnvtapVaIzo1jZbf/pD6ElLqSX+rUmOQNpJFa/r+sa4e/pBlAABoAAAAA3CUgShLdGIxsY7AUABPRrgCABdDuQ5GC7DqPQCgbbJUAoRSUj+NIEig0YfyWUho1VBBBA//uQZB4ABZx5zfMakeAAAAmwAAAAF5F3P0w9GtAAACfAAAAAwLhMDmAYWMgVEG1U0FIGCBgXBXAtfMH10000EEEEEECUBYln03TTTdNBDZopopYvrTTdNa325mImNg3TTPV9q3pmY0xoO6bv3r00y+IDGid/9aaaZTGMuj9mpu9Mpio1dXrr5HERTZSmqU36A3CumzN/9Robv/Xx4v9ijkSRSNLQhAWumap82WRSBUqXStV/YcS+XVLnSS+WLDroqArFkMEsAS+eWmrUzrO0oEmE40RlMZ5+ODIkAyKAGUwZ3mVKmcamcJnMW26MRPgUw6j+LkhyHGVGYjSUUKNpuJUQoOIAyDvEyG8S5yfK6dhZc0Tx1KI/gviKL6qvvFs1+bWtaz58uUNnryq6kt5RzOCkPWlVqVX2a/EEBUdU1KrXLf40GoiiFXK///qpoiDXrOgqDR38JB0bw7SoL+ZB9o1RCkQjQ2CBYZKd/+VJxZRRZlqSkKiws0WFxUyCwsKiMy7hUVFhIaCrNQsKkTIsLivwKKigsj8XYlwt/WKi2N4d//uQRCSAAjURNIHpMZBGYiaQPSYyAAABLAAAAAAAACWAAAAApUF/Mg+0aohSIRobBAsMlO//Kk4soosy1JSFRYWaLC4qZBYWFRGZdwqKiwkNBVmoWFSJkWFxX4FFRQWR+LsS4W/rFRb/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////VEFHAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAU291bmRib3kuZGUAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAMjAwNGh0dHA6Ly93d3cuc291bmRib3kuZGUAAAAAAAAAACU="
            );
            snd.play();
        }

        const html5QrCode = new Html5Qrcode("qr-reader");
        const config = {
            fps: 10,
            qrbox: {
                width: 250,
                height: 250
            }
        };

        const qrCodeSuccessCallback = (decodedText, decodedResult) => {
            beep();
            stopCamera()
            const qr_code = `${decodedText}`;
            checkFromServer(qr_code)
        }

        function checkData(event) {
            console.log('scan in mode online')
            const qr_code = $('#qrcode').val().toLowerCase();
            // const qr_code = $('#qrcode').val();

            // Validate if qr_code is empty
            if (!qr_code.trim()) {
                toastr.error('QR code cannot be empty');
                refocusQrcode();
                return;
            }
            checkFromServer(qr_code)

        }


        function checkFromServer(qr_code) {
            axios.post("{{ route('qrcode-scan.store') }}", {
                    qr_code: qr_code.trim()
                })
                .then(function(response) {
                    if (response.data.success) {
                        toastr.success(response.data.message)
                        if (response.data.type == 'SHOP_PRODUCT') {
                            showConfirmShop(response.data.data, response.data.shop_order)
                        } else if (response.data.type == 'TRANSACTION' || response.data.type == 'USER_TIMEOFF' ||
                            response.data.type == 'ANNUAL_PAYMENT') {
                            showConfirmTransaction(response.data.data)
                        } else if (response.data.type == 'MEMBERSHIP') {
                            showConfirmMembership(response.data.data)
                            $('#action-confirm-membership').removeClass('d-none')
                            if (response.data.activity_type == 'CHECKOUT') {
                                $('#membership_check_in_type').text('Checkout Membership');
                            } else {
                                $('#membership_check_in_type').text('Checkin Membership');
                            }
                        } else if (response.data.type == 'MERCHANDISE') {
                            showConfirmMerchandise(response.data.data, "scan")
                        }
                    } else {
                        toastr.error(response.data.message)
                        scanBackCamera()
                    }
                })
                .catch(function(error) {
                    localStorage.is_online = 0;
                    console.log(error);
                    refocusQrcode()
                    toastr.error(error.message)
                });
        }


        function displayOfflineData() {
            // Ambil data check-in dari local storage dan parsing menjadi array
            let checkinHistories = JSON.parse(localStorage.getItem('checkinHistories')) || [];

            // Kosongkan tabel sebelum diisi ulang
            $('#checkinTableOffline tbody').empty();

            if (checkinHistories.length > 0) {
                $('#cardCheckinTableOffline').removeClass('d-none');
                // Isi tabel dengan data check-in dan check-out
                checkinHistories.forEach(memberHistory => {
                    let row = `<tr>
                    <td>${memberHistory.member_id}</td>
                    <td>${new Date(memberHistory.checkin).toLocaleString()}</td>
                    <td>${memberHistory.checkout ? new Date(memberHistory.checkout).toLocaleString() : ''}</td>
                    </tr>`;
                    $('#checkinTableOffline tbody').append(row);
                });
            } else {
                $('#cardCheckinTableOffline').addClass('d-none');
            }
        }

        function syncCheckinData() {
            let checkinHistories = localStorage.getItem('checkinHistories');
            $('#cardCheckinTableOffline').addClass('d-none');
            $.ajax({
                method: 'POST',
                url: "{{ route('checkin-membership.sync') }}",
                data: {
                    checkin_histories: checkinHistories
                },
                success: function(response) {
                    toastr.success('Data berhasil disinkronisasi');
                    localStorage.removeItem('checkinHistories');
                    table()
                },
                error: function(error) {
                    $('#cardCheckinTableOffline').removeClass('d-none');
                }
            })
        }

        function scanBackCamera() {
            html5QrCode.start({
                facingMode: "environment"
            }, config, qrCodeSuccessCallback);
        }

        function giftPermission() {
            Html5Qrcode.getCameras().then(devices => {
                if (devices && devices.length) {
                    var cameraId = devices[0].id;
                    toastr.success('Permission kamera berhasil dapatkan')
                }
            }).catch(err => {
                toastr.error('Gagal meminta permission kamera')
            });
        }

        function stopCamera() {
            html5QrCode.stop().then((ignore) => {

            }).catch((err) => {

            });
        }

        function showConfirmTransaction(data) {
            let actionUrl = '{{ route('transaction.approve', ':id') }}'.replace(':id', data.id);
            $('#form-confirm-transaction').attr('action', actionUrl);
            $('#user_confirm').text(data.user.name)
            $('#payment_code_confirm').text(data.payment_code)
            $('#payment_method_confirm').text(data.payment_method.name)
            $('#pay_amount_confirm').text('Rp' + data.pay_amount.toLocaleString())
            $('#status_confirm').text(data.status)

            if (data.user_timeoff_history) {
                $('#program_confirm').html(`<ul><li><i>${data.user_timeoff_history.name}</i></li></ul>`);
            } else if (data.annual_payment_history) {
                $('#program_confirm').html(`<ul><li><i>${data.annual_payment_history.name}</i></li></ul>`);
            } else {
                let items = '';
                for (item of data?.transaction_details ?? []) {
                    items += `<li><i>${item.parent?.name}</i></li>`;
                }
                $('#program_confirm').html(`<ul>${items}</ul>`);
            }

            $("#modal-confirm-transaction").modal("show")
        }

        // get data list locker with request user_id
        function getLocker(user_id, locker_id = null, gender = null) {
            let route;
            var gym_place_id = $('#gym_place_id').val();
            if (user_id) {
                route = axios.get("{{ route('locker.search') }}", {
                    params: {
                        user_id: user_id,
                        gym_place_id: gym_place_id
                    }
                });
            } else if (user_id == null && gender) {
                route = axios.get("{{ route('locker.search.guest') }}", {
                    params: {
                        gender: gender,
                        gym_place_id: gym_place_id
                    }
                });
            }
            route.then(function(response) {
                if (response.data.length > 0) {
                    let lockers = response.data;
                    let options = '<option value="">Tanpa Loker</option>';
                    for (locker of lockers) {
                        options +=
                            `<option value="${locker.id}" ${locker_id == locker.id ? 'selected' : ''}>${locker.name}</option>`;
                    }
                    $('#locker_id').html(options);
                    $('#locker_checkin_id').html(options);
                    $('#guest_locker_checkin_id').html(options);
                } else {
                    toastr.error("locker tidak tersedia pada gym ini")
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


        function showConfirmMembership(data) {
            let actionUrl = '{{ route('membership.checkin-checkout', ':id') }}'.replace(':id', data.id);
            $('#info-additional-facility').empty();
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
            $('#user_member_id').text(data.user.membership_user?.member_id)
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

            // Clear any existing content before appending new elements
            $('#info-additional-facility').html('');
            if ($('#info-additional-facility').length) {
                let facilities = [];
                if (data?.is_sauna) {
                    facilities.push('With Sauna');
                }
                if (data?.is_ice_bath) {
                    facilities.push('With Ice Bath');
                }
                $('#info-additional-facility').html(facilities.join('<br>'));
            }
            $("#modal-confirm-membership").modal("show")
            checkLocker(data.user_activity_id, data.user_id)
            // getLocker(data.user_id, data.user.user_locker?.locker_id)
        }

        function showConfirmGate(data) {
            let actionUrl = '{{ route('gate-activity.checkin-checkout', ':id') }}'.replace(':id', data.user_activity_id);
            $('#info-additional-facility').empty();
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
            if (data.gates && data.gates.length > 0) {
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
                
                // locker
                if (data.user_locker) {
                    if (data.check_out === "-") {
                        checkLocker(data.user_activity_id, data.user_id)
                    }
                } else {
                    $('#locker_id').show()
                    $('#info-locker').text(' ')
                    if (data.check_out !== "-") {
                        checkLocker(data.user_activity_id, data.user_id)
                    } else {
                        getLocker(data.user_id)
                    }
                }
                // Cek fasilitas tambahan yang sudah digunakan
                $('#info-additional-facility').html('');
                if ($('#info-additional-facility').length) {
                    let facilities = [];
                    if (data?.is_sauna || data?.is_ice_bath) {
                        if (data?.is_sauna) {
                            facilities.push('With Sauna');
                        }
                        if (data?.is_ice_bath) {
                            facilities.push('With Ice Bath');
                        }
                        $('#additional_facility').hide()
                    } else {
                        if (data.check_out !== "-") {
                            $('#additional_facility').hide()
                        } else {
                            $('#additional_facility').show()
                        }
                    }
                    $('#info-additional-facility').html(facilities.join('<br>'));
                } else if (data.check_out === "-") {
                    $('#additional_facility').hide()
                }
                
                if (data.check_out === "-"){
                    $('#action-confirm-membership').removeClass('d-none');
                } else {
                    $('#action-confirm-membership').addClass('d-none');
                }

                $("#modal-confirm-membership").modal("show");
                $('#gates').removeClass('d-none');
            }
        }


        function showConfirmShop(data, order) {
            let actionUrl = '{{ route('shop-order.approve-pickup', ':id') }}'.replace(':id', data.id);
            $('#form-confirm-shop').attr('action', actionUrl);
            if (data.payment_method.id == '726200b8-a577-407f-b696-af34e10db904') {
                $('#offline_payment_method_shop').hide()
            } else {
                $('#offline_payment_method_shop').show()
            }
            $('#user_shop_confirm').text(data.user.name)
            $('#payment_code_confirm_shop').text(data.payment_code)
            $('#payment_method_confirm_shop').text(data.payment_method.name)
            $('#pay_amount_confirm_shop').text('Rp' + data.pay_amount.toLocaleString())
            $('#status_confirm_shop').text(order.translated_status)
            $("#modal-confirm-shop").modal("show")
            let items = '';
            for (item of order?.shop_order_detail ?? []) {
                items +=
                    `<li><i>${item.product?.name} - ${item.variant ? item.variant?.name + ' - ' : ''}${item.quantity}Pcs</i></li>`;
            }
            $('#program_confirm_shop').html(`<ul>${items}</ul>`);
            if (order.status == 'PENDING') {
                $('#btn-confirm-shop').text('Konfirmasi Pembayaran & Pengambilan Barang')
            } else {
                $('#btn-confirm-shop').text('Konfirmasi Pengambilan Barang')
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

        function showConfirmMerchandise(data, status) {
            let actionUrl = '{{ route('merchandise-claim.approve', ':id') }}'.replace(':id', data.id);
            $('#form-confirm-merchandise').attr('action', actionUrl);

            $('#merchandise_user').text(data.user?.name);
            $('#merchandise_gym').text(data.gym_place?.name);
            $('#merchandise_qrcode').text(data.qr_code);
            $('#merchandise_description').text(data.description);

            let table = document.getElementById("table-merchandise-list");
            let merchandise = data.list_merchandise;
            let html =
                "<tr>\
                            <th class='text-center'>No</th>\
                            <th class='text-center'>Images</th>\
                            <th class='text-center'>Description</th>\
                        </tr>";
            let i = 1;
            if (merchandise.length > 0) {
                merchandise.forEach(function(item) {
                    html += "<tr>";
                    html += "<td class='text-center' style='width: 5%'>" + (i++) + "</td>";
                    html += "<td class='text-center' style='width: 25%'>" +
                        `<img src="${item.image}" class="img-fluid" style="width: 100px;">` + "</td>";
                    html += "<td>" + item.merchandise + "</td>";
                    html += "</tr>";
                });
                table.innerHTML = html;
            } else {
                html += "<tr>";
                html += "<td colspan='3' class='text-center'>-- Tidak Ada Data Merchandise--</td>";
                html += "</tr>";
                table.innerHTML = html;
            }

            if (status == 'detail') {
                $('#submit-merchandise').addClass('d-none')
            } else {
                $('#submit-merchandise').removeClass('d-none')
            }

            $("#modal-confirm-merchandise").modal("show")
        }

        function showDetail(id) {
            let action = "{{ route('qrcode-scan.show', ':id') }}".replace(':id', id);
            axios.get(action)
                .then(function(response) {
                    if (response.data.type == 'Membership') {
                        showConfirmMembership(response.data.data);
                        // $('#locker_id').attr('disabled',
                        //     'disabled');
                        $('#membership_check_in_type').text('Membership Check In - Out');
                        $('#action-confirm-membership').addClass('d-none')
                    } else if (response.data.type == 'GymClass') {
                        showGymClass(response.data.data);
                    } else if (response.data.type == 'Merchandise') {
                        showConfirmMerchandise(response.data.data, "detail");
                    } else if (response.data.type == 'Gate') {
                        $('#membership_check_in_type').text('Membership Check In - Out');
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
    <script>
        $(document).ready(function() {
            function updateOnlineStatus() {
                const onlineElements = $('.online-only');
                if (navigator.onLine) {
                    // Lakukan permintaan jaringan untuk memastikan konektivitas
                    checkConnection().then(isOnline => {
                        if (isOnline) {
                            onlineElements.removeClass('d-none');
                            localStorage.is_online = 1;
                            console.log('You are online.');
                        } else {
                            onlineElements.addClass('d-none');
                            localStorage.is_online = 0;
                            console.log('You are offline.');
                            return window.location = '/offline-scan'
                        }
                    });
                } else {
                    onlineElements.addClass('d-none');
                    localStorage.is_online = 0;
                    console.log('You are offline.');
                    return window.location = '/offline-scan'
                }
            }

            function checkConnection() {
                return new Promise((resolve) => {
                    const xhr = new XMLHttpRequest();
                    xhr.timeout = 5000; // Timeout setelah 5 detik
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState === XMLHttpRequest.DONE) {
                            if (xhr.status >= 200 && xhr.status < 300) {
                                resolve(true);
                            } else {
                                resolve(false);
                            }
                        }
                    };
                    xhr.onerror = function() {
                        resolve(false);
                    };
                    xhr.ontimeout = function() {
                        resolve(false);
                    };
                    xhr.open("GET", "/", true);
                    xhr.send();
                });
            }

            // Periksa status jaringan saat ini
            updateOnlineStatus();

            // Tambahkan event listener untuk perubahan status jaringan
            window.addEventListener('online', updateOnlineStatus);
            window.addEventListener('offline', updateOnlineStatus);
        });
    </script>
@endpush
