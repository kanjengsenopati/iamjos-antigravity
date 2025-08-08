@extends('layouts.offline', ['title' => 'Scan Qrcode', 'main' => 'Dashboard'])
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
                                        <input type="text" class="form-control" id="qrcode" onchange="offlineCheck()"
                                            autofocus>
                                    </div>

                                    <div class="col-auto">
                                        <button class="btn btn-sm btn-success" onchange="offlineCheck()">Proses</button>
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
                    <!-- HTML -->
                    <div id="cardCheckinTableOffline" class="d-none">
                        <h4>Riwayat Checkin Membership Offline</h4>
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
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->

    <div class="modal fade" id="modal-confirm-membership-offline" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-cs-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="membership_check_in_type_offline"></h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Membership Detail</h5>
                            <table class="table table-striped table-bordered">
                                <tbody>

                                    <tr>
                                        <td>Membership ID</td>
                                        <td id="member_id_detail"></td>
                                    </tr>
                                    {{-- <tr>
                                        <td>Locker</td>
                                        <td>
                                            <select class="form-select" name="locker_id" id="locker_id"
                                                data-placeholder="Pilih Loker">
                                                <option value="">--Pilih Loker--</option>
                                            </select>
                                        </td>
                                    </tr> --}}
                                </tbody>
                                <input type="text" hidden id="member_id_input">
                                {{-- <input type="text" hidden id="gym_place_name_input">
                                <input type="text" hidden id="user_name_input"> --}}
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="action-confirm-membership">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import"
                        onclick="confirmCheckinOffline()">
                        Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('js')
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/html5-qrcode@2.0.9/dist/html5-qrcode.min.js"></script>
    <script>
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
            offlineCheck(qr_code)
        }


        function offlineCheck(qrCode = null) {

            // Ambil nilai QR code yang diinputkan dari elemen input
            const qr_code = qrCode ?? $('#qrcode').val();
            // const data = JSON.parse(atob(qr_code));

            const checkinHistories = JSON.parse(localStorage.getItem('checkinHistories')) || [];
            const checkinExist = checkinHistories.find(memberHistory => memberHistory.member_id === qr_code);


            // Tentukan jenis aktivitas check-in
            if (checkinExist) {
                // getLocker(checkinExist.locker_name)
                $('#membership_check_in_type_offline').text('Checkout Membership');
            } else {
                // getLocker()
                $('#membership_check_in_type_offline').text('Checkin Membership');
            }
            $('#member_id_input').val(qr_code)
            $('#member_id_detail').text(qr_code)
            $('#modal-confirm-membership-offline').modal('show');

            // Kosongkan input QR code
            $('#qrcode').val('');
        }

        function getLocker(lockerName = '') {
            let datas = localStorage.getItem('lockers');
            let lockers = JSON.parse(datas);
            console.log(lockers)
            let options = '<option value="">Tanpa Loker</option>';
            for (locker of Object.values(lockers ?? [])) {
                options +=
                    `<option data-name="${locker}" value="${locker}" ${lockerName == locker ? 'selected' : ''}>${locker}</option>`;
            }
            $('#locker_id').html(options)
        }


        function confirmCheckinOffline() {
            let member_id = $('#member_id_input').val(); // Ambil member_id dari modal
            let currentDateTime = new Date();
            // let lockerName = $('#locker_id').val();
            let data = {
                member_id: member_id,
                checkin: currentDateTime,
                checkout: null,
                // locker_name: lockerName,
            };

            // Ambil data check-in dari local storage dan parsing menjadi array
            let checkinHistories = JSON.parse(localStorage.getItem('checkinHistories')) || [];

            const checkinExist = checkinHistories.find(memberHistory => memberHistory.member_id === member_id);
            if (checkinExist) {
                // Update data check-out dengan tanggal sekarang
                checkinExist.checkout = currentDateTime;
                toastr.success('Checkout Berhasil');
            } else {
                // Tambahkan data baru ke array
                checkinHistories.push(data);
                toastr.success('Checkin Berhasil');
            }

            // Simpan array yang telah diperbarui kembali ke local storage
            localStorage.setItem('checkinHistories', JSON.stringify(checkinHistories));

            // Reset form dan tutup modal
            $('#member_id_input').val('');
            $('#modal-confirm-membership-offline').modal('toggle');
            displayOfflineData()
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
                    displayOfflineData()
                },
                error: function(error) {
                    $('#cardCheckinTableOffline').removeClass('d-none');
                }
            })
        }

        displayOfflineData();

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
    </script>
    <script>
        $(document).ready(function() {
            const onlineElements = $('.online-only');
            onlineElements.removeClass('d-none');

            function updateOnlineStatus() {
                if (navigator.onLine) {
                    // Lakukan permintaan jaringan untuk memastikan konektivitas
                    checkConnection().then(isOnline => {
                        if (isOnline) {
                            localStorage.is_online = 1;
                            console.log('You are online.');

                            // return window.location = '/qrcode-scan'
                        } else {
                            localStorage.is_online = 0;
                            console.log('You are offline.');
                        }
                    });
                } else {
                    localStorage.is_online = 0;
                    console.log('You are offline.');
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
    <script>
        $(document).ready(function() {
            const onlineElements = $('.online-only');
            onlineElements.removeClass('d-none');

            function updateOnlineStatus() {
                if (navigator.onLine) {
                    // Lakukan permintaan jaringan untuk memastikan konektivitas
                    checkConnection().then(isOnline => {
                        if (isOnline) {
                            localStorage.is_online = 1;
                            console.log('You are online.');
                            // return window.location = '/qrcode-scan'
                            $('#cardCheckinTableOffline').removeClass('d-none');
                        } else {
                            $('#cardCheckinTableOffline').addClass('d-none');
                            localStorage.is_online = 0;
                            console.log('You are offline.');
                        }
                    });
                } else {
                    localStorage.is_online = 0;
                    console.log('You are offline.');
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
