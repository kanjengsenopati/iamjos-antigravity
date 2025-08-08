@extends('layouts.master', ['title' => 'Detail Kelas', 'main' => 'List Kelas'])
@push('css')
<style>
    .hr {
        background: #D7DBFF;
        height: 1px;
    }

    label {
        font-size: 12px;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 18px */
        letter-spacing: 0.06px;
    }

    .text-label {
        /* Paragraph 2/Regular */
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    h1 {
        /* Heading 4/Bold */
        font-family: 'Gothic A1';
        font-size: 1.5rem;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 1.875rem */
        letter-spacing: 0.00625rem;
    }

    h2 {
        color: var(--Black, #000);
        /* Paragraph 2/Bold */
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    .text-sub-title {
        color: #B5B5C3;
        font-family: 'Gothic A1';
        font-size: 0.875rem;
        font-style: normal;
        font-weight: 700;
        line-height: 150%;
        /* 1.3125rem */
        letter-spacing: 0.00438rem;
    }

    .text-label-grey {
        color: var(--Grey, #A5A5A5);
        /* Paragraph 2/Bold */
        font-size: 12px;
        font-style: normal;
        font-weight: 500;
        line-height: 150%;
        /* 21px */
        letter-spacing: 0.07px;
    }

    .card .card-body {
        padding: 1.2rem 2rem !important;
    }

    hr {
        border-top: 0.1px solid #D7DBFF !important;
        outline: none;
        border: none;
        height: 1px !important;
    }

    .btn.btn-active-color-primary.active,
    .btn.btn-active-color-primary:hover {
        color: #3B4CED !important;
    }

    .btn.btn-active-light.active,
    .btn.btn-active-light:hover,
    .nav-link:hover,
    .btn.btn-active-light.active:hover {
        background: none !important;
    }

    .tab-content {
        font-family: 'Gothic A1' !important;
    }

    .card-body.v2 {
        padding: 2rem !important;
    }
</style>
@endpush
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Container-->
    <div id="kt_content_container" class="app-container container-xxl">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card body-->
            <div class="card-body">
                <div class="d-flex gap-5 align-items-center mb-2">
                    <a href="{{ route('gym-class.index') }}" class="mt-1">
                        <span class="menu-icon back pt-1">
                            <i class="ki-duotone ki-arrow-left">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                        </span>
                    </a>
                    <h1 class="text-capitalize mb-0">{{ $gymClass->name }}</h1>
                    <a href="{{ route('gym-class.edit', $gymClass->id) }}">
                        <i class="ki-duotone ki-notepad-edit fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                    </a>
                </div>
                <span class="badge badge-primary">{{ $gymClass->level }}</span> <span class="badge badge-secondary">{{
                    $gymClass->gym_class_category->name }}</span>
                <div class="hover-scroll-x mt-5">
                    <div class="d-grid">
                        <ul class="nav nav-tabs flex-nowrap text-nowrap">
                            <li class="nav-item">
                                <a class="nav-link active btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                    id="nav_tab_information" data-bs-toggle="tab" href="#tab_information">
                                    Informasi Kelas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-active-light btn-color-gray-600 btn-active-color-primary"
                                    id="nav_tab_partisipant" data-bs-toggle="tab" href="#tab_partisipant">
                                    Peserta Kelas
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--end::Card body-->
        </div>

        <div class="card mt-6">
            <div class="card-body v2">
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade active show" id="tab_information" role="tabpanel">
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Harga</label>
                                @if ($gymClass->strikeout_price > 0)
                                <p class="text-label">Rp<s
                                        class="text-label text-danger">@money($gymClass->strikeout_price)</s>
                                    @money($gymClass->price)</p>
                                @else
                                <p class="text-label">Rp<s>@money($gymClass->price)</s></p>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Hari</label>
                                <p class="text-label">{{ $gymClass->day }}</p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Jam</label>
                                <p class="text-label">
                                    {{ $gymClass->start_time . '-' . $gymClass->end_time }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Tipe Personal Trainer</label>
                                <p class="text-label">{{ $gymClass->trainer_type }}</p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Personal Trainer</label>
                                {{-- <p class="text-label">{{$gymClass?->personal_trainer?->name ??
                                    $gymClass->external_trainer->name}}</p> --}}
                                <p class="text-label">
                                    {{ $gymClass?->personal_trainer_name }}
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Kuota</label>
                                <p class="text-label">{{ $gymClass->quota }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Tipe Pengguna</label>
                                <p class="text-label">
                                    {{ $gymClass->is_special_membership ? 'Spesial Membership' : 'Semua Pengguna' }}
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Status</label>
                                <p class="text-label">
                                    <span class="badge badge-primary">{{ $gymClass?->is_active ? 'Aktif' : 'Non Aktif'
                                        }}</span>
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label class="text-label text-muted">Masa Berlaku</label>
                                <p class="text-label">
                                    <span class="badge badge-primary">{{ $gymClass->start_date ?
                                        Carbon\Carbon::parse($gymClass->start_date)->format('d M Y') . ' - ' .
                                        Carbon\Carbon::parse($gymClass->end_date)->format('d M Y') : 'Setiap ' .
                                        $gymClass->day
                                        }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div>
                                <label class="text-label text-muted">Deskripsi</label>
                                <p class="text-label">{{ $gymClass->description }}</p>
                            </div>
                        </div>
                        <div class="row en-feature">
                            <div>
                                <label class="text-label text-muted">Deskripsi (English)</label>
                                <p class="text-label en-feature">{{ $gymClass->description_en }}</p>
                            </div>
                        </div>
                        <div class="row" style="display: none">
                            <div>
                                <label class="text-label text-muted">Deskripsi (Chinese)</label>
                                <p class="text-label en-feature">{{ $gymClass->description_cn }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="tab_partisipant" role="tabpanel">
                        <!--begin::Accordion-->
                        <div class="accordion accordion-icon-toggle" id="kt_accordion_2">
                            @if(count($gymClassHistories) > 0)
                                @foreach ($gymClassHistories as $date => $gymClassHistory)
                                <!--begin::Item-->
                                <div class="mb-5">
                                    <!--begin::Header-->
                                    <div class="accordion-header py-3 d-flex collapsed" data-bs-toggle="collapse"
                                        data-bs-target="#kt_accordion_{{ $date }}">
                                        <span class="accordion-icon">
                                            <i class="ki-duotone ki-arrow-right fs-4"><span class="path1"></span><span
                                                    class="path2"></span></i>
                                        </span>
                                        <h3 class="fs-4 fw-semibold mb-0 ms-4">Kelas Tanggal
                                            {{ date('d/m/Y', strtotime($date)) }}</h3>&nbsp;
                                        @if (
                                        $gymClassHistory->first()?->date >= date('Y-m-d') &&
                                        !$gymClass->gym_class_cancel_histories()->where('date', $date)->exists())
                                        <a type="button" onclick="cancelClass('{{ $date }}')"
                                            class="badge badge-danger btn-cancel">
                                            Batalkan
                                        </a>
                                        @else
                                        @if($gymClass->gym_class_cancel_histories()?->where('date',
                                            $date)->first()?->created_at)
                                        <i class="text-danger">Kelas telah dibatalkan pada
                                            {{ $gymClass->gym_class_cancel_histories()?->where('date',
                                            $date)->first()?->created_at }}</i>
                                        @endif
                                        @endif
                                    </div>
                                    <!--end::Header-->

                                    <!--begin::Body-->
                                    <div id="kt_accordion_{{ $date }}" class="collapse fs-6 ps-10"
                                        data-bs-parent="#kt_accordion_2">
                                        <div class="d-flex justify-content-center align-content-center mb-3 gap-2">
                                            <span class="badge badge-primary">Total Peserta
                                                {{ $gymClassHistory->first()?->total_partisipant ?? 0 }}</span>
                                            <span class="badge badge-success">Total Hadir
                                                {{ $gymClassHistory->first()?->total_attendance ?? 0 }}</span>
                                            <span class="badge badge-secondary">Total Belum Hadir
                                                {{ $gymClassHistory->first()?->total_pending ?? 0 }}</span>
                                        </div>
                                        <div class="table-responsive">
                                            <table id="datatable-partisipant"
                                                class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                                <thead>
                                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                        <th style="width: 5%">No</th>
                                                        <th class="w-125px">Avatar</th>
                                                        <th>Nama Peserta</th>
                                                        <th>Email</th>
                                                        <th>Phone</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="text-dark fw-semibold">
                                                    @foreach ($gymClassHistory as $history)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            <img src="{{ asset($history->user->avatar) }}"
                                                                class="img img-thumbnail rounded" alt="avatar">
                                                        </td>
                                                        <td>{{ $history->user->name }}</td>
                                                        <td>{{ $history->user->email }}</td>
                                                        <td>{{ $history->user->phone }}</td>
                                                        <td>{{ $history->translated_status }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <!--end::Body-->
                                </div>
                                <!--end::Item-->
                                @endforeach
                            @else
                                <div class="alert alert-info text-center">
                                    Belum ada peserta yang terdaftar pada kelas ini.
                                </div>
                            @endif
                        </div>
                        <!--end::Accordion-->



                    </div>
                </div>
                <!--end::Container-->
            </div>
            <!--end::Post-->
        </div>

    </div>
</div>


<form action="{{ route('gym-class.cancel') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-cancel" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="cancel-title"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="gym_class_id" value="{{ $gymClass->id }}" hidden>
                    <input type="text" name="date" id="date_cancel" hidden>
                    <textarea placeholder="Alasan pembatalan" name="cancel_reason" class="form-control" rows="10"
                        required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-down fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Batalkan Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
@push('js')
<script>
    function cancelClass(date) {
            $('#date_cancel').val(date)
            $('#cancel-title').text(`Anda yakin akan membatalkan kelas tanggal ${date} ??`)
            $('#modal-cancel').modal('show')
        }
</script>
@endpush