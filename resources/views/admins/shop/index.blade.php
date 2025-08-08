@extends('layouts.master', ['title' => 'Shop', 'main' => 'Dashboard'])

@push('css')
<style>
    .w-170px {

        width: 170px;
    }
</style>
@endpush
@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <!--begin::Post-->
        <div class="d-flex flex-column-fluid" id="kt_post">
            <!--begin::Container-->
            <div id="kt_content_container" class="app-container container-xxl">
                <x-alert.alert-validation />
                <!--begin::Card-->
                <div class="card card-flush mt-6">
                    <div class="card-body">
                        @include('admins.shop.product.index')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@push('js')
<script>
    $(document).ready(function() {
        var table = $('#datatable-product').DataTable({
            ordering: true,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            columnDefs: [
                {"targets": 4}
            ],
            ajax: {
                url: "{{ route('shop-product.index') }}",
                type: 'GET',
                data: function(d) {
                    d.gym_place_id = $('#gym_place_id').val();
                },
                beforeSend: function() {
                    $('#datatable-product tbody').empty();
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
                {
                    "data": null,
                    "sortable": false,
                    "searchable": false,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'shop_product_images',
                    name: 'shop_product_images.image',
                    render: function(data, type, row, meta) {
                        if (row.shop_product_images && row.shop_product_images.length > 0) {
                            return `<img src="${row.shop_product_images[0].image}" alt="image" class="w-75px h-50px object-fit-cover" />`;
                        } else {
                            return '';
                        }
                    }
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'shop_category.name',
                    name: 'shop_category.name'
                },
                {
                    data: 'price',
                    name: 'price',
                },
                {
                    data: 'total_sold',
                    name: 'total_sold',
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        if (data) {
                            return `<span class="badge badge-light-success">di Publish</span>`;
                        } else {
                            return `<span class="badge badge-light-warning">di Draft</span>`;
                        }
                    },
                },
                {
                    data: 'action',
                    name: 'action'
                }
            ]
        });
        
        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable-product tbody').empty();
        });
        
        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable-product').fadeIn();
        });

        $('#gym_place_id').on('change', function() {
            table.ajax.reload();
        });
    });

   
</script>
@endpush