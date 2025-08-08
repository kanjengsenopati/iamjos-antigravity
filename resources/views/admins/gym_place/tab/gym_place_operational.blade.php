<div class="d-flex gap-2 align-items-center mb-3">
    <h3>Jadwal Operasional</h3>
    <a href="{{route('gym-place.edit', $gymPlace->id)}}" class="btn-edit">
        <i class="ki-duotone ki-notepad-edit fs-3">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </a>
</div>
<table class="table table-bordered">
    <thead>
        <tr class="text-start text-gray-400 fw-bolder fs-7 text-uppercase gs-0">
            <th style="max-width: 100px;">Hari</th>
            <th>Jam Buka</th>
            <th>Jam Tutup</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($gymPlace->gym_place_operationals ?? [] as $gymPlaceOperational)
        <tr class="text-dark fw-semibold">
            <td>{{$gymPlaceOperational->day}}</td>
            <td>{{$gymPlaceOperational->opening_time}}</td>
            <td>{{$gymPlaceOperational->closing_time}}</td>
        </tr>
        @endforeach
    </tbody>
</table>