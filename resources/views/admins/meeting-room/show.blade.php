@extends('layouts.master', ['main' => 'Detail Meeting Venue', 'title' => $meetingRoom->hotel])

@section('content')
    <!--begin::Container-->
    <div id="kt_content_container" class="app-container container-xxl pt-6">
        <div class="row g-7">
            <div class="col-xl-12">
                <div class="card h-lg-100" id="kt_contacts_main">
                    <div class="card-header" id="kt_chat_contacts_header">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3">
                                Detail Meeting Venue: {{ $meetingRoom->hotel }}
                            </span>
                            <span class="text-gray-500 mt-1 fw-semibold fs-6">
                                {{ $meetingRoom->city_name }}, {{ $meetingRoom->province_name }}
                            </span>
                        </h3>
                        <div class="card-toolbar">
                            <a href="{{ route('venue.rooms.index', $meetingRoom->id) }}"
                                class="btn btn-sm btn-success me-2">
                                <i class="fa fa-door-open"></i>
                                Kelola Ruang
                            </a>
                            <a href="{{ route('meeting-room.edit', $meetingRoom->id) }}"
                                class="btn btn-sm btn-warning me-2">
                                <i class="fa fa-edit"></i>
                                Edit
                            </a>
                            <a href="{{ route('meeting-room.index') }}" class="btn btn-sm btn-light">
                                <i class="fa fa-arrow-left"></i>
                                Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-5">
                        {{-- Navigation Tabs --}}
                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-6">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#overview_tab">
                                    <i class="ki-duotone ki-home fs-2 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Overview
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#gallery_tab">
                                    <i class="ki-duotone ki-picture fs-2 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Galeri
                                    @if ($meetingRoom->gallery && count($meetingRoom->gallery) > 0)
                                        <span
                                            class="badge badge-circle badge-primary ms-1">{{ count($meetingRoom->gallery) }}</span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#rooms_tab">
                                    <i class="ki-duotone ki-entrance-left fs-2 me-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    Ruang Meeting
                                    @if ($meetingRoom->meeting_rooms && count($meetingRoom->meeting_rooms) > 0)
                                        <span
                                            class="badge badge-circle badge-success ms-1">{{ count($meetingRoom->meeting_rooms) }}</span>
                                    @endif
                                </a>
                            </li>
                        </ul>

                        {{-- Tab Content --}}
                        <div class="tab-content" id="myTabContent">
                            {{-- Overview Tab --}}
                            <div class="tab-pane fade show active" id="overview_tab" role="tabpanel">
                                <div class="row g-6">
                                    {{-- Venue Information Card --}}
                                    <div class="col-lg-6">
                                        <div class="card card-flush h-100">
                                            <div class="card-header">
                                                <div class="card-title">
                                                    <i class="ki-duotone ki-information fs-1 text-primary me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    <h3 class="fw-bold m-0">Informasi Venue</h3>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="mb-5">
                                                    <label class="fw-semibold text-muted fs-7 mb-1">Nama Hotel/Venue</label>
                                                    <div class="fw-bold fs-6">{{ $meetingRoom->hotel }}</div>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="fw-semibold text-muted fs-7 mb-1">Alamat Lengkap</label>
                                                    <div class="fw-bold fs-6">{{ $meetingRoom->address }}</div>
                                                </div>
                                                <div class="mb-5">
                                                    <label class="fw-semibold text-muted fs-7 mb-1">Lokasi</label>
                                                    <div class="fw-bold fs-6">
                                                        <i class="ki-duotone ki-geolocation text-primary me-1">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        {{ $meetingRoom->city_name }}, {{ $meetingRoom->province_name }}
                                                    </div>
                                                </div>
                                                @if ($meetingRoom->max_capacity)
                                                    <div class="mb-0">
                                                        <label class="fw-semibold text-muted fs-7 mb-1">Kapasitas
                                                            Maksimum</label>
                                                        <div class="fw-bold fs-6 text-success">
                                                            <i class="ki-duotone ki-user fs-4 text-success me-1">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            {{ number_format($meetingRoom->max_capacity) }} orang
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Contact Information Card --}}
                                    <div class="col-lg-6">
                                        <div class="card card-flush h-100">
                                            <div class="card-header">
                                                <div class="card-title">
                                                    <i class="ki-duotone ki-call fs-1 text-success me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                    </i>
                                                    <h3 class="fw-bold m-0">Kontak</h3>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                @if ($meetingRoom->email || $meetingRoom->phone)
                                                    @if ($meetingRoom->email)
                                                        <div class="mb-5">
                                                            <label class="fw-semibold text-muted fs-7 mb-1">Email</label>
                                                            <div class="fw-bold fs-6">
                                                                <a href="mailto:{{ $meetingRoom->email }}"
                                                                    class="text-hover-primary">
                                                                    <i class="ki-duotone ki-sms text-primary me-1">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                    </i>
                                                                    {{ $meetingRoom->email }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    @if ($meetingRoom->phone)
                                                        <div class="mb-0">
                                                            <label class="fw-semibold text-muted fs-7 mb-1">Telepon</label>
                                                            <div class="fw-bold fs-6">
                                                                <a href="tel:{{ $meetingRoom->phone }}"
                                                                    class="text-hover-primary">
                                                                    <i class="ki-duotone ki-phone text-success me-1">
                                                                        <span class="path1"></span>
                                                                        <span class="path2"></span>
                                                                    </i>
                                                                    {{ $meetingRoom->phone }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    @endif
                                                @else
                                                    <div class="d-flex flex-column text-center py-5">
                                                        <i class="ki-duotone ki-call fs-3x text-gray-400 mb-4">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                        </i>
                                                        <div class="text-gray-500">Belum ada informasi kontak</div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    {{-- System Information Card --}}
                                    @if ($meetingRoom->external_id)
                                        <div class="col-12">
                                            <div class="card card-flush">
                                                <div class="card-header">
                                                    <div class="card-title">
                                                        <i class="ki-duotone ki-code fs-1 text-info me-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                        </i>
                                                        <h3 class="fw-bold m-0">Informasi Sistem</h3>
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="fw-semibold text-muted fs-7 mb-1">External
                                                                ID</label>
                                                            <div class="fw-bold fs-6">
                                                                <code
                                                                    class="bg-light-primary text-primary px-2 py-1 rounded">{{ $meetingRoom->external_id }}</code>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="fw-semibold text-muted fs-7 mb-1">Terakhir
                                                                Diperbarui</label>
                                                            <div class="fw-bold fs-6">
                                                                <i class="ki-duotone ki-calendar text-warning me-1">
                                                                    <span class="path1"></span>
                                                                    <span class="path2"></span>
                                                                </i>
                                                                {{ $meetingRoom->updated_at->format('d M Y H:i') }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Gallery Tab --}}
                            <div class="tab-pane fade" id="gallery_tab" role="tabpanel">
                                @if ($meetingRoom->gallery && count($meetingRoom->gallery) > 0)
                                    <div class="row g-5">
                                        @foreach ($meetingRoom->gallery as $index => $photo)
                                            <div class="col-lg-4 col-md-6">
                                                <div class="card card-flush">
                                                    <div class="card-body p-0">
                                                        <div class="position-relative overflow-hidden rounded">
                                                            <img src="{{ Storage::url($photo) }}"
                                                                alt="{{ $meetingRoom->hotel }} - Foto {{ $index + 1 }}"
                                                                class="img-fluid gallery-image"
                                                                style="height: 250px; width: 100%; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                                                                data-bs-toggle="modal" data-bs-target="#galleryModal"
                                                                data-image="{{ Storage::url($photo) }}"
                                                                data-title="{{ $meetingRoom->hotel }} - Foto {{ $index + 1 }}"
                                                                onmouseover="this.style.transform='scale(1.05)'"
                                                                onmouseout="this.style.transform='scale(1)'">
                                                            <div class="position-absolute top-0 end-0 m-3">
                                                                <span
                                                                    class="badge badge-primary">{{ $index + 1 }}/{{ count($meetingRoom->gallery) }}</span>
                                                            </div>
                                                            <div
                                                                class="position-absolute bottom-0 start-0 end-0 bg-gradient-dark p-3">
                                                                <div class="text-white fw-bold">{{ $meetingRoom->hotel }}
                                                                </div>
                                                                <div class="text-gray-300 fs-7">Klik untuk memperbesar
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="d-flex flex-column text-center py-10">
                                        <i class="ki-duotone ki-picture fs-3x text-gray-400 mb-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="text-gray-700 fs-2 fw-bold mb-2">Belum Ada Galeri</div>
                                        <div class="text-gray-500">Belum ada foto yang tersedia untuk venue ini</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Rooms Tab --}}
                            <div class="tab-pane fade" id="rooms_tab" role="tabpanel">
                                @if ($meetingRoom->meeting_rooms && count($meetingRoom->meeting_rooms) > 0)
                                    <div class="row g-5">
                                        @foreach ($meetingRoom->meeting_rooms as $index => $room)
                                            <div class="col-lg-6 col-xl-4">
                                                <div class="card card-flush h-100">
                                                    <div class="card-body p-0">
                                                        {{-- Room Photo --}}
                                                        <div class="position-relative overflow-hidden rounded-top">
                                                            @if ($room->photo)
                                                                <img src="{{ Storage::url($room->photo) }}"
                                                                    alt="{{ $room->name }}"
                                                                    class="img-fluid room-image"
                                                                    style="height: 200px; width: 100%; object-fit: cover; cursor: pointer; transition: transform 0.3s ease;"
                                                                    data-bs-toggle="modal" data-bs-target="#roomModal"
                                                                    data-image="{{ Storage::url($room->photo) }}"
                                                                    data-title="{{ $room->name }}"
                                                                    onmouseover="this.style.transform='scale(1.05)'"
                                                                    onmouseout="this.style.transform='scale(1)'">
                                                            @else
                                                                <div class="bg-light-primary d-flex align-items-center justify-content-center"
                                                                    style="height: 200px;">
                                                                    <div class="text-center">
                                                                        <i
                                                                            class="ki-duotone ki-home fs-3x text-primary mb-3">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                        </i>
                                                                        <div class="text-gray-600 fw-semibold">Tidak ada
                                                                            foto</div>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                            <div class="position-absolute top-0 end-0 m-3">
                                                                <span class="badge badge-success">Ruang
                                                                    {{ $index + 1 }}</span>
                                                            </div>
                                                        </div>

                                                        {{-- Room Info --}}
                                                        <div class="p-5">
                                                            <div class="mb-4">
                                                                <h4 class="fw-bold text-gray-900 mb-2">{{ $room->name }}
                                                                </h4>
                                                                @if ($room->meeting_room_layouts->count() > 0)
                                                                    <div class="text-gray-600 fs-7 mb-3">
                                                                        <i
                                                                            class="ki-duotone ki-abstract-26 text-success me-1">
                                                                            <span class="path1"></span>
                                                                            <span class="path2"></span>
                                                                        </i>
                                                                        {{ $room->meeting_room_layouts->count() }} layout
                                                                        tersedia
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Room Layouts --}}
                                                            @if ($room->meeting_room_layouts->count() > 0)
                                                                <div class="mb-4">
                                                                    <label
                                                                        class="fw-semibold text-gray-700 fs-7 mb-2">Layout
                                                                        & Kapasitas:</label>
                                                                    <div class="row g-2">
                                                                        @foreach ($room->meeting_room_layouts as $layout)
                                                                            <div class="col-12">
                                                                                <div
                                                                                    class="d-flex justify-content-between align-items-center bg-light-info rounded px-3 py-2">
                                                                                    <div class="d-flex align-items-center">
                                                                                        <i
                                                                                            class="ki-duotone ki-design-1 text-info me-2 fs-6">
                                                                                            <span class="path1"></span>
                                                                                            <span class="path2"></span>
                                                                                        </i>
                                                                                        <span
                                                                                            class="fw-semibold text-gray-800 fs-7">{{ $layout->layout }}</span>
                                                                                    </div>
                                                                                    @if ($layout->capacity > 0)
                                                                                        <span
                                                                                            class="badge badge-light-success">
                                                                                            <i
                                                                                                class="ki-duotone ki-user fs-8 me-1">
                                                                                                <span
                                                                                                    class="path1"></span>
                                                                                                <span
                                                                                                    class="path2"></span>
                                                                                            </i>
                                                                                            {{ $layout->capacity }} orang
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- Room Actions --}}
                                                            <div class="text-center">
                                                                <a href="{{ route('venue.rooms.edit', [$meetingRoom->id, $room->id]) }}"
                                                                    class="btn btn-sm btn-light-primary me-2">
                                                                    <i class="fa fa-edit fs-7 me-1"></i>
                                                                    Edit Ruang
                                                                </a>
                                                                @if ($room->photo)
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="d-flex flex-column text-center py-10">
                                        <i class="ki-duotone ki-entrance-left fs-3x text-gray-400 mb-4">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        <div class="text-gray-700 fs-2 fw-bold mb-2">Belum Ada Ruang Meeting</div>
                                        <div class="text-gray-500 mb-4">Belum ada ruang meeting yang tersedia untuk venue
                                            ini</div>
                                        <div>
                                            <a href="{{ route('venue.rooms.create', $meetingRoom->id) }}"
                                                class="btn btn-primary">
                                                <i class="fa fa-plus me-2"></i>
                                                Tambah Ruang Meeting
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gallery Modal -->
    <div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="galleryModalLabel">Galeri Venue</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalImage" src="" alt="" class="img-fluid rounded"
                        style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>

    <!-- Room Modal -->
    <div class="modal fade" id="roomModal" tabindex="-1" aria-labelledby="roomModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="roomModalLabel">Foto Ruang Meeting</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <img id="modalRoomImage" src="" alt="" class="img-fluid rounded"
                        style="max-height: 70vh;">
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Handle gallery image clicks
            $('.gallery-image').click(function() {
                var imageSrc = $(this).data('image');
                var imageTitle = $(this).data('title');

                $('#modalImage').attr('src', imageSrc);
                $('#modalImage').attr('alt', imageTitle);
                $('#galleryModalLabel').text(imageTitle);
            });

            // Handle room image clicks
            $('.room-image, .btn[data-bs-target="#roomModal"]').click(function() {
                var imageSrc = $(this).data('image');
                var imageTitle = $(this).data('title');

                $('#modalRoomImage').attr('src', imageSrc);
                $('#modalRoomImage').attr('alt', imageTitle);
                $('#roomModalLabel').text(imageTitle);
            });
        });
    </script>
@endpush
