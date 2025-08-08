@extends('layouts.master', ['main' => 'Tempat Gym', 'title' => request()->routeIs('gym-place.create') ? 'Tambah Tempat Gym' : 'Edit Tempat Gym'])
@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

<!-- Load Esri Leaflet from CDN -->
<script src="https://unpkg.com/esri-leaflet@3.0.0/dist/esri-leaflet.js"></script>
<script src="https://unpkg.com/esri-leaflet-vector@3.0.0/dist/esri-leaflet-vector.js"></script>


<!-- Load Esri Leaflet Geocoder from CDN -->
<link rel="stylesheet" href="https://unpkg.com/esri-leaflet-geocoder@3.0.0/dist/esri-leaflet-geocoder.css">
<script src="https://unpkg.com/esri-leaflet-geocoder@3.0.0/dist/esri-leaflet-geocoder.js"></script>


<style type="text/css">
    .pointer {
        position: absolute;
        top: 90px;
        right: 60px;
        z-index: 99999;
        display: none;
    }

    .cardid {
        position: relative;
        /* width: 148px; */
        margin-bottom: 10px;
    }

    .go-corner {
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        width: 32px;
        height: 32px;
        overflow: hidden;
        top: 5px;
        right: 0px;
        background-color: #ff0404;
        border-radius: 0 4px 0 32px;
    }

    .go-arrow {
        margin-top: -4px;
        margin-right: -4px;
        color: white;
        font-family: courier, sans;
    }
