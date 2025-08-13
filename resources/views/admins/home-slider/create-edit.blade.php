@extends('layouts.master', ['main' => 'Data Slider', 'title' => request()->routeIs('home-slider.create') ? 'Tambah Slider' : 'Edit Slider'])
@section('content')
    <!--begin::Content wrapper-->
    <div class="d-flex pt-6 flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Basic info-->
                <div class="card mb-5 mb-xl-10">
                    <!--begin::Card header-->
                    <div class="card-header">
                        <!--begin::Card title-->
                        <div class="card-title m-0">
                            <h3 class="fw-bold m-0">
                                {{ request()->routeIs('home-slider.create') ? 'Tambah Slider' : 'Edit Slider' }}
                            </h3>
                        </div>
                        <!--end::Card title-->
                    </div>
                    <!--begin::Card header-->
                    <!--begin::Content-->
                    <div id="kt_account_settings_profile_details" class="collapse show">
                        <!--begin::Form-->
                        <form class="form" method="POST" enctype="multipart/form-data"
                            action="{{ request()->routeIs('home-slider.create') ? route('home-slider.store') : route('home-slider.update', @$homeSlider->id) }}">
                            @csrf
                            <x-form.put-method />
                            <x-alert.alert-validation />
                            <!--begin::Card body-->
                            <div class="card-body">
                                <!--begin::Input group-->
                                <div class="row mb-6">
                                    <!--begin::Label-->
                                    <label for="title" class="col-lg-4 col-form-label required fw-semibold fs-6">Judul
                                        Slider</label>
                                    <!--end::Label-->
                                    <!--begin::Col-->
                                    <div class="col-lg-12">
                                        <!--begin::Row-->
                                        <div class="row">
                                            <!--begin::Col-->
                                            <div class="col-lg-12 fv-row">
                                                <input type="text" name="title" id="title"
                                                    class="form-control mb-3 mb-lg-0" placeholder="Masukkan Judul Slider"
                                                    value="{{ @$homeSlider->title ?? old('title') }}" required />
                                                <!--end::Col-->
                                            </div>
                                            <!--end::Row-->
                                        </div>
                                        <!--end::Col-->
                                    </div>

                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label for= "description"
                                            class="col-lg-4 col-form-label required fw-semibold fs-6">Sub Judul
                                            Slider</label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <!--begin::Row-->
                                            <div class="row">
                                                <!--begin::Col-->
                                                <div class="col-lg-12 fv-row">
                                                    <input type="text" name="description" id="description"
                                                        class="form-control mb-3 mb-lg-0"
                                                        placeholder="Masukkan Sub Judul Slider"
                                                        value="{{ @$homeSlider->description ?? old('description') }}"
                                                        required />
                                                </div>
                                                <!--end::Col-->
                                            </div>
                                            <!--end::Row-->
                                        </div>
                                        <!--end::Col-->
                                    </div>

                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label for="button_text" class="col-lg-4 col-form-label required fw-semibold fs-6">
                                            Teks Tombol Slider</label>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <!--begin::Row-->
                                            <div class="row">
                                                <!--begin::Col-->
                                                <div class="col-lg-12 fv-row">
                                                    <input type="text" name="button_text" id="button_text"
                                                        class="form-control mb-3 mb-lg-0"
                                                        placeholder="Masukkan Teks Tombol Slider"
                                                        value="{{ @$homeSlider->button_text ?? old('button_text') }}"
                                                        required />
                                                </div>
                                                <!--end::Col-->
                                            </div>
                                            <!--end::Row-->
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label for="button_link" class="col-lg-4 col-form-label required fw-semibold fs-6">
                                            Tautan Tombol Slider</label>
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <!--begin::Row-->
                                            <div class="row">
                                                <!--begin::Col-->
                                                <div class="col-lg-12 fv-row">
                                                    <input type="url" name="button_link" id="button_link"
                                                        class="form-control mb-3 mb-lg-0"
                                                        placeholder="Masukkan Tautan Tombol Slider"
                                                        value="{{ @$homeSlider->button_link ?? old('button_link') }}"
                                                        required />
                                                </div>
                                                <!--end::Col-->
                                            </div>
                                            <!--end::Row-->
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label for="media" class="col-lg-4 col-form-label required fw-semibold fs-6">
                                            Media Slider (Gambar/Video)
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <!--begin::Row-->
                                            <div class="row">
                                                <!--begin::Col-->
                                                <div class="col-lg-12 fv-row">
                                                    <input type="file" name="media" id="media"
                                                        class="form-control mb-3 mb-lg-0" accept="image/*,video/*"
                                                        placeholder="Masukkan Media Slider" onchange="previewMedia(this)" />
                                                    <div class="form-text">
                                                        Gambar: JPEG, PNG, JPG, GIF, WebP (max 50MB)<br>
                                                        Video: MP4, AVI, MOV, WMV, FLV (max 50MB)
                                                    </div>

                                                    <!-- Media Preview -->
                                                    <div id="media-preview" class="mt-3" style="display: none;">
                                                        <div class="card" style="max-width: 400px;">
                                                            <div class="card-body p-3">
                                                                <div id="preview-container"></div>
                                                                <div class="mt-2">
                                                                    <small class="text-muted" id="file-info"></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Existing Media -->
                                                    @if (isset($homeSlider) && $homeSlider->media)
                                                        <div class="mt-3" id="current-media">
                                                            <label class="fw-semibold fs-7 text-gray-600">Media Saat
                                                                Ini:</label>
                                                            <div class="card" style="max-width: 400px;">
                                                                <div class="card-body p-3">
                                                                    @if ($homeSlider->isImage())
                                                                        <img src="{{ $homeSlider->media_url }}"
                                                                            alt="Current Slider Media"
                                                                            class="img-fluid rounded"
                                                                            style="max-height: 200px;" />
                                                                        <div class="mt-2">
                                                                            <span class="badge badge-primary">Gambar</span>
                                                                        </div>
                                                                    @elseif($homeSlider->isVideo())
                                                                        <video controls class="w-100 rounded"
                                                                            style="max-height: 200px;">
                                                                            <source src="{{ $homeSlider->media_url }}"
                                                                                type="video/mp4">
                                                                            Browser Anda tidak mendukung video tag.
                                                                        </video>
                                                                        <div class="mt-2">
                                                                            <span class="badge badge-success">Video</span>
                                                                        </div>
                                                                    @endif

                                                                    @if ($homeSlider->media_processing_status === 'processing')
                                                                        <div class="mt-2">
                                                                            <span class="badge badge-warning">Sedang
                                                                                Diproses</span>
                                                                        </div>
                                                                    @elseif($homeSlider->media_processing_status === 'failed')
                                                                        <div class="mt-2">
                                                                            <span class="badge badge-danger">Gagal
                                                                                Diproses</span>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <!--end::Col-->
                                            </div>
                                            <!--end::Row-->
                                        </div>
                                        <!--end::Col-->
                                    </div>

                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label for="is_active" class="col-lg-4 col-form-label fw-semibold fs-6">
                                            Status Aktif
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" name="is_active"
                                                    id="is_active" value="1"
                                                    {{ (isset($homeSlider) && $homeSlider->is_active) || old('is_active', true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_active">
                                                    Aktifkan slider ini
                                                </label>
                                            </div>
                                        </div>
                                        <!--end::Col-->
                                    </div>

                                    <div class="row mb-6">
                                        <!--begin::Label-->
                                        <label for="sort_order" class="col-lg-4 col-form-label fw-semibold fs-6">
                                            Urutan Tampil
                                        </label>
                                        <!--end::Label-->
                                        <!--begin::Col-->
                                        <div class="col-lg-12">
                                            <input type="number" name="sort_order" id="sort_order"
                                                class="form-control mb-3 mb-lg-0" min="0"
                                                placeholder="Masukkan urutan tampil (0 = pertama)"
                                                value="{{ @$homeSlider->sort_order ?? old('sort_order', 0) }}" />
                                            <div class="form-text">
                                                Angka yang lebih kecil akan ditampilkan lebih dulu
                                            </div>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                </div>
                                <!--end::Card body-->
                                <!--begin::Actions-->
                                <div class="card-footer d-flex justify-content-end py-6 px-9">
                                    <a href="{{ route('home-slider.index') }}"
                                        class="btn btn-secondary btn-sm me-3">Batal</a>
                                    <button type="submit" class="btn btn-sm btn-primary"
                                        id="kt_account_profile_details_submit">Simpan</button>
                                </div>
                                <!--end::Actions-->
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Basic info-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
@endsection

@push('scripts')
    <script>
        function previewMedia(input) {
            const previewContainer = document.getElementById('media-preview');
            const previewContent = document.getElementById('preview-container');
            const fileInfo = document.getElementById('file-info');
            const currentMedia = document.getElementById('current-media');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                const fileSize = (file.size / 1024 / 1024).toFixed(2); // Convert to MB
                const fileName = file.name;
                const fileType = file.type;

                // Show file info
                fileInfo.innerHTML = `<strong>${fileName}</strong><br>Size: ${fileSize} MB<br>Type: ${fileType}`;

                // Hide current media if exists
                if (currentMedia) {
                    currentMedia.style.display = 'none';
                }

                // Clear previous preview
                previewContent.innerHTML = '';

                if (file.type.startsWith('image/')) {
                    // Image preview
                    const img = document.createElement('img');
                    img.className = 'img-fluid rounded';
                    img.style.maxHeight = '200px';
                    img.onload = function() {
                        URL.revokeObjectURL(img.src);
                    };
                    img.src = URL.createObjectURL(file);

                    const badge = document.createElement('span');
                    badge.className = 'badge badge-primary mt-2';
                    badge.textContent = 'Gambar Baru';

                    previewContent.appendChild(img);
                    previewContent.appendChild(document.createElement('br'));
                    previewContent.appendChild(badge);

                } else if (file.type.startsWith('video/')) {
                    // Video preview
                    const video = document.createElement('video');
                    video.controls = true;
                    video.className = 'w-100 rounded';
                    video.style.maxHeight = '200px';
                    video.onload = function() {
                        URL.revokeObjectURL(video.src);
                    };
                    video.src = URL.createObjectURL(file);

                    const badge = document.createElement('span');
                    badge.className = 'badge badge-success mt-2';
                    badge.textContent = 'Video Baru';

                    previewContent.appendChild(video);
                    previewContent.appendChild(document.createElement('br'));
                    previewContent.appendChild(badge);
                } else {
                    // Unsupported file type
                    previewContent.innerHTML = '<div class="alert alert-warning">File type not supported for preview</div>';
                }

                previewContainer.style.display = 'block';
            } else {
                previewContainer.style.display = 'none';
                if (currentMedia) {
                    currentMedia.style.display = 'block';
                }
            }
        }

        // Reset preview when form is reset
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('reset', function() {
                    const previewContainer = document.getElementById('media-preview');
                    const currentMedia = document.getElementById('current-media');

                    previewContainer.style.display = 'none';
                    if (currentMedia) {
                        currentMedia.style.display = 'block';
                    }
                });
            }
        });
    </script>
@endpush
