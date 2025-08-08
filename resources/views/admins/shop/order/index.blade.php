@extends('layouts.master', ['title' => 'Pesanan Shop', 'main' => 'Dashboard'])

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
		<div class="app-content flex-column-fluid" id="kt_app_content">
			<!--begin::Container-->
			<div id="kt_content_container" class="app-container container-xxl">
				<x-alert.alert-validation />
				<!--begin::Card-->
				<div class="card card-flush">
					<div class="card-header mt-6">
						<h3 class="card-title align-items-start flex-column">
							<span class="card-label fw-bold fs-3 mb-1">Daftar Pesanan Shop</span>
						</h3>
						<div class="d-flex align-items-center gap-2 gap-lg-3">
							<div class="me-4">
								@if(Auth::user()->is_show_all_gymplace)
								<select name="gym_place_id" id="gym_place_id" 
									class="form-select w-170px"
									data-control="select2" data-hide-search="true" data-dropdown-css-class="w-150px"
									data-placeholder="Pilih Gym Place" data-kt-table-widget-4="filter_status" onchange="table()">
									@foreach ($gym_places as $gym_place)
									<option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
									@endforeach
								</select>
								@else
								@php
									$userGymPlace = Auth::user()->gym_place;
								@endphp
								<select name="gym_place_id" id="gym_place_id" class="form-select w-170px" disabled>
									@if($userGymPlace)
										<option value="{{ $userGymPlace->id }}" selected>{{ $userGymPlace->name }}</option>
									@else
										<option value="">Tidak ada Gym Place</option>
									@endif
								</select>
								<input type="hidden" name="gym_place_id" value="{{ $userGymPlace->id ?? '' }}">
								@endif
							</div>
						</div>
					</div>
					<div class="card-body pt-0">
						<table id="datatable-shop-order"
							class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
							<thead>
								<tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
									<th style="width: 10%">No</th>
									<th>Kode Transaksi</th>
									<th>Nomor Pesanan</th>
									<th>Nama Pelanggan</th>
									<th>Total Harga</th>
									<th>Status</th>
									<th style="width: 10%">Aksi</th>
								</tr>
							</thead>
							<tbody class="text-dark fw-semibold"></tbody>
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
	$(document).ready(function() {
		$('#datatable-shop-order').DataTable();
	});

	var tableShopOrder = $('#datatable-shop-order').DataTable({
	ordering: true,
	processing: true,
	serverSide: true,
	responsive: true,
	destroy: true,
	ajax: {
	url: "{{ route('shop-order.index') }}",
	type: 'GET',
	data: function(d) {
	d.gym_place_id = $('#gym_place_id').val();
	},
	beforeSend: function() {
	$('#datatable-shop-order tbody').empty();
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
			data: 'transaction.payment_code',
			name: 'transaction.payment_code'
			},
			{
			data: 'order_number',
			name: 'order_number'
			},
			{
			data: 'user.name',
			name: 'user.name'
			},
			{
			data: 'transaction',
			name: 'transaction',
			render: function(data, type, row, meta) {
			const formatter = new Intl.NumberFormat('id-ID', {
			style: 'currency',
			currency: 'IDR'
			});
			return data?.pay_amount ? formatter.format(data.pay_amount) : 'Rp. 0';
			}
			},
			{
			data: 'status',
			name: 'status'
			},
			{
			data: 'action',
			name: 'action'
			}
			]
			});
	
			// Menyembunyikan tabel selama proses loading
			tableShopOrder.on('preXhr.dt', function(e, settings, data) {
			$('#datatable-shop-order tbody').empty();
			});
	
			// Menampilkan tabel setelah data selesai dimuat
			tableShopOrder.on('draw.dt', function() {
			$('#datatable-shop-order').fadeIn();
			});

			$('#gym_place_id').on('change', function() {
			tableShopOrder.ajax.reload();
			});
</script>
@endpush