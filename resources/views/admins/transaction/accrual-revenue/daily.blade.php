@extends('layouts.master', ['title' => 'Accrual Revenue', 'main' => 'Dashboard'])

@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Accrual Revenue</h3>
                    </div>
                    <div class="card-body">
                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_main">Data Pendapatan</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_export">Riwayat Export</a>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="myTabContent">
                            <!-- Tab 1: Data Pendapatan -->
                            <div class="tab-pane fade show active" id="kt_tab_main" role="tabpanel">
                                <!-- Filter Section -->
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <select id="monthSelect" class="form-select me-2">
                                                <option value="0">Januari</option>
                                                <option value="1">Februari</option>
                                                <option value="2">Maret</option>
                                                <option value="3">April</option>
                                                <option value="4">Mei</option>
                                                <option value="5">Juni</option>
                                                <option value="6">Juli</option>
                                                <option value="7">Agustus</option>
                                                <option value="8">September</option>
                                                <option value="9">Oktober</option>
                                                <option value="10">November</option>
                                                <option value="11">Desember</option>
                                            </select>
                                            <select id="yearSelect" class="form-select">
                                                <!-- Tahun akan di-generate oleh JavaScript -->
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exportModal">
                                            <i class="fas fa-file-export me-2"></i>Export Excel
                                        </button>
                                    </div>
                                </div>

                                <!-- Total Revenue Cards -->
                                <div class="row mb-5">
                                    <div class="col-md-6">
                                        <div class="card bg-light-primary">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h4 class="text-primary mb-1">Total Pendapatan Cash</h4>
                                                        <h2 class="mb-0" id="totalCashRevenue">Rp 0</h2>
                                                    </div>
                                                    <div class="symbol symbol-50px">
                                                        <span class="symbol-label bg-primary">
                                                            <i class="fas fa-money-bill-wave text-white fs-2x"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="col-md-6">
                                        <div class="card bg-light-danger">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-grow-1">
                                                        <h4 class="text-danger mb-1">Total Pendapatan Accrual</h4>
                                                        <h2 class="mb-0" id="totalAccrualRevenue">Rp 0</h2>
                                                    </div>
                                                    <div class="symbol symbol-50px">
                                                        <span class="symbol-label bg-danger">
                                                            <i class="fas fa-chart-line text-white fs-2x"></i>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div> --}}
                                </div>

                                <!-- Chart Section -->
                                <div class="row mb-5">
                                    <div class="col-12">
                                        <div class="card">
                                            <div class="card-header">
                                                <h4 class="card-title">Grafik Pendapatan</h4>
                                            </div>
                                            <div class="card-body">
                                                <div id="chartContainer" style="position: relative;">
                                                    <div id="loadingSpinner" class="text-center" style="display: none;">
                                                        <div class="spinner-border text-primary" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </div>
                                                    <canvas id="revenueChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab 2: Riwayat Export -->
                            <div class="tab-pane fade" id="kt_tab_export" role="tabpanel">
                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-title">Riwayat Export File</h4>
                                    </div>
                                    <div class="card-body">
                                        <table id="datatable-accrual" class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th style="width: 5%">No</th>
                                                    <th>Tanggal Export</th>
                                                    <th>Nama File</th>
                                                    <th>Tempat Gym</th>
                                                    <th>Status</th>
                                                    <th>Selesai Export</th>
                                                    <th>Berkas</th>
                                                    <th>Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Data Accrual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('accrual-revenue.daily.export-excel') }}" method="GET">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Tahun</label>
                        <select class="form-select" id="gym_place_id" name="gym_place_id" >
                            <option value="">Semua Tempat Gym</option>
                            @foreach ($gym_places as $gymPlace)
                                <option value="{{ $gymPlace->id }}">{{ $gymPlace->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Bulan</label>
                        <input type="month" class="form-control" name="year_month" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Export</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    tableAccrual();
    // Inisialisasi pemilihan bulan dan tahun
    const monthSelect = document.getElementById('monthSelect');
    const yearSelect = document.getElementById('yearSelect');
    const currentYear = new Date().getFullYear();
    
    // Generate opsi tahun
    for (let year = currentYear; year >= 2020; year--) {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    }

    // Set nilai default ke bulan dan tahun saat ini
    monthSelect.value = new Date().getMonth();
    yearSelect.value = currentYear;

    // Inisialisasi chart
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [
                {
                    label: 'Cash Revenue Membership/Others',
                    data: [],
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: 'rgba(0, 123, 255, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Cash Revenue Coach',
                    data: [],
                    backgroundColor: 'rgba(255, 0, 0, 0.6)',
                    borderColor: 'rgba(255, 0, 0, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Cash Revenue Total',
                    data: [],
                    backgroundColor: 'rgba(255, 255, 0, 0.6)',
                    borderColor: 'rgba(255, 255, 0, 1)',
                    borderWidth: 1
                },
                // {
                //     label: 'Accrual Revenue Membership/Others',
                //     data: [],
                //     backgroundColor: 'rgba(75, 0, 130, 0.6)',
                //     borderColor: 'rgba(75, 0, 130, 1)',
                //     borderWidth: 1
                // },
                // {
                //     label: 'Accrual Revenue Coach',
                //     data: [],
                //     backgroundColor: 'rgba(255, 165, 0, 0.6)',
                //     borderColor: 'rgba(255, 165, 0, 1)',
                //     borderWidth: 1
                // },
                // {
                //     label: 'Accrual Revenue Total',
                //     data: [],
                //     backgroundColor: 'rgba(34, 139, 34, 0.6)',
                //     borderColor: 'rgba(34, 139, 34, 1)',
                //     borderWidth: 1
                // }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'CASH REVENUE'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal'
                    }
                }
            }
        }
    });

    // Fungsi untuk mendapatkan data dari controller
    async function getDataByMonth(month, year) {
        try {
            const response = await axios.get("{{ route('accrual-revenue.daily.chart') }}", {
                params: {
                    month: month,
                    year: year
                }
            });
            return response.data;
        } catch (error) {
            console.error('Error fetching data:', error);
            throw error;
        }
    }

    // Fungsi untuk update data
    async function updateData() {
        const month = monthSelect.value;
        const year = yearSelect.value;
        
        document.getElementById('loadingSpinner').style.display = 'block';

        try {
            const data = await getDataByMonth(month, year);
            
            // Update total revenue
            const totalCash = data.datasets[2].reduce((a, b) => a + b, 0);
            // const totalAccrual = data.datasets[5].reduce((a, b) => a + b, 0);
            
            document.getElementById('totalCashRevenue').textContent = 'Rp ' + totalCash.toLocaleString('id-ID');
            // document.getElementById('totalAccrualRevenue').textContent = 'Rp ' + totalAccrual.toLocaleString('id-ID');
            
            // Update chart
            revenueChart.data.labels = data.labels;
            revenueChart.data.datasets.forEach((dataset, index) => {
                dataset.data = data.datasets[index];
            });
            revenueChart.update();
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data');
        } finally {
            document.getElementById('loadingSpinner').style.display = 'none';
        }
    }

    // Event listener untuk perubahan bulan dan tahun
    monthSelect.addEventListener('change', updateData);
    yearSelect.addEventListener('change', updateData);

    // Load data awal
    updateData();

    // Contoh implementasi export
    document.querySelector('form[action="#"]')
        .addEventListener('submit', function(e) {
            e.preventDefault();
            
            const startMonth = this.querySelector('[name="start_month"]').value;
            const endMonth = this.querySelector('[name="end_month"]').value;

            if (!startMonth || !endMonth) {
                alert('Mohon isi bulan mulai dan bulan akhir');
                return;
            }

            if (endMonth < startMonth) {
                alert('Bulan akhir tidak boleh kurang dari bulan mulai');
                return;
            }

            // Untuk contoh, kita tampilkan pesan sukses
            alert('Data berhasil diexport!');
            $('#exportModal').modal('hide');
        });
});

    // Fungsi untuk download file (contoh)
    function downloadFile(fileName) {
        alert(`Download file: ${fileName}`);
    }

    // Fungsi untuk delete file (contoh)
    function deleteFile(fileName) {
        if (confirm(`Apakah Anda yakin ingin menghapus file ${fileName}?`)) {
            alert(`File ${fileName} berhasil dihapus`);
        }
    }

    function tableAccrual() {
        var table = $('#datatable-accrual').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('accrual-revenue.index') }}"
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
                    data: 'created_at',
                    name: 'created_at',
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'gym_place',
                    name: 'gym_place_id',
                    render: function(data, type, row) {
                        return data ? data.name : 'Semua Gym Place';
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'completed_at',
                    name: 'completed_at',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'file',
                    name: 'file',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                }
            ]
        });
    }

</script>
@endpush
