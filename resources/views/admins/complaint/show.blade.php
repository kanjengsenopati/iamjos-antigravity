@extends('layouts.master', ['title' => 'Detail Komplain', 'main' => 'Data Komplain'])

@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <div class="card-header mt-6">
                        <!--begin::Card title-->
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bold fs-3 mb-1">Detail Komplain</span>
                        </h3>
                        <!--end::Card title-->
                    </div>
                    <!--end::Card header-->

                    <!--begin::Card body-->
                    <!--begin::Table-->
                    <div class="card-body">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Nomor Tiket</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->ticket ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Subjek</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->subject ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Topik Komplain</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->faq->question ?? 'Topik tidak tersedia' }}</span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Deskripsi</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->description ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Status</label>
                            <div class="col-lg-8 d-flex align-items-center">
                                <span class="badge 
                                        @if($complaint->status == 'aktif') 
                                            badge-success 
                                        @elseif($complaint->status == 'diproses') 
                                            badge-warning 
                                        @else 
                                            badge-secondary 
                                        @endif">
                                    {{ ucfirst($complaint->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Pengirim</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->user->name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Bukti</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                @if($complaint->attachment)
                                <img src="{{ asset('storage/' . $complaint->attachment) }}" alt="Attachment" width="100">
                                @else
                                <span class="form-control-plaintext">Tidak ada lampiran</span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Respons</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->response ?? 'Belum ada tanggapan' }}</span>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-semibold fs-6">Admin yang Merespons</label>
                            <div class="col-lg-8 d-flex justify-content-between">
                                <span class="form-control-plaintext">{{ $complaint->admin->name ?? 'Belum ada admin yang merespons' }}</span>
                            </div>
                        </div>
                    </div>

                    <!--end::Table-->
                    <div class="card-body">
                        <!--begin::Form-->
                        <form action="{{ route('complaint.update', $complaint->id) }}" method="POST" class="mt-4">
                            @csrf
                            @method('PUT')

                            <!-- Status Field -->
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="aktif" {{ $complaint->status == 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="diproses" {{ $complaint->status == 'diproses' ? 'selected' : '' }}>Diproses</option>
                                    <option value="ditutup" {{ $complaint->status == 'ditutup' ? 'selected' : '' }}>Ditutup</option>
                                </select>
                            </div>

                            <!-- Response Field -->
                            <div class="mb-3">
                                <label for="response" class="form-label">Respons</label>
                                <textarea name="response" id="response" class="form-control" rows="4" required>{{ $complaint->response }}</textarea>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </form>
                        <!--end::Form-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->
</div>
@endsection