</style>
@endpush
@section('content')
<!--begin::Content wrapper-->
<div class="d-flex pt-6 flex-column flex-column-fluid">
    <!--begin::Content-->
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <!--begin::Content container-->
        <div id="kt_app_content_container" class="app-container container-xxl">
            <!--begin::Basic info-->
            <div class="card mb-5 mb-xl-10">
                <!--begin::Card header-->
                <div class="card-header">
                    <!--begin::Card title-->
                    <div class="card-title m-0">
                        <h3 class="fw-bold m-0">{{ request()->routeIs('gym-place.create') ? 'Tambah Tempat Gym' : 'Edit Tempat Gym'
                            }}
                        </h3>
                    </div>
                    <!--end::Card title-->
                </div>
                <!--begin::Card header-->
                <!--begin::Content-->
                <div id="kt_account_settings_profile_details" class="collapse show">
                     <!--begin::Form-->
                    <form class="form" method="POST" enctype="multipart/form-data" action="{{ request()->routeIs('gym-place.create') ? route('gym-place.store') : route('gym-place.update', $gymPlace->id) }}">
                        @csrf
                        <x-form.put-method />
                        <x-alert.alert-validation />
                        <!--begin::Card body-->
                        <div class="card-body">
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nama Gym</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="text" name="name" id="name"
                                                class="form-control form-control-lg mb-3 mb-lg-0"
                                                value="{{ @$gymPlace->name ?? old('name') }}" required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Kode Awalan Kode Member (Prefix Code)</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="text" name="prefix_code" id="prefix_code"
                                                class="form-control form-control-lg mb-3 mb-lg-0" placeholder="NGPIK"
                                                value="{{ @$gymPlace->prefix_code ?? old('prefix_code') }}" required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Nomor Telepon</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <input type="text" name="phone" id="phone"
                                                class="form-control form-control-lg mb-3 mb-lg-0"
                                                value="{{ @$gymPlace->phone ?? old('phone') }}" required />
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6">
                                <div class="col-4">
                                    <label class="col-form-label required fw-semibold fs-6">
                                        Kode Transaksi Pajak
                                        <i class="fas fa-info-circle ms-1 fs-7"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Kode awalan untuk transaksi yang dikenakan pajak (contoh: nest-gym-pik)"></i>
                                    </label>
                                    <div class="row">
                                        <div class="col-lg-12 fv-row">
                                            <input type="text" name="transaction_code_tax" id="transaction_code_tax"
                                                class="form-control form-control-lg mb-3 mb-lg-0" placeholder="nest-gym-pik"
                                                value="{{ @$gymPlace->transaction_code_tax ?? old('transaction_code_tax') }}" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <label class="col-form-label required fw-semibold fs-6">
                                        Ambil Transaksi untuk Pajak
                                        <i class="fas fa-info-circle ms-1 fs-7"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Jumlah transaksi berturut-turut yang akan dikenakan pajak (contoh: 3 transaksi pertama)"></i>
                                    </label>
                                    <div class="row">
                                        <div class="col-lg-12 fv-row">
                                            <input type="number" name="take_transaction_for_tax" id="take_transaction_for_tax"
                                                class="form-control form-control-lg mb-3 mb-lg-0" placeholder="3"
                                                value="{{ @$gymPlace->take_transaction_for_tax ?? old('take_transaction_for_tax') }}" required />
                                        </div>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <label class="col-form-label required fw-semibold fs-6">
                                        Batas Transaksi untuk Pajak
                                        <i class="fas fa-info-circle ms-1 fs-7"
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        title="Total transaksi dalam satu batch sebelum siklus pajak diulang (contoh: setiap 5 transaksi)"></i>
                                    </label>
                                    <div class="row">
                                        <div class="col-lg-12 fv-row">
                                            <input type="number" name="limit_transaction_for_tax" id="limit_transaction_for_tax"
                                                class="form-control form-control-lg mb-3 mb-lg-0" placeholder="5"
                                                value="{{ @$gymPlace->limit_transaction_for_tax ?? old('limit_transaction_for_tax') }}" required />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Alamat</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <textarea class="form-control form-control-lg mb-3 mb-lg-0" id="address" name="address" required>{{ old('address',@$gymPlace->address) }}</textarea>
                                            <input type="text" id="latitude" value="{{@$gymPlace->latitude}}" name="latitude" hidden>
                                            <input type="text" id="longitude" value="{{@$gymPlace->longitude}}" name="longitude" hidden>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div id="map" style="height: 400px"></div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Deskripsi</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="col-lg-12">
                                    <!--begin::Row-->
                                    <div class="row">
                                        <!--begin::Col-->
                                        <div class="col-lg-12 fv-row">
                                            <textarea class="form-control form-control-lg mb-3 mb-lg-0" id="description" name="description" required>{{ old('description',@$gymPlace->description) }}</textarea>
                                        </div>
                                        <!--end::Col-->
                                    </div>
                                    <!--end::Row-->
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="description_en">
                                    <span class="required">Deskripsi (English)</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <div class="row">
                                    <div class="col-10">
                                        <textarea class="form-control form-control-lg mb-3 mb-lg-0" id="description_en" name="description_en" required>{{ old('description_en',@$gymPlace->description_en) }}</textarea>
                                    </div>
                                    <div class="col-2">
                                        <a onclick="translateDescriptionEnglish()" class="btn btn-translate">Translate English</a>
                                    </div>
                                </div>
                                <!--end::Input-->
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="description_cn">
                                    <span class="required">Deskripsi (chinese)</span>
                                    <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                        title="Input Deskripsi"></i>
                                </label>
                                <!--end::Label-->
                                <!--begin::Input-->
                                <div class="row">
                                    <div class="col-10">
                                        <textarea class="form-control form-control-lg mb-3 mb-lg-0" id="description_cn" name="description_cn" required>{{ old('description_cn',@$gymPlace->description_cn) }}</textarea>
                                    </div>
                                    <div class="col-2">
                                        <a onclick="translateDescriptionChinese()" class="btn btn-translate">Translate Chinese</a>
                                    </div>
                                </div>
                                <!--end::Input-->
                            </div>
                            <div class="card py-2 px-4 bg-light-warning hoverable card-xl-stretch mt-4 mb-2">
                                    <p class="text-warning">
                                        <span class="fw-bold">Perhatian!</span> Isi jadwal operasional sesuai dengan jadwal operasional (Jam buka - tutup) masing - masing tempat GYM, kosongkan hari dan jam jika merupakan hari libur .
                                    </p>
                            </div>
                            <div class="row mb-4 me-4">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Jadwal Operasional</label>
                                <!--end::Label-->
                                <div class="table-responsive">
                                    <table class="table mx-4">
                                        <thead>
                                            <tr>
                                                <th style="w-100px">Hari</th>
                                                <th>Jam Buka</th>
                                                <th>Jam Tutup</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($days as $day)
                                            <tr>
                                                <td><input type="text" class="form-control w-100px" value="{{$day}}" name="gym_place_operationals[{{$day}}][day]"></td>
                                                <td><input type="time" class="form-control" value="{{@$gymPlace?->gym_place_operationals()?->where('day', $day)?->first()?->opening_time}}" name="gym_place_operationals[{{$day}}][opening_time]"></td>
                                                <td><input type="time" class="form-control" value="{{@$gymPlace?->gym_place_operationals()?->where('day', $day)?->first()?->closing_time}}" name="gym_place_operationals[{{$day}}][closing_time]"></td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Galeri</label>
                                <!--end::Label-->
                                <!--begin::Col-->
                                <div class="row col-lg-12">
                                    @php
                                        $no =1;
                                    @endphp
                                    @foreach (@$gymPlace?->gym_place_galeries ?? [] as $galery)
                                    <div class="col-sm-3">
                                        <div class="cardid">
                                            <img src="{{ asset($galery->image) }}" class="m-1 img img-thumbnail" alt="Thumbnail{{ $no }}">
                                            <div class="go-corner">
                                                <div class="go-arrow">
                                                    <a class="btn btn-danger mx-auto mx-md-0 text-white" onclick="myFunctiondelete('{{ $galery->id }}')">X</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- <div class="col-sm-3">
                                        <img src="{{asset($galery->image)}}" alt="" class="m-1 img img-thumbnail" />
                                    </div> --}}
                                    @endforeach
                                </div>
                                <div class="px-4">
                                    <x-form.file-pound />
                                </div>
                                <!--end::Col-->
                            </div>
                            <div class="row mb-6 px-4">
                                <!--begin::Label-->
                                <label class="col-lg-4 col-form-label required fw-semibold fs-6">Status</label>
                                <!--end::Label-->
                                <select name="is_active" id="is_active" class="form-control" required>
                                    <option value="">--Pilih Tipe Diskon--</option>
                                    <option {{@$gymPlace->is_active == 1 ? 'selected' : ''}} value="1">AKTIF</option>
                                    <option {{@$gymPlace->is_active == 0 ? 'selected' : ''}} value="0">NON AKTIF</option>
                                </select>
                            </div>
                        </div>
                        <!--begin::Actions-->
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('gym-place.index') }}"
                                class="btn btn-secondary btn-sm me-2">Batal</a>
                            <button type="submit" class="btn btn-sm btn-primary"
                                id="kt_account_profile_details_submit">Simpan</button>
                        </div>
                        <!--end::Actions-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Basic info-->
        </div>
        <!--end::Content container-->
    </div>
    <!--end::Content-->
