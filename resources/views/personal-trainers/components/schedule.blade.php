@push('css')
<style>
    #loading_wrap {
        height: 15rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .is_booking::before {
        content: attr(data-waiting-count);
        position: absolute;
        background-color: #D83C15;
        border-radius: 100%;
        width: 1.375rem;
        height: 1.375rem;
        bottom: 10px;
        right: -5px;
        font-size: 12px;
        color: #fff !important;
    }
    .is_booking {
        position: relative;
    }
</style>
@endpush
<div class="row mt-6">
    <div class="col-12">
        <div class="card">
            <div class="card-header align-items-center">
                <h1 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Kalendar </h1>
            </div>
            <div class="card-body">
                <ul id="wrap_date"
                    class="nav nav-pills d-flex flex-nowrap hover-scroll-x py-2 mb-3 w-100 justify-content-evenly">
                </ul>
                <ul class="row nav nav-pills d-flex flex-nowrap gap-6 me-4 mb-8 mb-sm-0 h-45px mt-8">
                    {{-- <li class="nav-item col-6 text-center"> --}}
                        <a class="nav-item col-6 text-center tab text-nowrap fs-4 active" data-bs-toggle="tab"
                            href="#kt_session">
                            Sesi
                        </a>
                        {{--
                    </li> --}}
                    {{-- <li class="nav-item col-6 text-center"> --}}
                        <a class="nav-item col-6 text-center tab text-nowrap fs-4" data-bs-toggle="tab"
                            href="#kt_class">
                            Kelas
                        </a>
                        {{--
                    </li> --}}
                </ul>
                <div class="tab-content mt-8">
                    <div id="kt_session" class="tab-pane show active fade">
                    </div>
                    <div id="kt_class" class="tab-pane fade">
                    </div>
                    <div id="loading_wrap">
                        <div class="d-flex flex-column gap-5 justify-content-center align-items-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <h6>Loading...</h6>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- modal detail kelas --}}
<div class="modal fade" id="kt_modal_detail_class" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal body-->
            <div class="modal-body px-8">
                <div class="d-flex justify-content-end">
                    <!--begin::Close-->
                    <div class="cursor-pointer btn-icon btn-active-color-primary close_modal_class"
                        data-bs-dismiss="modal">
                        <img src="{{asset('assets/media/icons/close.svg')}}" alt="">
                    </div>
                    <!--end::Close-->
                </div>
                <!--begin::Row-->
                <div class="d-flex">
                    <div class="mb-4">
                        <!--begin::Event name-->
                        <div class="d-flex align-items-center mb-2">
                            <span id="name_class" class="fs-4 fw-bold me-3"></span>
                            <span class="status violet">KELAS</span>
                        </div>
                        <!--end::Event name-->
                    </div>
                </div>
                <div class="d-flex flex-column align-items-center justify-content-center">
                    <div class="qr-code position-relative">
                        <img class="img_qr" alt="Qr Code Kelas" id="qr_code_class" src="" />
                    </div>
                    <div class="text-center mt-4">
                        <p class="badge badge-light-success gray-10">Scan</p>
                    </div>
                </div>
                <div class="d-flex gap-4 align-items-center mb-2">
                    <!--begin::Bullet-->
                    <img src="{{asset('assets/media/icons/calendar1.svg')}}" alt="">
                    <!--end::Bullet-->
                    <!--begin::Event start date/time-->
                    <div class="fs-6">
                        <div class="d-flex align-items-center gap-2">
                            <span id="date_class"></span>
                            <span class="mb-2">.</span>
                            <span id="time_class"></span>
                        </div>
                    </div>
                    <!--end::Event start date/time-->
                </div>
                <!--begin::Row-->
                <div class="d-flex gap-4 align-items-center mb-2">
                    <!--begin::Bullet-->
                    <img src="{{asset('assets/media/icons/barbel.svg')}}" alt="">
                    <!--end::Bullet-->
                    <!--begin::Event end date/time-->
                    <div class="fs-6">
                        <span id="level_class" data-kt-calendar="event_end_date"></span>
                    </div>
                    <!--end::Event end date/time-->
                </div>
                <!--end::Row-->
                <!--begin::Row-->
                <div class="d-flex gap-4 align-items-start mb-2">
                    <img src="{{asset('assets/media/icons/down-right.svg')}}" alt="">
                    <!--begin::Event location-->
                    <div id="description_class" class="fs-6" data-kt-calendar="event_location"></div>
                    <!--end::Event location-->
                </div>
                <!--end::Row-->

                <div id="member_class_handler"
                    class="d-flex gap-4 align-items-center mb-2 cursor-pointer member-class-handler">
                    <img class="toggle" src="{{ asset('assets/media/icons/CaretDownGold.svg') }}" alt="toggle">
                    <!--begin::Event location-->
                    <div class="fs-6" id="total_member_class"></div>
                    <!--end::Event location-->
                </div>
                <div id="members_class" class="row px-6">
                </div>
                <!--end::Row-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
{{-- end modal detail kelas --}}

