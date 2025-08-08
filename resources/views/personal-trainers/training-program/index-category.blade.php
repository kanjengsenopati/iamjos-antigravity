@extends('layouts.pt-master', ['title' => 'Kategori Movement'])

@section('content')
<div class="content pt-6 d-flex flex-column flex-column-fluid" id="kt_content">
    <div id="kt_content_container" class="app-container container-xxl">
        <div class="row g-7">
            <div class="col-xl-12">
                <div class="card h-lg-100">
                    <div class="card-header">
                        <h3 class="card-title align-items-start flex-column">Kategori Movement</h3>
                        <div class="card-toolbar">
                            <a href="{{ route('trainer.category-movement.create') }}" class="btn btn-primary">Tambah Kategori</a>
                        </div>
                    </div>
                    <div class="card-body pt-5">
                        <table class="table table-striped" id="categories-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama</th>
                                    <th>Jumlah Movement</th> <!-- Kolom baru -->
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(() => {
        var table = $('#categories-table').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('trainer.category-movement.index') }}",
            },
            language: {
                "paginate": {
                    "next": "<i class='fa fa-angle-right'>",
                    "previous": "<i class='fa fa-angle-left'>"
                },
                "loadingRecords": "Loading...",
                "processing": "Processing...",
            },
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            columns: [
                {
                    data: null,
                    sortable: false,
                    searchable: false,
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        // Mengembalikan nomor urut
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -2,
                },
                {
                    data: 'movement_count', // menggunakan field baru untuk count movement
                    name: 'movement_count',
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
    });
</script>
@endpush