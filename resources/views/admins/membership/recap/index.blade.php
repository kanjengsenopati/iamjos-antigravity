@extends('layouts.master', ['title' => 'Membership Recap', 'main' => 'Dashboard'])

@section('content')
<div class="app-main pt-6 flex-column flex-row-fluid" id="kt_app_main">
    <!--begin::Content wrapper-->
    <div class="d-flex flex-column flex-column-fluid">
        <!--begin::Content-->
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <!--begin::Content container-->
            <div id="kt_app_content_container" class="app-container container-xxl">
                <!--begin::Card-->
                <div class="card card-flush">
                    <!--begin::Card header-->
                    <!--end::Card header--> 
                    <!--begin::Card body-->
                    <div class="card-body pt-6">
                        <div class="mb-2 d-flex flex-wrap align-items-center justify-content-between gap-4 border-0 pt-6">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Membership Recap</span>
                            </h3>
                            @if(Auth::user()->is_show_all_gymplace)
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" onchange="table()">
                                        <option value="">Semua Gym Place</option>
                                        @foreach ($gym_places as $gym_place)
                                        <option value="{{$gym_place->id}}">{{$gym_place->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div class="">
                                    <select name="gym_place_id" id="gym_place_id" class="form-select w-170px" disabled>
                                        @php
                                            $userGymPlace = Auth::user()->gym_place;
                                        @endphp
                                        @if($userGymPlace)
                                            <option value="{{ $userGymPlace->id }}" selected>{{ $userGymPlace->name }}</option>
                                        @else
                                            <option value="">Tidak ada Gym Place</option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="gym_place_id" value="{{ $userGymPlace->id ?? '' }}">
                                </div>
                            @endif
                        </div>
                        <div class="border-0 pt-6 d-flex mb-3 flex-wrap gap-4 justify-content-between align-items-center">
                            <div>
                                <h4>&nbsp;</h4>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <form action="{{ route('membership.recap.export', $gym_places[0]->id) }}" id="membership_export" method="GET" enctype="multipart/form-data">
                                    @method('GET')
                                    {{-- <input type="text" id="i_filter_gym_place_id" name="gym_place_id" hidden> --}}
                                    <button class="btn btn-primary btn-sm text-nowrap" type="submit">
                                        <i class="ki-duotone ki-exit-up fs-3">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                        Export Excel
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div>
                            <table id="datatable_membership" class="table table-hover align-middle table-row-dashed fs-6 gy-5 mb-0">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th width="5%">No</th>
                                        <th width="10%">Thumbnail</th>
                                        <th width="25%">Nama Paket</th>
                                        <th width="20%">Tipe Paket</th>
                                        <th width="15%">Periode Berlangganan</th>
                                        {{-- <th width="10%">Status</th>
                                        <th width="">Status Publish</th> --}}
                                        <th width="5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="text-dark fw-semibold"></tbody>
                            </table>
                        </div>
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Content container-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Content wrapper-->

</div>

<div class="modal fade" tabindex="-1" id="modal-membership">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="membership_name">Membership ....</h3>

                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
                <!--end::Close-->
            </div>

            <div class="modal-body">
                <h4 class="mb-6" >Daftar User Membership</h4>
                <div class="row">
                    <table class="table table-bordered table-hover align-middle table-row-dashed fs-6 p-4 mb-0" id="tableData">
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    $(document).ready(function() {
        table();
    });

    function membership(id) {
        $.ajax({
            url: "{{ url('membership-recap/detail/:id') }}".replace(':id', id),
            method: 'get',
            dataType: 'json',
            success: function(data) {
                $('#modal-membership').modal('show');
                $("#membership_name").text('Membership '+data.membership)

                let table = document.getElementById("tableData");
                let user = data.user;
                let no = 1;
                let html = 
                    "<tr>\
                        <th>No</th>\
                        <th>Nama User</th>\
                        <th>Telepon</th>\
                        <th>Harga Pembelian</th>\
                        <th>Tanggal Mulai</th>\
                        <th>Tanggal Selesai</th>\
                        <th>Sisa Waktu Membership</th>\
                    </tr>";
                
                if (user.length > 0){
                    user.forEach(function(item) {
                        var dateString = item.expiry_date;
                        var parts = dateString.split("-");
                        var day = parts[0];
                        var month = parts[1];
                        var year = parts[2];
                        var expiry_date = month + "/" + day + "/" + year;
    
                        const date1 = new Date(data.dateNow);
                        const date2 = new Date(expiry_date);
                        const diffTime = Math.abs(date2 - date1);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
    
                        if (item.expiry_date != 'Aktif Selamanya'){
                            if (date2 >= date1) {
                                var dayRemaining = diffDays + " Hari";
                            } else {
                                var dayRemaining = "Membership Telah Berakhir";
                            }
                            // var dayRemaining = diffDays + " Hari";
                        }else{
                            var dayRemaining = "Aktif Selamanya";
                        }
    
                        var price = item.transaction_detail != null ? item.transaction_detail.transaction.pay_amount : 0;
                        let phone = item.user.phone ? item.user.phone : "-";
    
                        html += "<tr>";
                        html += "<td>" + (no++) + "</td>";
                        html += "<td><a href='{{ route('user.show', ':id') }}' target='_blank'>".replace(':id', item.user_id) + item.user?.name + "</a></td>";
                        html += "<td>" + phone + "</td>";
                        html += "<td>Rp. " + price.toLocaleString() + "</td>";
                        html += "<td>" + item.start_active_date + "</td>";
                        html += "<td>" + item.expiry_date + "</td>";
                        html += "<td>" + dayRemaining + "</td>";
                        html += "</tr>";
                    });

                    table.innerHTML = html;
                } else {
                        html += "<tr>";
                        html += "<td colspan='7' class='text-center'>-- Tidak Ada Data Berlangganan Membership --</td>";
                        html += "</tr>";
                    table.innerHTML = html;
                }
            }
        });
    }
    
    function table() {
        // replace route
        var gym_place = $("#gym_place_id").val();
        $('#membership_export').attr('action', "{{ url('membership-recap/export/excel') }}/" + gym_place);
        
        // datatable
        var table = $('#datatable_membership').DataTable({
            ordering: false,
            processing: true,
            serverSide: true,
            responsive: true,
            destroy: true,
            ajax: {
                url: "{{ route('membership.recap.index') }}",
                type: 'GET',
                data: {
                    gym_place_id:$("#gym_place_id").val()
                },
                beforeSend: function() {
                    $('#datatable_membership tbody').empty(); 
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
                    responsivePriority: -3,
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { data: 'thumbnail', name: 'thumbnail', responsivePriority: -1},
                { data: 'name', name: 'name', responsivePriority: -2},
                { data: 'type', name: 'type' },
                {
                    data: 'period',
                    name: 'period',
                    render: function(data, type, row, meta) {
                        return data ? `<p>${data} Hari</p>` : 'Aktif Selamanya';
                    }
                },
                // {
                //     data: null,
                //     render: function(data, type, row) {
                //         let is_active = data.is_active ? `<span class="badge badge-light-success">Aktif</span>` : `<span class="badge badge-light-warning">Non Aktif</span>`;
                //         return is_active;
                //     },
                // },
                // {
                //     data: null,
                //     render: function(data, type, row) {
                        
                //         let is_published = data.is_published ? `<span class="badge badge-light-success">Membership di Tampilkan Publik</span>` : `<span class="badge badge-light-warning">Membership di Sembunyikan</span>`;
                //         return is_published;
                //     },
                // },
                {
                    data: 'action',
                    name: 'action',
                    orderable: true,
                    searchable: true,
                    responsivePriority: -1,
                },
            ]
        });
        // document.getElementById('i_filter_gym_place_id').value = $("#gym_place_id").val();

        // Menyembunyikan tabel selama proses loading
        table.on('preXhr.dt', function(e, settings, data) {
            $('#datatable_membership tbody').empty();
        });

        // Menampilkan tabel setelah data selesai dimuat
        table.on('draw.dt', function() {
            $('#datatable_membership').fadeIn();
        });
    }    
    </script>
@endpush