</div>
<!--end::Content wrapper-->

{{-- form delete galeri --}}
<form action="#" id="delete_galeri" method="post">
@csrf
</form>

@endsection
@push('js')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.forEach(function (tooltipTriggerEl) {
            new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<script text="text/javascript">
    let latitude = <?= @$gymPlace->latitude ?? '-6.175389999999936' ?>;
    let longitude = <?= @$gymPlace->longitude ?? '106.82704000000007' ?>;

    function validationLocation() {
        var form_lng = $('#latitude').val();
        if (form_lng.length < 3) {
            Swal.fire('Gagal menyimpan', 'Silakan Melengkapi Titik Lokasi Kantor Terlebih Dahulu', 'info');
        }
    }

    const apiKey = "AAPKb22a3f2a79c44e7faf92f7c2175410835pyhwZf9KZfY4WtfUz8bLzwmHltYsHcsY2QYuJz_JPvBfKeddWZmRc1Ecfmo4DeT";
    const basemapEnum = "ArcGIS:Navigation";
    const map = L.map("map", {
        minZoom: 2

    }).setView([latitude, longitude], 14);

    L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors',
        maxZoom: 18
    }).addTo(map);

    const searchControl = L.esri.Geocoding.geosearch({
        position: "topright",
        placeholder: "Cari Lokasi anda",
        useMapBounds: false,
        providers: [L.esri.Geocoding.arcgisOnlineProvider({
            apikey: apiKey,
            nearby: {
                lat: latitude,
                lng: longitude
            },
        })]
    }).addTo(map);

    var marker = L.marker([latitude, longitude], {
        draggable: true
    }).addTo(map);
    const results = L.layerGroup().addTo(map);

    searchControl.on("results", (data) => {
        map.removeLayer(marker)
        results.clearLayers();
        for (let i = data.results.length - 1; i >= 0; i--) {
            const lngLatString = `${Math.round(data.results[i].latlng.lng * 100000)/100000}, ${Math.round(data.results[i].latlng.lat * 100000)/100000}`;
            const marker = L.marker(data.results[i].latlng, {
                draggable: true
            });
            marker.bindPopup(`<b>${lngLatString}</b><p>${data.results[i].properties.LongLabel}</p>`)
            results.addLayer(marker);
            marker.openPopup();

            marker.on('dragend', function(e) {
                document.getElementById('latitude').value = e.target._latlng.lat;
                document.getElementById('longitude').value = e.target._latlng.lng;
            });


            document.getElementById('latitude').value = data.results[i].latlng.lat;
            document.getElementById('longitude').value = data.results[i].latlng.lng;
        }
    });

    marker.on('dragend', function(e) {

        document.getElementById('latitude').value = e.target._latlng.lat;
        document.getElementById('longitude').value = e.target._latlng.lng;
    });
    $(document).ready(function() {

        $('.pointer').show();

        setTimeout(function() {
            $('.pointer').fadeOut('slow');
        }, 3400);

    });

    function myFunctiondelete(id) {
        var form = document.getElementById("delete_galeri");
        var newUrl = "{{ route('gym-place.galeries.destroy', ':galeri_id') }}".replace(':galeri_id', id);
        form.action = newUrl;

        var agree = confirm("Anda yakin ingin menghapus galeri ini?");
        if(agree == true){
            document.getElementById("delete_galeri").submit();
        }
        else{
            return false;
        }
    }

    function translateDescriptionEnglish() {
        translate('#description', '#description_en');
    }

    function translateDescriptionChinese() {
        translateChinese('#description', '#description_cn');
    }

</script>
@endpush