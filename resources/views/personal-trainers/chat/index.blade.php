@extends('layouts.pt-master', ['title' => 'Chat'])
@push('css')
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
    integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />

<style>
    [data-bs-theme="light"] {
        --color-gray-10: #F1F1F2;
        --color-orange: #DAC496;
        --color-gray-6: #423f3f;

        .input-wrap,
        .btn.input-wrap:hover,
        .btn.input-wrap:focus {
            box-shadow: none;
        }
    }

    [data-bs-theme="dark"] {
        --color-gray-10: #262626;
        --color-gray-6: #BFBFBF;
        --color-orange: #B18D41;
    }

    .input-wrap,
    .btn.input-wrap:hover,
    .btn.input-wrap:focus {
        color: #8C8C8C;
        border-radius: 0.625rem !important;
        background: var(--color-gray-10);
        box-shadow: 0px 1px 2px 0px rgba(0, 0, 0, 0.12), 0px 0px 2px 0px rgba(0, 0, 0, 0.12);
    }

    img.search {
        position: absolute;
        left: 1.2rem;
        top: 1rem;
    }

    .input-wrap input,
    .input-wrap input[type=text]:focus {
        border: none;
        outline: none;
        margin-left: 2.2rem;
        background-color: var(--color-gray-10);
        border-radius: 0.625rem;
        color: #8C8C8C;
        font-weight: 400;
    }

    .input-wrap input::placeholder {
        color: #8C8C8C;
    }

    .fc-h-event {
        border: none;
        cursor: pointer;
    }

    .border-radius-xxl {
        border-radius: 1.25rem !important;
    }

    .border-radius-xxxl {
        border-radius: 2rem !important;
    }

    .btn-all {
        color: var(--color-gray-1, #FFF);
        font-size: 0.875rem;
        font-weight: 500;
        line-height: 1.375rem;
        /* 157.143% */
        letter-spacing: -0.00006rem;
        border-radius: var(--radius-xl, 1.25rem);
        background: var(--color-gray-10);
        outline: none;
        border: none;
        padding: 0.5rem 1rem
    }

    .bg-success {
        background-color: #3B61FF !important;
    }

    .bg-primary {
        background-color: #99CD15 !important;
    }

    .bg-main {
        background-color: var(--color-orange) !important;
    }

    h1.title {
        font-size: 1.9375rem !important;
    }

    .fc-button {
        text-transform: capitalize !important;
    }

    .fc .fc-button {
        padding: 0.75rem 1.25rem !important;
        box-shadow: none !important;
        border: 0 !important;
        border-radius: 0.475rem;
        vertical-align: middle;
        font-weight: 500;
        text-transform: capitalize;
    }

    .fc .fc-toolbar-title {
        font-size: 1.3rem !important;
    }

    .status {
        font-size: 0.75rem;
        font-weight: 600;
        line-height: 1.125rem;
        /* 150% */
        border-radius: var(--radius-m, 0.75rem);
        background: var(--color-gray-10);
        padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
        justify-content: center;
        align-items: center;
    }

    .violet {
        color: var(--fuchsia-add-2500, #C366CF);
    }

    .green {
        color: var(--salem-add-1500, #0ABF70);
    }

    .cursor-pointer {
        cursor: pointer;
    }

    .text-grey {
        color: var(--color-gray-6);
    }

    .card {}

    .fw-400 {
        font-weight: 400 !important;
    }

    .text-grey2 {
        color: #8C8C8C !important;
    }

    .bg-gray {
        background-color: var(--color-gray-10);
    }

    .wrap-icon {
        width: 9rem;
        height: 9rem;
        padding: 0.375rem 1rem 1rem 1rem;
        background-color: var(--color-gray-10) !important;
        border-radius: 6.25rem;
    }

    .text-blue {
        color: #2896FF;
    }

    .btn-status {
        font-size: 0.75rem;
        outline: none;
        border: none;
        border-radius: var(--radius-m, 0.75rem);
        padding: var(--spacing-02, 0.25rem) var(--spacing-03, 0.5rem);
    }

    .message {
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: nowrap;
    }

    .max-w-400px {
        max-width: 400px
    }

    .vh-50 {
        height: 50vh !important;
    }

    .vh-45 {
        height: 45vh !important;
    }


    .wrap-icon img {
        width: 7rem;
    }
</style>
@endpush
@section('content')
<div class="content pt pt-5 d-flex flex-column flex-column-fluid" id="kt_content">
    <div class="app-container container-xxl">
        <!--begin::Toolbar-->
        <div class="toolbar" id="kt_toolbar">
            <div id="kt_toolbar_container" class="d-flex flex-stack">
                <!--begin::Page title-->
                <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
                    data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
                    class="page-title d-flex flex-column flex-wrap me-3 mb-5 mb-lg-0">
                    <!--begin::Title-->
                    <h2 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Chat </h2>
                    <p class="text-grey"><span class="text-primary">Home</span> - Chat</p>
                    <!--end::Title-->
                </div>
            </div>
        </div>
        <div class="d-flex flex-column flex-lg-row">
            <!--begin::Sidebar-->
            <div class="flex-column flex-lg-row-auto w-100 w-lg-300px w-xl-400px mb-10 mb-lg-0">
                <!--begin::Contacts-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header pt-7" id="kt_chat_contacts_header">
                        <div class="d-flex w-100 input-wrap align-items-center position-relative my-1">
                            <img class="search" src="{{asset('assets/media/icons/search.svg')}}" alt="">
                            <input type="text" name="search-member" id="search-member" class="form-control"
                                placeholder="Cari member" />
                        </div>
                    </div>
                    <!--end::Card header-->
                    <!--begin::Card body-->
                    <div class="card-body pt-7" id="kt_chat_contacts_body">
                        <!--begin::List-->
                        <div class="scroll-y me-n5 pe-5 h-200px h-lg-auto" data-kt-scroll="true" id="wrap_people"
                            data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
                            data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_toolbar, #kt_app_toolbar, #kt_footer, #kt_app_footer, #kt_chat_contacts_header"
                            data-kt-scroll-wrappers="#kt_content, #kt_app_content" data-kt-scroll-offset="5px">
                            <div id="no_message"
                                class="text-center d-none vh-50 d-flex flex-column align-items-center justify-content-center gap-8">
                                <div class="wrap-icon d-flex justify-content-center align-items-center">
                                    <img src="{{asset('assets/media/icons/Empty-folder.svg')}}" alt="empty">
                                </div>
                                <p>Belum ada pesan</p>
                            </div>

                        </div>
                        <div id="loading_search"
                            class="h-100 d-none my-15 d-flex justify-content-center align-items-center">
                            <h1 class="fs-4 fw-500">Loading...</h1>
                        </div>
                        <div id="member_not_found"
                            class="text-center d-none vh-50 d-flex flex-column align-items-center justify-content-center gap-8">
                            <div class="wrap-icon d-flex justify-content-center align-items-center">
                                <img src="{{asset('assets/media/icons/Empty-folder.svg')}}" alt="empty">
                            </div>
                            <p>Member tidak ditemukan</p>
                        </div>
                        <!--end::List-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Contacts-->
            </div>
            <!--end::Sidebar-->
            <!--begin::Content-->
            <div class="flex-lg-row-fluid ms-lg-7 ms-xl-10">
                @if (!empty($targetRoom))
                <div class="card" id="kt_chat_messenger">
                    <div class="card-header align-items-center">
                        <h1 id="name" class="d-flex text-dark fs-6 align-items-center my-1">
                            {{ $targetRoom->user->name }}
                        </h1>
                    </div>
                    <!--begin::Card body-->
                    <div class="card-body" id="kt_chat_messenger_body">
                        <!--begin::Messages-->
                        <div class="scroll-y me-n5 pe-5 h-300px h-lg-auto" id="chat-scroll" data-kt-element="messages"
                            data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                            data-kt-scroll-max-height="auto"
                            data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_app_toolbar, #kt_toolbar, #kt_footer, #kt_app_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                            data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_messenger_body"
                            data-kt-scroll-offset="5px">
                            <div id="wrap_loading" class="h-100 my-15 d-flex justify-content-center align-items-center">
                                <h1 class="fs-4 fw-500">Loading...</h1>
                            </div>
                            <div class="wrap_message">
                            </div>
                        </div>
                        <!--end::Messages-->
                    </div>
                    <!--end::Card body-->
                    <!--begin::Card footer-->
                    <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                        <form id="form2">
                            <!--begin::Input-->
                            <textarea class="form-control input_message form-control-flush mb-3" rows="1"
                                placeholder="Tulis pesan anda.."></textarea>
                            <!--end::Input-->
                            <!--begin:Toolbar-->
                            <div class="d-flex justify-content-end">
                                <!--begin::Send-->
                                <button class="btn btn-primary border-radius-xxxl send" type="button">Kirim</button>
                                <!--end::Send-->
                            </div>
                            <!--end::Toolbar-->
                        </form>
                    </div>
                    <!--end::Card footer-->
                </div>
                @else
                <div class="card no_chat">
                    <div class="card-body">
                        <div class="my-15 d-flex flex-column gap-4 justify-content-center align-items-center">
                            <div class="wrap-icon d-flex justify-content-center align-items-center">
                                <img src="{{asset('assets/media/icons/Empty-folder.svg')}}" alt="empty">
                            </div>
                            <p class="mb-0">Belum ada chat apapun dengan member</p>
                            <a href="{{route('personal-trainer.membership.index')}}">
                                <button class="btn btn-primary border-radius-xxxl" type="button"
                                    data-kt-element="send">Cari member</button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card d-none chat_canvas">
                    <div class="card-header align-items-center">
                        <h1 id="name" class="d-flex text-dark fs-6 align-items-center my-1"></h1>
                    </div>
                    <!--begin::Card body-->
                    <div class="card-body" id="kt_chat_messenger_body">
                        <!--begin::Messages-->
                        <div class="scroll-y me-n5 pe-5 h-300px h-lg-auto" id="chat-scroll" data-kt-element="messages"
                            data-kt-scroll="true" data-kt-scroll-activate="{default: false, lg: true}"
                            data-kt-scroll-max-height="auto"
                            data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_app_toolbar, #kt_toolbar, #kt_footer, #kt_app_footer, #kt_chat_messenger_header, #kt_chat_messenger_footer"
                            data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_messenger_body"
                            data-kt-scroll-offset="5px">
                            <div id="wrap_loading" class="h-100 my-15 d-flex justify-content-center align-items-center">
                                <h1 class="fs-4 fw-500">Loading...</h1>
                            </div>
                            <div class="wrap_message">
                            </div>
                        </div>
                        <!--end::Messages-->
                    </div>
                    <!--end::Card body-->
                    <!--begin::Card footer-->
                    <div class="card-footer pt-4" id="kt_chat_messenger_footer">
                        <form id="form">
                            <!--begin::Input-->
                            <textarea class="form-control form-control-flush mb-3 input_message" rows="1"
                                placeholder="Tulis pesan anda.."></textarea>
                            <!--end::Input-->
                            <!--begin:Toolbar-->
                            <div class="d-flex justify-content-end">
                                <!--begin::Send-->
                                <button class="btn btn-primary border-radius-xxxl send" type="button">Kirim</button>
                                <!--end::Send-->
                            </div>
                            <!--end::Toolbar-->
                        </form>
                    </div>
                    <!--end::Card footer-->
                </div>
                @endif
            </div>
            <!--end::Content-->
        </div>
    </div>
</div>
<!--end::Container-->
@endsection
@push('js')
<script>
    // Set the locale to 'id' for Indonesian
    moment.locale('id');
</script>
<script src="{{asset('assets/js/widgets.bundle.js')}}"></script>
<script src="{{asset('assets/js/custom/widgets.js')}}"></script>

<script src="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.js')}}"></script>
<script src="{{asset('assets/plugins/custom/datatables/datatables.bundle.js')}}"></script>
<!--end::Vendors Javascript-->
<!--begin::Custom Javascript(used for this page only)-->
<script src="{{asset('assets/js/custom/apps/chat/chat.js')}}"></script>

<script type="module">
    import { io } from "https://cdn.socket.io/4.7.4/socket.io.esm.min.js";
      
        const socket = io("https://socket.nestgymindonesia.com");
            
        $(document).ready(function() {
            const loader = `<div id="loader" class="text-center mb-3">
                                <span class="spinner-border text-primary" role="status"></span>
                            </div>`
            
                            
            // Member
            let delayTimer;
            let pageMember = 1
            let nextPageMember = null
            let memberName = ''
            let isNoMembers = false
            let loadingMember = false

            $('#search-member').on('input', function() {
                $('#member_not_found').addClass('d-none')
                $('#wrap_people').addClass('d-none')
                $('#loading_search').removeClass('d-none')
                
                clearTimeout(delayTimer);
                delayTimer = setTimeout(async function() {
                    memberName = $('#search-member').val()
                    await fetchMember('')
                    $('#wrap_people').empty()
                }, 1500); // 1,5 seconds delay
            }); 

            const fetchMember = async (page = pageMember) => {
                    loadingMember = true
                    // add loader
                    if(page>1) {
                        $('#wrap_people').append(loader)
                    }

                    // Make an AJAX request to your search route
                    $.ajax({
                        url: "{{ route('personal-trainer.search-member-chat') }}",
                        type: "GET",
                        data: { search: memberName, page },
                        success: function({data}) {
                            loadingMember = false
                            pageMember = data.current_page
                            nextPageMember = data.next_page_url

                            // Update the contact list with the fetched data
                            if(data.data.length===0){
                                isNoMembers = true
                                $('#loading_search').addClass('d-none')
                                $('#member_not_found').removeClass('d-none')
                            } else {
                                $('#loader').remove()
                                $('#member_not_found').addClass('d-none')
                                $('#loading_search').addClass('d-none')
                                $('#wrap_people').removeClass('d-none')

                                let result
                                let roomIdsPage1 = []
                                if(page === 2) {
                                    $(".member").each(function(){
                                        roomIdsPage1.push($(this).data('room'))
                                    });
                                } 

                                result = data.data.map(item => !roomIdsPage1.includes(item.name) ? displayMember(item) : false).join(' ')
                                $('#wrap_people').append(result)
                            }
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
            }

            // scroll member
            // detect user scroll chat to bottom
            $('#wrap_people').on('scroll', function() {
                if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
                    console.log('loading');
                    if(!loadingMember&&nextPageMember!==null) fetchMember(pageMember+1)
                }
            })

            const displayMember = (data) => {
                let total_unread = data.total_unread_message > 0 ? `<button class="btn-status bg-gray text-blue unread mt-1 ms-4">${data.total_unread_message}</button>` : ''
                return `<div data-room="${data.name}" class="member cursor-pointer mb-8">
                            <div class="d-flex w-100 align-items-center gap-4">
                                <div class="symbol symbol-45px symbol-md-50px symbol-circle"
                                    data-bs-toggle="tooltip" title="${data.name_chat_with}">
                                    <img alt="Profile Picture"
                                        src="${data.avatar_chat_with ?? 'assets/media/avatars/blank.png'}"" />
                                </div>
                                <div class="d-flex flex-column justify-content-center w-100 overflow-hidden">
                                    <div class="d-flex justify-content-between">
                                        <h1 id="username" class="text-dark fs-7">${data.name_chat_with}</h1>
                                        <p class="text-grey2 text-nowrap fw-400 mb-0 time">${data.time_last_message || ''}</p>
                                    </div>
                                    <div class="d-flex justify-content-between footer_message">
                                        <p class="fw-400 message text-grey mb-0 mt-1">${data.last_message || 'Belum ada pesan'}</p>
                                        ${total_unread}
                                    </div>
                                </div>
                            </div>
                        </div>`
            }

            const getMember = async () => {
                $('#wrap_people').addClass('d-none')
                $('#loading_search').removeClass('d-none')

                await fetchMember(pageMember)

                if(isNoMembers) {
                    $('#wrap_people').removeClass('d-none')
                    $('#no_message').removeClass('d-none')
                }
            }

            getMember()

            // variable member
            let current_page = 1
            let next_page_url = null
            let loading = false

            // Chat
            let resultMessage
            socket.on("chat message", (msg) => {
                // console.log(msg);
                resultMessage = msg
                displayMessage(msg);
            });

            let roomId = '';
            let username = '';
            let sender = '{{ auth('trainer')->user()->name }}';

            // select member
            $(document).on('click', '.member', function(event) {
                if(!loading) {
                    const element = $(this)
    
                    // remove tampilan belum ada pesan
                    const no_chat = $('.no_chat')
                    if(no_chat) {
                        no_chat.addClass('d-none')
                        $('.chat_canvas').removeClass('d-none')
                        $('.wrap_message').empty()
                    }
    
                    username = $(this).find('#username').text()
                    roomId = $(this).data('room')
                    openChat()
                    event.stopPropagation(); // Optional
                }
            });

            $(".input_message").keypress(function (e) {
                // send chat by enter
                if(e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    if($(this).val().trim()!=='') {
                        sendMessage()
                    }
                }
            });
            
            $('.send').on('click', function(e) {
                e.preventDefault()
                if($('.input_message').val().trim()!==''){
                    sendMessage()
                }
            })

            function openChat() {
                socket.emit("join", roomId);
                $('#wrap_message').empty()
                getHistoryChat(roomId);

                $('#name').text(username)
            }

            let senderMessage = false
            async function sendMessage() {
                senderMessage = true
                const msg = $('.input_message').val();
                $('.send').attr("disabled", true)
                const chatMessage = {
                    roomId,
                    sender,
                    message: msg,
                    created_at: moment().format('YYYY-MM-DD HH:mm:ss')
                };
                
                socket.emit("chat message", chatMessage);
                
                try {
                    $('.input_message').val('');
                    $('#chat-scroll').animate({
                        scrollTop: $('#chat-scroll').get(0).scrollHeight
                    }, 100);

                    await saveMessageToHistory(msg);
                    
                    $('.send').attr("disabled", false)
                } catch (error) {
                    console.error(error);
                }
            }

            async function saveMessageToHistory(message) {
                try {
                    const response = await axios.post("{{ route('personal-trainer.chat.store') }}", {
                        message,
                        room_id: roomId,
                    }, {
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': "{{ csrf_token() }}",
                        },
                    });
                    
                } catch (error) {
                    console.error(error);
                    throw error; // Rethrow the error for the calling function to handle
                }
            }

            
            let isGetHistory = false
            function getHistoryChat(roomId, page = current_page) {
                isGetHistory = true
                loading = true
                if(page===1){
                    $('#wrap_loading').removeClass('d-none')
                } else {
                    $('.wrap_message').prepend(loader)
                }

                axios.get("{{ route('personal-trainer.get-history-chat') }}", {
                    params: {
                        room_id: roomId,
                        page
                    }
                })
                .then(function (response) {
                    loading = false
                    $('#loader').remove()

                    const data = response.data.data
                    next_page_url = data.next_page_url
                    current_page = data.current_page

                    const wrap = $('.wrap_message');

                    let top = $('#chat-scroll').scrollTop();
                    let scrollHeight = $('#chat-scroll').prop("scrollHeight");

                    // display message
                    const result = data.data.map((item, index) => {
                        if(moment(item.created_at).format('LL') !== moment(data.data[index+1]?.created_at).format('LL')) {
                            const date_chat = `<div class="d-flex justify-content-center">
                              <button class="btn-status bg-gray fw-400 date">${moment(item.created_at).format('LL')}</button>
                             </div>`

                            return displayMessage(item, date_chat)
                        } else {
                            return displayMessage(item)
                        }
                    }).reverse().join(' ')
                    wrap.prepend(result)

                    $('#chat-scroll').scrollTop(top + $('#chat-scroll').prop("scrollHeight") - scrollHeight);

                    if(page===1) {
                        // remove unread message
                        const element = $(`.member[data-room='${roomId}']`) 
                        const unread = element.find('.unread')
                        unread.remove()

                        // scroll message
                        $('#chat-scroll').scrollTop($('#chat-scroll')[0].scrollHeight);
                    } 

                    $('#wrap_loading').addClass('d-none')
                    isGetHistory = false
                })
                .catch(function (error) {
                    console.log(error);
                });
            }

            // display chat from selected user
            const targetRoom = @json($targetRoom);

            if(@json($targetRoom)) {
                roomId = targetRoom['name']
                socket.emit("join", roomId);
                getHistoryChat(roomId)
            }

            // detect user scroll chat to top
            $('#chat-scroll').scroll(function() {
                var pos = $('#chat-scroll').scrollTop();
                if (pos == 0) {
                    if(next_page_url!==null&&!loading) getHistoryChat(roomId, current_page + 1)
                }
            });
            
            function displayMessage(msg, date_chat = '') {
                let chat
                
                if(msg.sender !== sender) {
                    chat = `${date_chat}<div class="d-flex flex-column justify-content-start align-items-start">
                        <div class="mt-8 d-flex gap-4 align-items-center">
                            <div class="symbol symbol-35px symbol-md-40px symbol-circle" data-bs-toggle="tooltip" title="${msg.sender}">
                                <img alt="Pic" src="${msg.avatar ?? 'assets/media/avatars/blank.png'}" />
                            </div>
                            <h1 class="text-dark fs-7 mb-0">${msg.sender}</h1>
                            <p class="text-grey2 text-nowrap fw-400 mb-0">${moment(msg.created_at).fromNow()}</p>
                        </div>
                        <div class="input-wrap text-dark p-5 max-w-400px border-radius-xxl mt-3">
                            ${msg.message}
                        </div>
                    </div>
                    `
                } else {
                    chat = `${date_chat}<div class="mt-8 d-flex justify-content-end align-items-end flex-column">
                        <div class="d-flex flex-row-reverse gap-4 align-items-center justify-content-end">
                            <div class="symbol symbol-35px symbol-md-40px symbol-circle" data-bs-toggle="tooltip" title="${msg.sender}">
                                <img alt="Pic" src="${msg.avatar ?? 'assets/media/avatars/blank.png'}" />
                            </div>
                            <h1 class="text-dark fs-7 mb-0">Anda</h1>
                            <p class="text-grey2 text-nowrap fw-400 mb-0">${moment(msg.created_at).fromNow()}</p>
                        </div>
                        <div class="input-wrap text-end bg-main text-dark p-5 max-w-400px border-radius-xxl mt-3">
                            ${msg.message}
                        </div>
                    </div>
                    `
                }
                                       
                if(!isGetHistory) {
                    $('.wrap_message').append(chat)
                    $('#chat-scroll').scrollTop($('#chat-scroll')[0].scrollHeight);

                    // update last message
                    const element = $(`.member[data-room='${roomId}']`)
                    const last_message = element.find('.message')
                    const time = element.find('.time')
                    last_message.text(msg.message)
                    time.text(moment(msg.time_last_message).fromNow())

                    // add member at top list member
                    $('#wrap_people').prepend(element)
                    $("#wrap_people").animate({ scrollTop: 0 }, "fast")
                } else {
                    return chat
                }
            }
        });
</script>
@endpush