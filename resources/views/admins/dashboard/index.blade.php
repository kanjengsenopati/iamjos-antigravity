@extends('layouts.master', ['title' => 'Dashboard PHRI', 'main' => 'Dashboard'])
@push('css')
    <!-- Bootstrap Icons CDN -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <style>
        /* Custom Dashboard Styles */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .badge {
            font-weight: 500;
        }

        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }

        .message-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .chart-range-btn.active {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }

        .progress {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .min-width-0 {
            min-width: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .h2 {
                font-size: 1.5rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }

        /* Loading animation */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }

        .btn {
            border-radius: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
        }

        .card {
            border-radius: 1rem !important;
        }
    </style>
@endpush
@section('content')
    <div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    @if (!$isPasswordSafe)
                        <div class="container-fluid">
                            <div class="alert alert-danger fade show mt-4 mb-2" role="alert">
                                <strong>Perhatian!</strong> Password Anda tidak memenuhi kriteria keamanan. Segera ganti
                                password Anda
                                <a href="{{ route('profile-admin.edit') }}" class="text-primary">Ganti
                                    Password
                                    Sekarang</a>.
                            </div>
                        </div>
                    @endif
                    <!-- A. Baris 1 - 5 Stat Cards -->
                    <div class="row g-4 mb-4">
                        @foreach ($stats as $index => $stat)
                            <div class="col-12 col-sm-6 col-xl">
                                @if (isset($stat['route']))
                                    <a href="{{ route($stat['route']) }}" class="text-decoration-none">
                                        <div class="card shadow-sm border-0 rounded-3 h-100 card-hover">
                                        @else
                                            <div class="card shadow-sm border-0 rounded-3 h-100">
                                @endif
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-3 p-2">
                                            <i class="{{ $stat['icon'] }} text-primary fs-4"></i>
                                        </div>
                                        @if (isset($stat['route']))
                                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill"
                                                onclick="window.location.href='#'">
                                                <i class="bi bi-arrow-up-right me-1"></i>
                                                <span class="small">Kelola</span>
                                            </button>
                                        @endif
                                    </div>
                                    <h3 class="h2 fw-bold text-dark mb-1">{{ number_format($stat['value']) }}</h3>
                                    <p class="text-muted mb-2 small">{{ $stat['label'] }}</p>
                                    <span
                                        class="badge rounded-pill {{ $stat['positive'] ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger' }}">
                                        <i class="bi {{ $stat['positive'] ? 'bi-arrow-up' : 'bi-arrow-down' }} me-1"></i>
                                        {{ $stat['delta'] }}
                                    </span>
                                </div>
                            </div>
                            @if (isset($stat['route']))
                                </a>
                            @endif
                    </div>
                    @endforeach
                </div>

                <!-- B. Baris 2 - 2 Kolom Utama -->
                <div class="row g-4 mb-4">
                    <!-- B1. Card Daftar Hotel/Venue -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0 rounded-3 h-100">
                            <div class="card-header bg-transparent border-0 pt-4 pb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="card-title fw-bold mb-0">Daftar Hotel & Resort</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex gap-2 mt-3 mt-md-0">
                                            <select class="form-select form-select-sm" id="cityFilter">
                                                @foreach ($cities as $city)
                                                    <option value="{{ $city }}">{{ $city }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="table-responsive">
                                    <table id="hotelTable" class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th class="border-0 fw-semibold text-muted small w-50">Hotel</th>
                                                <th class="border-0 fw-semibold text-muted small">Kota</th>
                                                <th class="border-0 fw-semibold text-muted small">Kamar</th>
                                                <th class="border-0 fw-semibold text-muted small">Kapasitas</th>
                                                <th class="border-0 fw-semibold text-muted small text-center"
                                                    style="width: 80px;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($hotels as $hotel)
                                                <tr>
                                                    <td>
                                                        <h6 class="mb-0 fw-semibold">{{ $hotel['name'] }}</h6>
                                                    </td>
                                                    <td class="text-muted">{{ $hotel['city'] }}</td>
                                                    <td class="text-muted">{{ $hotel['rooms'] }} kamar</td>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-people-fill text-primary me-1"></i>
                                                            <span class="fw-semibold">{{ $hotel['max_capacity'] }}</span>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="{{ route('meeting-room.show', $hotel['id']) }}"
                                                            class="btn btn-sm btn-outline-secondary rounded-pill">
                                                            <i class="bi bi-eye me-1"></i> Lihat
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- B2. Card Pesan (Inbox) -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0 rounded-3 h-100">
                            <div class="card-header bg-transparent border-0 pt-4 pb-3">
                                <h5 class="card-title fw-bold mb-0">Pesan (Inbox)</h5>
                            </div>
                            <div class="card-body pt-0">
                                <!-- Summary Stats -->
                                <div class="row g-3 mb-4">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <h4 class="fw-bold text-primary mb-1">{{ $pesanSummary['total'] }}</h4>
                                            <p class="text-muted small mb-0">Total</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <h4 class="fw-bold text-warning mb-1">{{ $pesanSummary['pending'] }}</h4>
                                            <p class="text-muted small mb-0">Pending</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <h4 class="fw-bold text-success mb-1">{{ $pesanSummary['confirmed'] }}</h4>
                                            <p class="text-muted small mb-0">Confirmed</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Message List -->
                                <div class="message-list">
                                    @foreach ($pesanList as $pesan)
                                        <div
                                            class="p-3 mb-3 rounded-3 {{ $pesan['is_read'] ? 'bg-light' : 'bg-warning bg-opacity-25' }}">

                                            <h6 class="mb-1 fw-semibold">{{ $pesan['name'] ?? 'N/A' }}</h6>

                                            <p class="text-muted small mb-1">
                                                {{ Str::limit($pesan['message'] ?? 'N/A', 50) }}
                                            </p>

                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-muted small">
                                                    {{ \Carbon\Carbon::parse($pesan['created_at'])->diffForHumans() }}
                                                </span>
                                                <a href="{{ route('contact-us.index', $pesan['id']) }}"
                                                    class="btn btn-sm btn-outline-secondary rounded-pill">
                                                    <i class="bi bi-eye me-1"></i> Detail
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- C. Baris 3 - Card Analytics Ads -->
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card shadow-sm border-0 rounded-3">
                            <div class="card-header bg-transparent border-0 pt-4 pb-3">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="card-title fw-bold mb-3">Analytics Iklan</h5>
                                        <!-- KPI Mini Stats -->
                                        <div class="d-flex gap-4">
                                            <div>
                                                <h6 class="fw-bold text-primary mb-0" id="totalViews">
                                                    {{ number_format(array_sum($adsSeries['views'])) }}</h6>
                                                <small class="text-muted">Total Views</small>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-success mb-0" id="totalClicks">
                                                    {{ number_format(array_sum($adsSeries['clicks'])) }}</h6>
                                                <small class="text-muted">Total Clicks</small>
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-warning mb-0" id="ctrRate">
                                                    {{ number_format((array_sum($adsSeries['clicks']) / array_sum($adsSeries['views'])) * 100, 2) }}%
                                                </h6>
                                                <small class="text-muted">CTR</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex justify-content-md-end gap-2 mt-3 mt-md-0">
                                            <button class="btn btn-sm btn-outline-secondary chart-range-btn active"
                                                data-range="7">7 Hari</button>
                                            <button class="btn btn-sm btn-outline-secondary chart-range-btn"
                                                data-range="30">30 Hari</button>
                                            <button class="btn btn-sm btn-outline-secondary chart-range-btn"
                                                data-range="90">90 Hari</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body pt-0">
                                <!-- Chart Container -->
                                <div class="mb-4">
                                    <canvas id="adsChart" height="300"></canvas>
                                </div>
                                <div class="d-flex gap-4">
                                    <div>
                                        <h6 class="fw-bold text-primary mb-0">
                                            {{ number_format($adsStats['total_views'] ?? 0) }}</h6>
                                        <small class="text-muted">Total Views</small>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-success mb-0">
                                            {{ number_format($adsStats['total_clicks'] ?? 0) }}</h6>
                                        <small class="text-muted">Total Clicks</small>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold text-warning mb-0">
                                            {{ number_format($adsStats['ctr'] ?? 0, 2) }}%</h6>
                                        <small class="text-muted">CTR</small>
                                    </div>
                                </div>

                                <hr class="my-3" />

                                <h6 class="fw-semibold mb-3">Statistik Per Iklan</h6>

                                @forelse ($adsList as $ad)
                                    <div class="d-flex align-items-center mb-3">
                                        {{-- logo iklan / favicon / fallback --}}
                                        <div class="d-flex align-items-center me-3" style="min-width: 160px;">
                                            @if (!empty($ad['logo']))
                                                <img src="{{ $ad['logo'] }}" alt="{{ $ad['name'] }}" width="24"
                                                    height="24" class="rounded me-2">
                                            @else
                                                <div class="rounded bg-secondary d-inline-flex justify-content-center align-items-center me-2"
                                                    style="width:24px;height:24px;">
                                                    <i class="bi bi-image text-white" style="font-size:12px;"></i>
                                                </div>
                                            @endif

                                            <a href="{{ $ad['link'] }}" target="_blank" rel="noopener"
                                                class="small fw-semibold text-decoration-none">
                                                {{ $ad['name'] }}
                                            </a>
                                        </div>

                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-muted">Views</span>
                                                <span class="fw-semibold">{{ number_format($ad['views']) }}
                                                    ({{ $ad['share_views'] }}%)
                                                </span>
                                            </div>
                                            <div class="progress mb-2" style="height:8px;">
                                                <div class="progress-bar" style="width: {{ $ad['share_views'] }}%"></div>
                                            </div>

                                            <div class="d-flex justify-content-between small mb-1">
                                                <span class="text-muted">Clicks</span>
                                                <span class="fw-semibold">{{ number_format($ad['clicks']) }}
                                                    ({{ $ad['share_clicks'] }}%)</span>
                                            </div>
                                            <div class="progress" style="height:8px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: {{ $ad['share_clicks'] }}%"></div>
                                            </div>
                                        </div>

                                        <div class="ms-3 text-end" style="min-width:80px;">
                                            <div class="small text-muted">CTR</div>
                                            <div class="fw-bold">{{ number_format($ad['ctr'], 2) }}%</div>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-muted small">Belum ada data iklan aktif.</p>
                                @endforelse

                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Custom Dashboard Styles */
        .card-hover {
            transition: all 0.3s ease;
        }

        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .cursor-pointer {
            cursor: pointer;
        }

        .badge {
            font-weight: 500;
        }

        .bg-opacity-10 {
            --bs-bg-opacity: 0.1;
        }

        .message-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .chart-range-btn.active {
            background-color: var(--bs-primary);
            color: white;
            border-color: var(--bs-primary);
        }

        .progress {
            background-color: rgba(0, 0, 0, 0.1);
        }

        .min-width-0 {
            min-width: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .card-body {
                padding: 1rem;
            }

            .h2 {
                font-size: 1.5rem;
            }

            .table-responsive {
                font-size: 0.875rem;
            }
        }

        /* Loading animation */
        .loading {
            opacity: 0.6;
            pointer-events: none;
        }

        /* Smooth transitions */
        * {
            transition: all 0.2s ease;
        }

        .btn {
            border-radius: 0.5rem;
        }

        .form-control,
        .form-select {
            border-radius: 0.5rem;
        }

        .card {
            border-radius: 1rem !important;
        }
    </style>
@endpush

@push('js')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Data untuk chart
            const adsData = @json($adsSeries);

            // Initialize Chart
            const ctx = document.getElementById('adsChart').getContext('2d');
            let adsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: adsData.labels,
                    datasets: [{
                            label: 'Views',
                            data: adsData.views,
                            borderColor: '#0d6efd',
                            backgroundColor: 'rgba(13, 110, 253, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                        },
                        {
                            label: 'Clicks',
                            data: adsData.clicks,
                            borderColor: '#198754',
                            backgroundColor: 'rgba(25, 135, 84, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#ddd',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: true,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.parsed.y
                                        .toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)',
                            },
                            ticks: {
                                callback: function(value) {
                                    return value.toLocaleString();
                                },
                                font: {
                                    size: 11
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        }
                    },
                    elements: {
                        point: {
                            radius: 4,
                            hoverRadius: 6,
                        }
                    }
                }
            });

            // Chart Range Buttons
            document.querySelectorAll('.chart-range-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    document.querySelectorAll('.chart-range-btn').forEach(b => b.classList.remove(
                        'active'));
                    // Add active class to clicked button
                    this.classList.add('active');

                    // Update chart data based on range (dummy implementation)
                    const range = this.dataset.range;
                    let newData = adsData.views.slice();
                    let newClicks = adsData.clicks.slice();

                    if (range === '7') {
                        newData = adsData.views.slice(-7);
                        newClicks = adsData.clicks.slice(-7);
                        adsChart.data.labels = adsData.labels.slice(-7);
                    } else if (range === '30') {
                        newData = adsData.views.slice(-6);
                        newClicks = adsData.clicks.slice(-6);
                        adsChart.data.labels = adsData.labels.slice(-6);
                    } else {
                        newData = adsData.views;
                        newClicks = adsData.clicks;
                        adsChart.data.labels = adsData.labels;
                    }

                    adsChart.data.datasets[0].data = newData;
                    adsChart.data.datasets[1].data = newClicks;
                    adsChart.update();

                    // Update KPI numbers
                    updateKPIs(newData, newClicks);
                });
            });

            // Update KPI function
            function updateKPIs(views, clicks) {
                const totalViews = views.reduce((a, b) => a + b, 0);
                const totalClicks = clicks.reduce((a, b) => a + b, 0);
                const ctr = totalViews > 0 ? (totalClicks / totalViews * 100).toFixed(2) : 0;

                document.getElementById('totalViews').textContent = totalViews.toLocaleString();
                document.getElementById('totalClicks').textContent = totalClicks.toLocaleString();
                document.getElementById('ctrRate').textContent = ctr + '%';
            }

            // Date Range Dropdown
            document.querySelectorAll('[data-range]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    const range = this.dataset.range;
                    let rangeText = '';

                    switch (range) {
                        case '7':
                            rangeText = 'Last 7 Days';
                            break;
                        case '30':
                            rangeText = 'Last 30 Days';
                            break;
                        case '90':
                            rangeText = 'Last 90 Days';
                            break;
                    }

                    document.getElementById('selectedRange').textContent = rangeText;
                });
            });
            // Add loading state to cards when clicked
            document.querySelectorAll('.card-hover.cursor-pointer').forEach(card => {
                card.addEventListener('click', function() {
                    this.classList.add('loading');
                    setTimeout(() => {
                        this.classList.remove('loading');
                    }, 1000);
                });
            });

            // Smooth scroll for better UX
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Auto-refresh data every 5 minutes (placeholder)
            setInterval(function() {
                // Implement auto-refresh logic here
                console.log('Auto-refreshing dashboard data...');
            }, 5 * 60 * 1000);
        });
    </script>
    <script>
        $(document).ready(function() {
            const table = $('#hotelTable').DataTable({
                pageLength: 8,
                lengthChange: false,
                ordering: true,
                columnDefs: [{
                        orderable: false,
                        targets: [4]
                    }, // Aksi
                    {
                        width: "50%",
                        targets: 0
                    }, // Hotel
                    {
                        width: "10%",
                        targets: 4
                    } // Aksi
                ],
                language: {
                    search: "Cari:",
                    paginate: {
                        previous: "Sebelumnya",
                        next: "Berikutnya"
                    },
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ hotel",
                    infoEmpty: "Tidak ada data tersedia",
                    zeroRecords: "Tidak ditemukan hasil"
                }
            });

            // Pindahkan filter kota ke sebelah kotak search DataTables (kanan atas)
            const $city = $('#cityFilter'); // select yang sudah ada di header
            const $dtFilter = $('#hotelTable_wrapper .dataTables_filter');
            $dtFilter.addClass('d-flex align-items-center gap-2 flex-wrap'); // rapi

            // Biar ada label kecil "Kota:"
            const $wrapper = $('<div class="d-flex align-items-center gap-2 ms-2"></div>');
            $wrapper.append('<label class="mb-0 small text-muted">Kota:</label>');
            $wrapper.append($city.detach()); // pindahkan elemen select ke sini
            $dtFilter.append($wrapper);

            // Binding: filter kolom 1 (Kota). "Semua Kota" = reset.
            $city.on('change', function() {
                const v = $(this).val();
                if (v === 'Semua Kota') {
                    table.column(1).search('').draw();
                } else {
                    table.column(1).search('^' + $.fn.dataTable.util.escapeRegex(v) + '$', true, false)
                        .draw();
                }
            });
        });
    </script>
@endpush
