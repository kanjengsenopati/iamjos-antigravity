<div class="d-flex flex-wrap gap-3 align-items-center justify-content-end">
    <div>
        <x-form.date-range-filter />
        <input type="text" id="start_date" hidden>
        <input type="text" id="end_date" hidden>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-health-information" class="table table-striped border rounded gy-5 gs-7">
        <thead>
            <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
                <th>No</th>
                <th style="width: 8%">Tanggal</th>
                <th>Body Age</th>
                <th>Tinggi (Cm)</th>
                <th>Weight (Kg)</th>
                <th>Muscle Mass (%)</th>
                <th>Visceral Fat</th>
                <th>Fat Percentage (%)</th>
                <th>BMI (%)</th>
                <th>BMR (Kcal)</th>
                <th>Metabolic Age</th>
                <th>Measurement Score</th>
                <th>Change Tinggi (Cm)</th>
                <th>Change Weight (Kg)</th>
                <th>Change Muscle Mass (kg)</th>
                <th>Change Visceral Fat</th>
                <th>Change Fat Percentage (%)</th>
                <th>Change BMI (%)</th>
                <th>Change BMR (Kcal)</th>
                <th>Change Metabolic Age</th>
                <th>Change Measurement Score</th>
            </tr>
        </thead>
    </table>
</div>

@push('js')
    <script>
        function table() {
            var tableHealthInformation = $('#datatable-health-information').DataTable({
                ordering: false,
                processing: true,
                serverSide: true,
                responsive: true,
                destroy: true,
                ajax: {
                    url: "{{ route('health-information.index') }}",
                    type: 'GET',
                    data: {
                        user_id: "{{ $user->id }}",
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
                        data: 'body_age',
                        name: 'body_age'
                    },
                    {
                        data: 'height',
                        name: 'height'
                    },
                    {
                        data: 'weight',
                        name: 'weight'
                    },
                    {
                        data: 'muscle_mass',
                        name: 'muscle_mass'
                    },
                    {
                        data: 'visceral_fat',
                        name: 'visceral_fat'
                    },
                    {
                        data: 'fat_percentage',
                        name: 'fat_percentage'
                    },
                    {
                        data: 'bmi',
                        name: 'bmi'
                    },
                    {
                        data: 'bmr',
                        name: 'bmr'
                    },
                    {
                        data: 'metabolic_age',
                        name: 'metabolic_age'
                    },
                    {
                        data: 'measurement_score',
                        name: 'measurement_score'
                    },
                    {
                        data: 'change_height',
                        name: 'change_height'
                    },
                    {
                        data: 'change_weight',
                        name: 'change_weight'
                    },
                    {
                        data: 'change_muscle_mass',
                        name: 'change_muscle_mass'
                    },
                    {
                        data: 'change_visceral_fat',
                        name: 'change_visceral_fat'
                    },
                    {
                        data: 'change_fat_percentage',
                        name: 'change_fat_percentage'
                    },
                    {
                        data: 'change_bmi',
                        name: 'change_bmi'
                    },
                    {
                        data: 'change_bmr',
                        name: 'change_bmr'
                    },
                    {
                        data: 'change_metabolic_age',
                        name: 'change_metabolic_age'
                    },
                    {
                        data: 'change_measurement_score',
                        name: 'change_measurement_score'
                    },
                ]
            });
        }
    </script>
@endpush
