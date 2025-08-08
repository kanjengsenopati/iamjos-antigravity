<html>
    @if ($membership == false)
    <table>
        <tr>
            <td rowspan="2" colspan="9" align="center" style="vertical-align: middle">Data Berlangganan Membership {{ $gym_place->name }}</td>
        </tr>
        <tr></tr>
        @foreach ($data as $data_membership)
        <tr>
            <td width='150px'>Nama Paket</td>
            <td>:</td>
            <td><b>{{ $data_membership['membership']->name }}</b></td>
        </tr>
        <tr>
            <td width='150px'>Tipe Paket</td>
            <td>:</td>
            <td><b>{{ $data_membership['membership']->types()[$data_membership['membership']->type] }}</b></td>
        </tr>
        <tr>
            <td width='150px'>Lama Berlangganan</td>
            <td>:</td>
            <td><b>{{ $data_membership['membership']->period ? $data_membership['membership']->period . " Hari" : "Aktif Selamanya" }}</b></td>
        </tr>
        <tr></tr>
        <tr>
            <td colspan="7" style="padding: 0">
                <table class="table table-bordered" border="2">
                    <tr></tr>
                    <tr>
                        <td></td>
                        <td width='30px'>No</td>
                        <td width='200px'>Nama User</td>
                        <td width='180px'>Telepon</td>
                        <td width='120px'>Harga Pembelian</td>
                        <td width='100px'>Membership ID</td>
                        <td width='100px'>Tanggal Mulai</td>
                        <td width='120px'>Tanggal Selesai</td>
                        <td width='170px'>Sisa Waktu Membership</td>
                    </tr>
                    @if (count($data_membership['membership_history']) > 0)
                        @foreach ($data_membership['membership_history'] as $membership_history)
                        @php
                            $price = $membership_history->transactionDetail != null ? $membership_history->transactionDetail?->transaction?->pay_amount : 0;
                        @endphp
                            <tr>
                                <td></td>
                                <td>{{ $loop->iteration }}</td> 
                                <td>{{ $membership_history->user?->name }}</td>
                                <td>{{ $membership_history->user?->phone ? $membership_history->user?->phone : "" }}</td>
                                {{-- <td>{{ substr(chunk_split(substr($phone, 0, 12), 4, ' '), 0, -1) . substr($phone, 12) }}</td> --}}
                                <td>{{ $price }}</td>
                                <td>{{ $membership_history->user?->membership_user?->member_id }}</td>
                                <td>{{ $membership_history->start_active_date }}</td>
                                <td>{{ $membership_history->expiry_date }}</td>
                                @if ($membership_history->expiry_date == 'Aktif Selamanya' || date('Y-m-d', strtotime($membership_history->expiry_date)) >= Carbon\Carbon::now())
                                <td>{{ $membership_history->expiry_date != 'Aktif Selamanya' ? 'Expired ' . Carbon\Carbon::parse($membership_history->expiry_date)->diffInDays() . ' Hari lagi' : 'Aktif Selamanya' }}</td>
                                @else
                                <td>Membership Telah Berakhir</td>
                                @endif
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td></td>
                            <td colspan="7" align="center">-- Tidak Ada Data Berlangganan Membership --</td>
                        </tr>
                    @endif 
                </table>
            </td>
        </tr>
        @endforeach
    </table>
    @else
    <table>
        <thead>
            <tr>
                <th colspan="8" align="center" style="vertical-align: middle">Data Berlangganan Membership {{ $membership->name }}</th>
            </tr>
            <tr>
                <th width='30px'>No</th>
                <th width='200px'>Nama User</th>
                <th width='180px'>Telepon</th>
                <th width='120px'>Harga Pembelian</th>
                <th width='100px'>Membership ID</th>
                <th width='100px'>Tanggal Mulai</th>
                <th width='120px'>Tanggal Selesai</th>
                <th width='170px'>Sisa Waktu Membership</th>
            </tr>
        </thead>
        <tbody>
            @if (count($data) > 0)
                @foreach ($data as $membership_history)
                <tr>
                    @php
                        $price = $membership_history->transactionDetail != null ? $membership_history->transactionDetail?->transaction?->pay_amount : 0;
                    @endphp
                    <td>{{ $loop->iteration }}</td> 
                    <td>{{ $membership_history->user?->name }}</td>
                    <td>{{ $membership_history->user?->phone ? $membership_history->user?->phone : "" }}</td>
                    {{-- <td>{{ substr(chunk_split(substr($phone, 0, 12), 4, ' '), 0, -1) . substr($phone, 12) }}</td> --}}
                    <td>{{ $price }}</td>
                    <td>{{ $membership_history->user?->membership_user?->member_id }}</td>
                    <td>{{ $membership_history->start_active_date }}</td>
                    <td>{{ $membership_history->expiry_date }}</td>
                    @if ($membership_history->expiry_date == 'Aktif Selamanya' || date('Y-m-d', strtotime($membership_history->expiry_date)) >= Carbon\Carbon::now())
                    <td>{{ $membership_history->expiry_date != 'Aktif Selamanya' ? 'Expired ' . Carbon\Carbon::parse($membership_history->expiry_date)->diffInDays() . ' Hari lagi' : 'AktifSelamanya' }}</td>
                    @else
                    <td>Membership Telah Berakhir</td>
                    @endif
                </tr>
                @endforeach
            @else
            <tr>
                <td colspan="8" align="center">-- Tidak Ada Data Berlangganan Membership --</td>
            </tr>
            @endif 
        </tbody>
    </table>
    @endif
</html>