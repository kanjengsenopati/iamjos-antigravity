<div class="d-flex gap-1 align-items-center">
    <div>
        <h4>Jadwal Sesi</h4>
    </div>
    <div>
        <a href="{{ route('personal-trainer.edit', $personalTrainer->id) }}">
            <i class="ki-duotone ki-notepad-edit fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
    </div>
</div>
<div class="row">
    @foreach ($days as $day)
        <div class="col-sm-4 border pb-2">
            <div class="row">
                <div class="col-auto mt-2">
                    <div class="fv-row my-2">
                        <h5>{{ $day }} 
                        
                        @can('gym-place')
                        <a type="button" data-toggle="modal"
                                onclick="showModal('modal-cancel{{ $day }}')" class="badge badge-danger">Cancel
                                Jadwal</a>
                        @endcan
                                </h5>
                    </div>
                </div>
            </div>
            @php
                $schedules = $personalTrainer
                    ->personal_trainer_schedules()
                    ->where('day', $day)
                    ->orderBy('start_time')
                    ->get();
            @endphp
            <form action="{{ route('personal-trainer-schedule-packet-session.cancel-schedule') }}" method="post"
                enctype="multipart/form-data">
                @csrf
                <div class="modal fade" id="modal-cancel{{ $day }}" tabindex="-1" data-bs-backdrop="static"
                    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="staticBackdropLabel">Pembatalan Sesi</h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="">Tanggal</label>
                                    <input type="date" name="date"
                                        onchange="getPartisipant('{{ $day }}')"
                                        id="cancel_date_{{ $day }}" class="form-control" required>
                                </div>
                                <div class="form-group mt-2">
                                    <label for="">Sesi</label>
                                    <select name="personal_trainer_schedule_id[]" multiple
                                        id="personal_trainer_schedule_id_{{ $day }}"
                                        onchange="getPartisipant('{{ $day }}')"
                                        class="form-control select2 form-select" required>
                                        @foreach ($schedules as $schedule)
                                            <option value="{{ $schedule->id }}">
                                                {{ $schedule->start_time }}-{{ $schedule->end_time }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="d-flex gap-3">
                                    <input type="checkbox" class="select-all"
                                        onchange="getPartisipant('{{ $day }}')">
                                    <label style="font-size: 14px;" class="cursor-pointer">Select
                                        All</label>
                                </div>
                                <div class="form-grou mt-2">
                                    <textarea name="cancel_reason" class="form-control" rows="5" required placeholder="Alasan pembatalan"></textarea>
                                </div>
                                <hr>
                                <h4 class="mt-3">User Booked</h4>
                                <div class="table-responsive" style="max-height: 30vh">
                                    <table id="datatable-partisipant{{ $day }}"
                                        class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                                                <th style="width: 5%">No</th>
                                                <th class="w-125px">Avatar</th>
                                                <th>Nama</th>
                                                <th>Email</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-dark fw-semibold"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-sm btn-secondary"
                                    data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-danger">
                                    Batalkan Sekarang</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            @foreach ($schedules as $key => $schedule)
                <div class="border-buttom clone-wrapper{{ $day }}">
                    <div class="d-flex gap-1">
                        <div>
                            <div class="fv-row my-1">
                                <label>Mulai</label>
                                <input readonly type="time" class="form-control form-control-solid"
                                    value="{{ date('H:i', strtotime($schedule->start_time)) }}"
                                    name="personal_trainer_schedules[{{ $day }}][start_time][]" required />
                            </div>
                        </div>
                        <div>
                            <div class="fv-row my-1">
                                <label>Selesai</label>
                                <input readonly type="time" class="form-control form-control-solid"
                                    value="{{ date('H:i', strtotime($schedule->end_time)) }}"
                                    name="personal_trainer_schedules[{{ $day }}][end_time][]" required />
                            </div>
                        </div>
                        <div>
                            <div class="fv-row my-1">
                                <label>Kuota</label>
                                <input readonly type="text" class="form-control form-control-solid"
                                    value="{{ $schedule->quota }}"
                                    name="personal_trainer_schedules[{{ $day }}][quota][]" required />
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
            @if ($personalTrainer->personal_trainer_schedules()->where('day', $day)?->count() <= 0)
                <div class="d-flex h-75 mb-4 align-items-center justify-content-center">
                    <p class="text-center"><i>Tidak ada sesi hari ini</i></p>
                </div>
            @endif
        </div>
    @endforeach
</div>
@push('js')
    <script>
        function showModal(id) {
            $(".form-select").find('option').prop("selected", false);
            $(".form-select").trigger('change');
            $('#' + id).modal('show')
        }
        $(".select2").select2({
            placeholder: 'Pilih Sesi'
        });
        $(".select-all").on('click', function() {
            if ($(this).is(':checked')) { //select all
                $(".form-select").find('option').prop("selected", true);
                $(".form-select").trigger('change');
            } else { //deselect all
                $(".form-select").find('option').prop("selected", false);
                $(".form-select").trigger('change');
            }
        });

        $('#select2').on('change', function() {
            let selected = $(this).val();
            if (selected == '') {
                $(".select-all").prop('checked', false);
            }
        })
    </script>
    <script>
        function getPartisipant(day) {
            var table = $('#datatable-partisipant' + day).DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('personal-trainer-schedule.partisipant') }}",
                    type: 'GET',
                    data: {
                        is_not_canceled: 1,
                        date: $('#cancel_date_' + day).val(),
                        personal_trainer_schedule_id: $('#personal_trainer_schedule_id_' + day).val()
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
                        responsivePriority: -3,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'avatar',
                        name: 'avatar',
                        render: function(data, type, row) {
                            if (data == null) {
                                return `
                            <div class="symbol-label h-50px w-50px rounded-circle bg-light-primary d-flex justify-content-center align-items-center">
                                <span class="fs-2x fw-bold text-primary text-capitalize">
                                    ${row.name.charAt(0)}</span>
                            </div>`;
                            } else {
                                return `<img src="${data}" alt="image" class="h-50px object-fit-cover w-75px img-thumnail" />`;
                            }
                        }
                    },
                    {
                        data: 'name',
                        name: 'name',
                    },
                    {
                        data: 'email',
                        name: 'email',
                    },
                ]
            });
        }
    </script>
@endpush
