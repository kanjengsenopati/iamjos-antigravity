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
                        {{-- Venue Information --}}
                        <div class="row mb-8">
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4 class="card-title">Informasi Venue</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Nama Hotel/Venue:</strong><br>
                                            {{ $meetingRoom->hotel }}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Alamat:</strong><br>
                                            {{ $meetingRoom->address }}
                                        </div>
                                        <div class="mb-3">
                                            <strong>Lokasi:</strong><br>
                                            {{ $meetingRoom->city_name }}, {{ $meetingRoom->province_name }}
                                        </div>
                                        @if ($meetingRoom->max_capacity)
                                            <div class="mb-3">
                                                <strong>Kapasitas Maksimum:</strong><br>
                                                {{ number_format($meetingRoom->max_capacity) }} orang
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border">
                                    <div class="card-header">
                                        <h4 class="card-title">Kontak</h4>
                                    </div>
                                    <div class="card-body">
                                        @if ($meetingRoom->email)
                                            <div class="mb-3">
                                                <strong>Email:</strong><br>
                                                <a href="mailto:{{ $meetingRoom->email }}">{{ $meetingRoom->email }}</a>
                                            </div>
                                        @endif
                                        @if ($meetingRoom->phone)
                                            <div class="mb-3">
                                                <strong>Telepon:</strong><br>
                                                <a href="tel:{{ $meetingRoom->phone }}">{{ $meetingRoom->phone }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Photo Section --}}
                        @if ($meetingRoom->photo)
                            <div class="row mb-8">
                                <div class="col-12">
                                    <div class="card border">
                                        <div class="card-header">
                                            <h4 class="card-title">Foto Venue</h4>
                                        </div>
                                        <div class="card-body text-center">
                                            <img src="{{ Storage::url($meetingRoom->photo) }}"
                                                alt="{{ $meetingRoom->hotel }}" class="img-fluid rounded shadow-sm"
                                                style="max-height: 400px;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Daftar ruang meeting dihapus -->

                        {{-- Metadata --}}
                        @if ($meetingRoom->external_id)
                            <div class="mt-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Informasi Sistem</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted">External ID:</small><br>
                                                <code>{{ $meetingRoom->external_id }}</code>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted">Terakhir diperbarui:</small><br>
                                                {{ $meetingRoom->updated_at->format('d M Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
