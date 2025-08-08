@extends('layouts.master', ['title' => 'Detail Gate Activity', 'main' => 'Gate Activity'])
@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <!--begin::Post-->
    <div class="post d-flex flex-column-fluid" id="kt_post">
        <!--begin::Container-->
        <div id="kt_content_container" class="app-container container-xxl">
            <!--begin::Card-->
            <div class="card">
                <!--begin::Card body-->
                <div class="card-body">
                    <div class="d-flex gap-2 align-items-center mb-3">
                        <a href="{{ route('gate-activity.index') }}" class="mt-1">
                            <span class="menu-icon back pt-1">
                                <i class="ki-duotone ki-arrow-left">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </a>
                        <h3 class="text-capitalize mb-0">Detail Gate Activity</h3>
                    </div>
                    <hr class="mt-8 mb-3">
                    <div class="row">
                        <table class="table table-sm table-bordered" rules="none">
                            <tr height=40px>
                                <td style="width: 30%" class="fw-semibold text-label text-muted">Gate Number</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label">
                                    {{ $gateActivity->gate_number ?? '-' }}
                                </td>
                            </tr>
                            <tr height=40px>
                                <td style="width: 30%" class="fw-semibold text-label text-muted">Waktu</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label">
                                    {{ date('d F Y H:i', strtotime($gateActivity->activity_at)) }}
                                </td>
                            </tr>
                            <tr height=40px>
                                <td style="width: 30%" class="fw-semibold text-label text-muted">Aktifitas</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label">
                                    @php
                                        if ($gateActivity->status == "IN" || $gateActivity->status == "OUT") {
                                            if ($gateActivity->parentable_type == 'App\Models\User') {  // ini membership
                                                echo "Membership : {$gateActivity->barcode} - {$gateActivity->parent->name}";
                                            } elseif ($gateActivity->parentable_type == 'App\Models\GateCard') { // ini untuk yg pake card
                                                $reason = $gateActivity->reason ?? '-';
                                                echo "Card : {$gateActivity->barcode} - {$gateActivity->parent->card_owner}";
                                            } elseif ($gateActivity->parentable_type == 'App\Models\Employee') {
                                                echo "Employee : {$gateActivity->barcode} - {$gateActivity->parent->name}";
                                            } else {
                                                echo "Employee : {$gateActivity->description}";
                                            }
                                        } else {
                                            echo $gateActivity->description;
                                        }
                                    @endphp 
                                </td>
                            </tr>
                            @if ($gateActivity->parentable_type == "App\Models\User")
                                <tr height=40px>
                                    <td style="width: 30%" class="fw-semibold text-label text-muted">Status Membership</td>
                                    <td style="width: 1%">:</td>
                                    <td class="text-label">
                                        @php
                                            $membership = App\Models\MembershipHistory::where('user_id', $gateActivity->parentable_id)
                                                ->where(function ($query) use ($gateActivity) {
                                                    $query->where('start_active_date', '<=', $gateActivity->activity_at)
                                                        ->where('expiry_date', '>=', $gateActivity->activity_at)
                                                        ->orWhere('expiry_date', null);
                                                })
                                                ->oldest()
                                                ->first();
                                            if ($membership && $membership->is_timeoff) {
                                                echo "User Cuti";
                                            } elseif ($membership) {
                                                if ($membership->expiry_date == null || $membership->expiry_date == 'Aktif Selamanya') {
                                                    echo "Membership Aktif";
                                                } else {
                                                    $expired = \Carbon\Carbon::parse($membership->expiry_date)->diffInDays() . ' Hari lagi';
                                                    echo "Membership Aktif <br> Expired : {$expired}";
                                                }
                                            } else {
                                                echo "Membership Expired";
                                            }
                                        @endphp
                                    </td>
                                </tr>
                            @endif
                            <tr height=40px>
                                <td style="width: 30%" class="fw-semibold text-label text-muted">Status</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label">
                                    @php
                                        if ($gateActivity->status == "IN" || $gateActivity->status == "OUT") {
                                            echo "<span class='badge badge-light-success'>CHECK {$gateActivity->status}</span>";
                                        } elseif ($gateActivity->status == "FAILED") {
                                            echo "<span class='badge badge-light-danger'>{$gateActivity->status}</span>";
                                        } elseif ($gateActivity->status == "CREATE" || $gateActivity->status == "UPDATE" || $gateActivity->status == "DELETE") {
                                            echo "<span class='badge badge-light-info'>{$gateActivity->status}</span>";
                                        } else {
                                            echo "<span class='badge badge-light-warning'>{$gateActivity->status}</span>";
                                        }
                                    @endphp
                                </td>
                            </tr>
                            <tr height=40px>
                                <td style="width: 30%" class="fw-semibold text-label text-muted">Reason</td>
                                <td style="width: 1%">:</td>
                                <td class="text-label">
                                    {{ $gateActivity->reason }}
                                </td>
                            </tr>
                        </table>
                    </div>

                </div>
                <!--end::Card body-->
            </div>
        </div>
    </div>
</div>
<!--end::Wrapper-->

@if ($gateActivity->parentable_type == "App\Models\User")
    <div class="content pt-4 d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card">
                    <!--begin::Card body-->
                    <div class="card-body">
                        <div class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Notes User</span>
                            </h3>
                            <div class="d-flex align-items-center gap-2 gap-lg-3">
                                <a type="button" class="btn btn-primary btn-sm" onclick="createNotes()">
                                    <i class="fa fa-plus"></i> Notes
                                </a>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="datatable-notes" class="table table-striped border rounded gy-5 gs-7">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                        <th style="width: 5%">No</th>
                                        <th class="text-center" style="width: 15%">Tanggal</th>
                                        <th class="text-center">Catatan</th>
                                        <th class="text-center" style="width: 10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
            </div>
        </div>
    </div>

    {{-- modal create - edit --}}
    <form action="{{ route('notes.store') }}" method="POST" enctype="multipart/form-data" id="notes-form">
        @csrf
        <input type="text" name="_method" value="POST" id="notes-method" hidden>
    
        <div class="modal fade" tabindex="-1" id="modal-notes">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title" id="notes-title">Create Notes</h3>
                    </div>
    
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="{{ @$gateActivity->parentable_id }}">
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label mt-3" for="description">
                                <span class="required text-dark">Deskripsi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Input Deskripsi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea class="form-control" id="notes-description" name="description" required></textarea>
                            <!--end::Input-->
                        </div>
                        <!--begin::Input group-->
                    </div>
    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endif


@endsection

@push('js')
@if ($gateActivity->parentable_type == "App\Models\User")
    <script>
        function createNotes() {
            $("#modal-notes").modal("show")
        }

        function editNotes(id) {
            $.ajax({
                url: "{{ url('notes') }}/" + id,
                method: 'get',
                type: 'json',
            }).done(function(data) {
                $("#notes-method").val('PUT');
                $("#notes-form").attr('action', "{{ route('notes.update', ':id') }}".replace(':id', id));
                $("#notes-title").text('Edit Notes')
                $('#notes-description').val(data.description);
                $("#modal-notes").modal("show")
            });
        }

        $(document).ready(function() {
            var tableNotes = $('#datatable-notes').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('notes.index') }}",
                    type: 'GET',
                    data: {
                        user_id: "{{ $gateActivity->parentable_id }}"
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
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'date',
                        name: 'date',
                    },
                    {
                        data: 'description',
                        name: 'description',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: -1,
                    }
                ]
            });
        })
    </script>
@endif
@endpush

