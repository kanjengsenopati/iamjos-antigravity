<div class="scroll-y me-n5 pe-5 h-200px h-lg-auto" data-kt-scroll="true"
    data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-max-height="auto"
    data-kt-scroll-dependencies="#kt_header, #kt_app_header, #kt_toolbar, #kt_app_toolbar, #kt_footer, #kt_app_footer, #kt_chat_contacts_header"
    data-kt-scroll-wrappers="#kt_content, #kt_app_content, #kt_chat_contacts_body" data-kt-scroll-offset="5px">
    @forelse($rooms as $value)
    <div data-room="{{ $value->name }}" class="row person cursor-pointer mb-8">
        <div class="col-9">
            <div class="d-flex w-100 align-items-center gap-4">
                <div class="symbol symbol-45px symbol-md-50px symbol-circle" data-bs-toggle="tooltip"
                    title="{{ $value->user->name ?? '' }}">
                    <img alt="Profile Picture"
                        src="{{asset($value->user->avatar ?? 'assets/media/avatars/blank.png')}}" />
                </div>
                <div class="d-flex flex-column justify-content-center w-100 overflow-hidden">
                    <h1 id="username" class="text-dark fs-7">{{ $value->user->name }}</h1>
                    <p class="fw-400 message text-grey mb-0">{{ $value->last_message ??
                        'Belum ada pesan'
                        }}</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="text-end">
                <p class="text-grey2 text-nowrap fw-400"></p>
                @if ($value->total_unread_message > 0)
                <button class="btn-status bg-gray text-blue">{{
                    $value->total_unread_message ?? '' }}</button>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="text-center vh-50 d-flex flex-column align-items-center justify-content-center gap-8">
        <div class="wrap-icon d-flex justify-content-center align-items-center">
            <img src="{{asset('assets/media/icons/Empty-folder.svg')}}" alt="empty">
        </div>
        <p>Belum ada pesan</p>
    </div>
    @endforelse
</div>