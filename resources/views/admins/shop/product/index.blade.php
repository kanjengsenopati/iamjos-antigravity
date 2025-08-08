<div class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
    <h3 class="card-title align-items-start flex-column">
        <span class="card-label fw-bold fs-3 mb-1">Daftar Produk</span>
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
        <a type="button" class="btn btn-primary btn-sm btn-create" href="{{ route('shop-product.create') }}">
            <i class="fa fa-plus"></i>
            Produk</a>
    </div>
</div>
<div class="table-responsive">
    <table id="datatable-product" class="table table-hover align-start table-row-dashed fs-6 gy-5 mb-0">
        <thead>
            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                <th style="width: 5%">No</th>
                <th>Image</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Total Terjual</th>
                <th>Status</th>
                <th style="width: 10%">Aksi</th>
            </tr>
        </thead>
        <tbody class="text-dark fw-semibold"></tbody>
    </table>
</div>
