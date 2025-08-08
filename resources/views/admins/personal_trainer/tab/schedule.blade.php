<div class="d-flex flex-wrap gap-3 align-items-center">
    <div>
        <a type="button" class="btn btn-light-danger btn-sm mt-3" id="btnDeleteSelected">
            <i class="ki-duotone btn-delete ki-basket fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>Delete Selected
        </a>
    </div>
    <div class="ms-auto d-flex align-items-center">
        <div class="me-3">
            <x-form.month-date-range-filter />
            <input type="text" id="start_date" hidden>
            <input type="text" id="end_date" hidden>
        </div>
        <a type="button" class="btn btn-primary btn-sm mt-3"
            href="{{ route('coach-schedule.create', ['personal_trainer_id' => $personalTrainer->id]) }}">
            <i class="fa fa-plus"></i> Jadwal
        </a>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-schedule" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th style="width: 3%"><input type="checkbox" onchange="checkAll(this)"></th>
                <th style="width: 5%">No</th>
                <th style="width: 15%">Tanggal</th>
                <th>Waktu Mulai</th>
                <th>Waktu Selesai</th>
                <th>Kouta</th>
                <th style="width: 8%">Aksi</th>
            </tr>
        </thead>
    </table>
</div>

@push('js')
<script>
    function checkAll(source) {
        checkboxes = document.getElementsByName('schedule_id[]');
        for (var i = 0; i < checkboxes.length; i++) {
            checkboxes[i].checked = source.checked;
        }
    }   

    function table() {
        var tableSchedule = $('#datatable-schedule').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            pageLength: 50,
            ajax: {
                url: "{{ route('coach-schedule.index') }}",
                type: 'GET',
                data: {
                    personal_trainer_id: "{{ $personalTrainer->id }}",
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
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
            columns: [
                { data: 'checkbox', name: 'checkbox', orderable: false, searchable: false },  
                {
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
                    responsivePriority: -1,
                },
                {
                    data: 'start_time',
                    name: 'start_time',
                },
                {
                    data: 'end_time',
                    name: 'end_time',
                },
                {
                    data: 'quota',
                    name: 'quota',
                },
                {
                    data: 'action',
                    name: 'action',
                    searchable: false,
                    sortable: false,
                    responsivePriority: -1,
                },
            ]
        });

        
        $('#btnDeleteSelected').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Apakah Anda Yakin?",
                text: "Anda akan menghapus Jadwal Coach yang dipilih ?",
                icon: "warning",
                input: 'textarea',
                inputPlaceholder: 'Masukkan alasan Anda...',
                inputAttributes: {
                    'name': 'reason',
                    'id': 'reason',
                    'required': 'required'
                },
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal",
            }).then((result) => {
                if (result.isConfirmed) {
                    handleButtonClick(result.value);
                }
            });
        });

        function handleButtonClick(reason) {
            const rows = tableSchedule.rows({ search: 'applied' }).nodes();
            const selectedValues = $(rows).find('input[type="checkbox"]:checked').map((_, el) => $(el).val()).get();

            $.ajax({
                url: '{{ route("coach-schedule.multiple-destroy") }}',
                method: 'POST',
                data: { 
                    schedule_id: selectedValues,
                    reason: reason,
                },
                success: function(response) {
                    tableSchedule.ajax.reload();
                    Swal.fire({
                        title: 'Sukses!',
                        text: response,
                        icon: 'success',
                        confirmButtonText: 'Tutup'
                    });
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        }
    }

    $(document).on('click', '.btn-delete-schedule', function(e) {
        var form = $("#" + e.target.dataset.id);
        Swal.fire({
            title: 'Hapus Data',
            text: 'Anda yakin akan menghapus data ini? Data yang telah dihapus tidak dapat dikembalikan.',
            icon: "warning",
            input: 'textarea',
            inputPlaceholder: 'Masukkan alasan Anda...',
            inputAttributes: {
                'name': 'reason',
                'id': 'reason',
                'required': 'required'
            },
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal',
            customClass: {
                confirmButton: 'btn btn-sm fw-semibold btn-primary',
                cancelButton: 'btn btn-sm fw-semibold btn-active-light-primary'
            }
        }).then((res) => {
            if (res.isConfirmed) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'reason',
                    value: res.value
                }).appendTo(form);
                form.submit();
            } else {
                return false;
            }
        });
        return false;
    });
</script>
@endpush