{{-- modal detail PT --}}
<div class="modal fade" id="kt_modal_detail_activity_pt" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-500px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal body-->
            <div class="modal-body px-8">
                <div class="d-flex justify-content-end">
                    <!--begin::Close-->
                    <div class="cursor-pointer btn-icon btn-active-color-primary close_modal_pt"
                        data-bs-dismiss="modal">
                        <img src="{{asset('assets/media/icons/close.svg')}}" alt="">
                    </div>
                    <!--end::Close-->
                </div>
                <!--begin::Row-->
                <div class="d-flex">
                    <div class="mb-4">
                        <!--begin::Event name-->
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span id="name_pt" class="fs-4 fw-bold me-3"></span>
                            <span class="status green">PERSONAL TRAINER</span>
                        </div>
                        <!--end::Event name-->
                    </div>
                </div>
                <div class="d-flex gap-4 align-items-center mb-2">
                    <!--begin::Bullet-->
                    <img src="{{asset('assets/media/icons/calendar1.svg')}}" alt="">
                    <!--end::Bullet-->
                    <!--begin::Event start date/time-->
                    <div class="fs-6">
                        <div class="d-flex align-items-center gap-2">
                            <span id="date_pt"></span>
                            <span class="mb-2">.</span>
                            <span id="time_pt"></span>
                        </div>
                    </div>
                    <!--end::Event start date/time-->
                </div>
                <div id="member_pt_handler"
                    class="d-flex gap-4 align-items-center mb-2 cursor-pointer member-class-handler">
                    <img src="{{ asset('assets/media/icons/CaretDownGold.svg') }}" alt="toggle">
                    <!--begin::Event location-->
                    <div class="fs-6" id="total_member_pt"></div>
                    <!--end::Event location-->
                </div>
                <div id="members_pt" class="row px-6">

                </div>
                
                <!--end::Row-->
            </div>
            <!--end::Modal body-->
            
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
{{-- end modal detail PT --}}
@push('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.30.1/locale/id.min.js"
    integrity="sha512-he8U4ic6kf3kustvJfiERUpojM8barHoz0WYpAUDWQVn61efpm3aVAD8RWL8OloaDDzMZ1gZiubF9OSdYBqHfQ=="
    crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<!--end::Vendors Javascript-->
<!--begin::Custom Javascript(used for this page only)-->
<script src="{{ asset('assets/js/custom/utilities/modals/upgrade-plan.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/create-app.js') }}"></script>
<script src="{{ asset('assets/js/custom/utilities/modals/users-search.js') }}"></script>

<script>
    $(document).ready(function() {
        let sessions = [];
        let classes = [];

        const itemDate = (date, status) => {
            const dDay = moment().format('YYYY-MM-DD');
            const data = date.split('|');
            const id = data[1];
            const item = data[0].split(' ');
            const isActive = dDay === id ? 'active' : '';
            const isWaiting = status === 'Menunggu' ? 'is_booking' : '';

            return `<li class="nav-item me-1 date-item" data-date="${id}">
                        <a class="nav-link btn d-flex flex-column flex-center rounded-pill min-w-45px me-2 py-4 px-3 btn-active-primary ${isActive} ${isWaiting}" data-bs-toggle="tab" href="#kt_schedule_day_0">
                            <span class="day fs-6 fw-400">${item[0]}</span>
                            <span class="fs-3 fw-bold">${item[1]}</span>
                        </a>
                    </li>`;
        }

        const checkIfDateHasWaitingSessions = (date) => {
            if (!sessions.length) return false;

            const sessionsOnDate = sessions.filter(session => session.date === date && session.status === 'Menunggu');
            return sessionsOnDate.length > 0;
        }

        const generateDateList = () => {
            var dateList = [];
            var currentDate = moment().locale('id');

            for (var i = 0; i < 8; i++) {
                const formattedDate = currentDate.format('ddd DD|YYYY-MM-DD');
                const isWaiting = checkIfDateHasWaitingSessions(formattedDate.split('|')[1]) ? 'is_booking' : '';
                dateList.push(itemDate(formattedDate, isWaiting)); // Mengirimkan status isWaiting ke dalam itemDate
                currentDate.add(1, 'day');
            }

            $('#wrap_date').append(dateList);
        }

        const updateDateStatus = () => {
            $('.date-item').each(function() {
                const date = $(this).data('date');
                const isWaiting = checkIfDateHasWaitingSessions(date) ? 'is_booking' : '';
                $(this).find('a').addClass(isWaiting); // Menambahkan kelas is_booking jika ada booking yang menunggu
                const waitingCount = sessions.filter(session => session.date === date && session.status === 'Menunggu').length;
                $(this).find('a').attr('data-waiting-count', waitingCount); // Menambahkan data attribute untuk menampilkan jumlah booking yang menunggu
            });
        }

        // Memanggil generateDateList dan updateDateStatus
        generateDateList();
        updateDateStatus();


        // on load page load data sesi personal trainer dan kelas
        $.ajax({
            url: "{{ route('personal-trainer.dashboard') }}",
            type: "GET",
            success: function(response) {
                $('#loading_wrap').addClass('d-none');
                sessions = Object.values(response.sessions).map((item, idx) => {return {...item, id: `${idx}-sesi`}});
                classes = Object.values(response.classSchedule).map((item, idx) => {return {...item, id: `${idx}-kelas`}});
                filterDataSchedule(moment().format('YYYY-MM-DD'));
                updateDateStatus(); // panggil updateDateStatus setelah data diterima
            }
        });

        const generateBgStatus = (status) => {
            let bgStatus = 'bg-red';
            if(status === 'Selesai') bgStatus = 'bg-green';
            else if(status === 'Menunggu') bgStatus = 'bg-orange';
            else if(status === 'Sedang Berlangsung') bgStatus = 'bg-blue';
            
            return bgStatus;
        }

        const generateElementSession = (data) => {
            $('#kt_session').empty();

            let sessionElement = '';
            data.forEach(item => {
                const names = [];
                item.members.forEach(member => {
                    names.push(member);
                });

                sessionElement += `
                    <div data-id="${item.id}" data-bs-toggle="modal" data-bs-target="#kt_modal_detail_activity_pt" class="bg-dark green card p-6 mb-6 schedule_pt">
                        <div class="d-flex justify-content-between align-items-cente mb-3">
                            <div class="d-flex align-items-center gap-4">
                                <div class="type d-flex justify-content-center align-items-center bg-green100 border-radius-xxxl p-1">
                                    <img src="{{asset('assets/media/icons/PersonArmsSpread.svg')}}" alt="">
                                </div>
                                <p class="mb-0 green fw-500">Personal Trainer</p>
                            </div>
                            <div class="${generateBgStatus(item.status)} fs-8 fs-sm-7 px-4 py-2 fw-500 text-white border-radius-xxl">${item.status}</div>
                        </div>
                        <p fw-400 mb-1 fs-6">${item.start_time} - ${item.end_time}</p>
                        <h1 class="fs-6">Session with ${names.join(', ')}</h1>
                    </div>`;
            });
            
            $('#kt_session').append(sessionElement);
        }

        const generateElementClass = (data) => {
            $('#kt_class').empty();

            let classElement = '';
            data.forEach(item => {
                const names = [];
                item.members.forEach(member => {
                    names.push(member);
                });
               
                classElement += `
                    <div data-id="${item.id}" data-bs-toggle="modal" data-bs-target="#kt_modal_detail_class" class="bg-dark purple card p-6 mb-6 schedule_class">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-4">
                                <div class="type d-flex justify-content-center align-items-center bg-purple100 border-radius-xxxl p-1">
                                    <img src="{{asset('assets/media/icons/PersonSimpleRun.svg')}}" alt="">
                                </div>
                                <p class="mb-0 violet fw-500">Kelas</p>
                            </div>
                            <div class="${generateBgStatus(item.status)} fs-8 fs-sm-7 px-4 py-2 fw-500 text-white border-radius-xxl">${item.status}</div>
                        </div>
                        <p fw-400 mb-1 fs-6">${item.start_time} - ${item.end_time}</p>
                        <h1 class="fs-6">${item.name}</h1>
                    </div>`;
            });
            $('#kt_class').append(classElement);
        }

        // filter riwayat aktivitas by date
        $(document).on('click', '.date-item', function() {
            const date = $(this).data('date');
            filterDataSchedule(date);
        });

        const noneElement = `<div class="my-15 d-flex flex-column gap-4 justify-content-center align-items-center">
                        <div class="wrap-icon d-flex justify-content-center align-items-center">
                            <img src="{{asset('assets/media/icons/Empty-folder.svg')}}" alt="empty">
                        </div>
                        <p class="mb-0 fs-4">Data tidak ditemukan</p>
                        <p class="mb-0">Kami tidak dapat menemukan data yang anda cari</p>
                    </div>`;

        const filterDataSchedule = (date) => {
            const resultSession = sessions.filter(item => item.date === date);
            const resultClass = classes.filter(item => item.date === date);

            if (resultSession.length === 0) {
                $('#kt_session').empty();
                $('#kt_session').append(noneElement);
            } else {
                generateElementSession(resultSession);
            }

            if (resultClass.length === 0) {
                $('#kt_class').empty();
                $('#kt_class').append(noneElement);
            } else {
                generateElementClass(resultClass);
            }
        };

        // generate modal detail data session
        $(document).on('click', '.schedule_pt', function() {
            $('#members_pt').empty();
            const id = $(this).data('id');
            
            const data = sessions.find(item => item.id === id);
            const names = [];
            data.members.forEach(member => {
                names.push(member);
            });
            $('#name_pt').text(`Session with ${names.join(', ')}`);
            $('#date_pt').text(moment(data.date).format('dddd, DD MMMM YYYY'));
            $('#time_pt').text(`${data.start_time} - ${data.end_time}`);
            $('#total_member_pt').text(`${data.total_members} Peserta`);

            let el_member = '';
            data.members.forEach(member => {
                el_member += `<div class="col-lg-4 col-6 ps-0">
                        <ul class="mb-2">
                            <li>${member}</li>
                        </ul>
                    </div>`;
            });

            $('#members_pt').append(el_member);
        });

        // generate modal detail data class
        $(document).on('click', '.schedule_class', function() {
            $('#members_class').empty();
            const id = $(this).data('id');
            
            const data = classes.find(item => item.id === id);
            console.log(data);
            const names = [];
            data.members.forEach(member => {
                names.push(member);
            });
            $('#name_class').text(data.name);
            // generate qr code to image and show in #qr_code_class
            $('#qr_code_class').attr('src', `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${data.qr_code}`);
            $('#date_class').text(moment(data.date).format('dddd, DD MMMM YYYY'));
            $('#time_class').text(`${data.start_time} - ${data.end_time}`);
            $('#level_class').text(data.level);
            $('#description_class').text(data.description);
            $('#total_member_class').text(`${data.total_members} Peserta`);

            let el_member = '';
            data.members.forEach(member => {
                el_member += `<div class="col-lg-4 col-6 ps-0">
                        <ul class="mb-2">
                            <li>${member}</li>
                        </ul>
                    </div>`;
            });

            $('#members_class').append(el_member);
        });
    });
</script>

@endpush