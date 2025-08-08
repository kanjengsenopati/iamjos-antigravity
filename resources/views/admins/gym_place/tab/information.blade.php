@push('css')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A=="
    crossorigin="" />
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"
    integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA=="
    crossorigin=""></script>
@endpush

<div class="d-flex gap-2 align-items-center mb-3">
    <h3>Informasi Tempat Gym</h3>
    <div>
        @can('gym-place')
        <a href="{{route('gym-place.edit', $gymPlace->id)}}" class="btn-edit">
            <i class="ki-duotone ki-notepad-edit fs-3">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
        </a>
        @endcan
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        <label class="text-muted">Nama</label>
        <p class="text-label">{{$gymPlace->name}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">Kode Member</label>
        <p class="text-label">{{$gymPlace->prefix_code}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">No.Telepon</label>
        <p class="text-label">{{$gymPlace->phone}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">Kode Transaksi Pajak</label>
        <p class="text-label">{{$gymPlace->transaction_code_tax}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">Ambil Transaksi Pajak</label>
        <p class="text-label">{{$gymPlace->take_transaction_for_tax}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">Batas Transaksi Pajak</label>
        <p class="text-label">{{$gymPlace->limit_transaction_for_tax}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">Alamat</label>
        <p class="text-label">{{$gymPlace->address}}</p>
    </div>
    <div class="col-sm-4">
        <label class="text-muted">Deskripsi</label>
        <p class="text-label">{{$gymPlace->description}}</p>
    </div>
    <div class="col-sm-4" style="display: none">
        <label class="text-muted">Deskripsi (Chinese)</label>
        <p class="text-label">{{ $gymPlace->description_cn }}</p>
    </div>
</div>
<div id="map" style="height: 400px"></div>
@push('js')
<script>
    let latitude = <?= @$gymPlace->latitude ?? '-6.175389999999936' ?>;
    let longitude = <?= @$gymPlace->longitude ?? '106.82704000000007' ?>;

    var map, newMarker, markerLocation;
    $(function() {
        // Now map reference the global map declared in the first line
        map = L.map('map').setView([latitude, longitude], 8);

        L.tileLayer('http://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, <a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>',
            maxZoom: 18
        }).addTo(map);
        newMarkerGroup = new L.LayerGroup();
        var marker = L.marker([latitude, longitude]).addTo(map);
    });
</script>
@endpush