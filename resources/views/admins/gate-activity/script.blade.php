@push('js')
<script>
    $(function() {
        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#dateRange span').html(start.format('D/MM/YYYY') + ' - ' + end.format('D/MM/YYYY'));
            var start = start.format('YYYY-MM-DD');
            var end = end.format('YYYY-MM-DD');
            $('#start_date').val(start);
            $('#end_date').val(end);

            table();
        }

        $('#dateRange').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Hari Ini': [moment(), moment()],
                'Kemarin': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Bulan Ini': [moment().startOf('month'), moment().endOf('month')],
                'Bulan Kemarin': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                '7 Hari Terakhir': [moment().subtract(6, 'days'), moment()],
                '30 Hari Terakhir': [moment().subtract(29, 'days'), moment()],
                'Tahun Ini': [moment().startOf('year'), moment().endOf('year')],
                'Tahun Kemarin': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')],
            }
        }, cb);
        cb(start, end);
    });
</script>
<script>
    function updateReason(id) {
        axios.get("{{ route('gate-activity.get-reason', ':id') }}".replace(':id', id))
        .then(function(response) {
            console.log(response);
            document.getElementById('reason').value = response.data.reason;
        })
        .catch(function(error) {
            toastr.error(error.message)
        });

        $('#modal-update-reason').modal('show');
        document.getElementById('gate_activity_id').value = id;
    }

    $('select').on('change', function() {
        table();
    })

    function table() {
        var table = $('#datatable').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('gate-activity.index') }}",
                type: 'GET',
                data: {
                    start_date: $("#start_date").val(),
                    end_date: $("#end_date").val(),
                    status: $('#status').val()
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
                    data: 'gate_number',
                    name: 'gate_number',
                    render: function(data, type, row) {
                        return data ? data : '-';
                    },
                },
                {
                    data: 'activity_at',
                    name: 'activity_at',
                    responsivePriority: -1
                },
                {
                    data: 'activity',
                    name: 'activity',
                },
                {
                    data: 'status_membership',
                    name: 'status_membership',
                    responsivePriority: -1
                },
                {
                    data: 'status',
                    name: 'status',
                    responsivePriority: -1
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    responsivePriority: -1
                },
            ]
        });

        axios.get("{{ route('gate-activity.index') }}", {
            params: {
                updateStatus: true,
                start_date: $("#start_date").val(),
                end_date: $("#end_date").val(),
                status: $('#status').val()
            }
        }).then(function(response) {
            document.getElementById('total_checkin').innerHTML = response.data.check_in;
            document.getElementById('total_checkout').innerHTML = response.data.check_out;
            document.getElementById('total_activity').innerHTML = response.data.total_activity;
            document.getElementById('total_failed').innerHTML = response.data.total_failed;
        });
    }
    </script>
@endpush