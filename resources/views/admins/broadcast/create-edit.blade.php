@extends('layouts.master', [
'main' => 'Data Broadcast',
'title' => request()->routeIs('broadcast.create')
? 'Tambah
Broadcast'
: 'Edit Broadcast',
])
@section('content')
<!--begin::Container-->
<div id="kt_content_container" class="app-container container-xxl pt-6">
    <!--begin::Contacts App- Add New Contact-->
    <div class="row g-7">
        <!--begin::Content-->
        <div class="col-xl-12">
            <!--begin::Contacts-->
            <div class="card h-lg-100" id="kt_contacts_main">
                <!--begin::Card header-->
                <div class="card-header" id="kt_chat_contacts_header">
                    <!--begin::Card title-->
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold fs-3">{{ request()->routeIs('broadcast.create')
                            ? 'Tambah
                            Broadcast'
                            : 'Edit Broadcast' }}</span>
                    </h3>
                    <!--end::Card title-->
                </div>
                <!--end::Card header-->
                <!--begin::Card body-->
                <div class="card-body pt-5">
                    <!--begin::Form-->
                    <x-alert.alert-validation />
                    <form id="broadcast"
                        action="{{ request()->routeIs('broadcast.create') ? route('broadcast.store') : route('broadcast.update', @$broadcast->id) }}"
                        method="POST" enctype="multipart/form-data">
                        @csrf
                        <x-form.put-method />
                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="title">
                                <span class="required text-dark">Judul</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Judul"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <input type="title" class="form-control" id="title" placeholder="Nama Broadcast"
                                name="title" value="{{ @$broadcast->title ?? old('title') }}" required />
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label for="name" class="fs-6 fw-bold form-label mt-3" for="body">
                                <span class="required text-dark">Isi</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Isi"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <textarea class="form-control" id="body" name="body" placeholder="Masukkan Isi Broadcast"
                                required>{{ @$broadcast->body ?? old('body') }}</textarea>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label for="name" class="fs-6 fw-bold form-label mt-3" for="body">
                                <span class="required text-dark">Broadcast Untuk Gym Place</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih Gym Place"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="gym_place_id" class="form-select" id="gym_place_id">
                                <option value="">--Semua Gym Place--</option>
                                @foreach ($gym_places as $gym_place)
                                <option value="{{ $gym_place->id }}"
                                    @if (@$broadcast->gym_place_id == $gym_place->id) selected @endif>
                                    {{ $gym_place->name }}
                                </option>
                                @endforeach
                            </select>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="type">
                                <span class="required text-dark">Tipe Broadcast</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Tipe Broadcast"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="type" class="form-select" id="type">
                                <option value="">--Pilih Tipe Broadcast--</option>
                                <option value="All" @if (@$broadcast->type == 'All') selected @endif>
                                    Semua
                                </option>
                                <option value="Membership" @if (@$broadcast->type == 'Membership') selected @endif>
                                    Membership
                                </option>
                                <option value="PersonalTrainer" @if (@$broadcast->type == 'PersonalTrainer') selected
                                    @endif>
                                    Personal Trainer
                                </option>
                                <option value="GymClassBundling" @if (@$broadcast->type == 'GymClassBundling') selected
                                    @endif>
                                    Personal Trainer Plus
                                </option>
                                <option value="Article" @if (@$broadcast->type == 'Article') selected @endif>
                                    Artikel
                                </option>
                            </select>
                            <!--end::Input-->
                        </div>

                        <div id="reference_input"
                            class="fv-row mb-6 {{ @$broadcast->type == 'All' || @$broadcast->type == 'PersonalTrainer' ? 'd-none' : '' }}">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="reference_id">
                                <span class="required text-dark">Broadcast Untuk</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Pilih Data Yang Akan Diberikan Broadcast"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="reference_id" class="form-select" id="reference_id">
                                <option value="">--Pilih Broadcast Untuk--</option>
                            </select>
                            <!--end::Input-->
                            @if (request()->routeIs('broadcast.edit'))
                            <input type="text" name="reference_value_id" id="reference_value_id"
                                value="{{ @$broadcast->reference_id }}" hidden>
                            @endif
                        </div>


                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="type">
                                <span class="required text-dark">Tipe Target</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan tipe target"></i>
                            </label>
                            <!--end::Label-->
                            <select id="target_type" name="target" class="form-select" data-control="select2"
                                data-hide-search="true">
                                <option {{ !@$broadcast->user || @$broadcast->target == 'ALL' ? 'selected' : '' }}
                                    value="ALL">All</option>
                                {{-- tambahkan userby membership --}}
                                <option {{ @$broadcast->user || @$broadcast->target == 'USER' ? 'selected' : '' }}
                                    value="USER">Pilih User</option>
                                @foreach ($memberships as $membership)
                                <option value="MEMBERSHIP-{{ $membership->id }}" @selected(@$broadcast->target ==
                                    'MEMBERSHIP-'.$membership->id ? true : false)>Member Langganan {{ $membership->name
                                    }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="fv-row mb-6 {{ @$broadcast->user || @$broadcast->user_list && @$broadcast->target == 'USER' ? '' : 'd-none' }}"
                            id="input-user">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="type">
                                <span class="required text-dark">Target User</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan user tujuan"></i>
                            </label>
                            <!--end::Label-->
                            <select name="user_id" id="user_id" class="form-select mb-4" data-control="select2"
                                data-hide-search="true" data-placeholder="Select User" onchange="addToTable()">
                                @if (@$broadcast->user)
                                <option value="{{ $broadcast->user_id }}" selected>{{ $broadcast->user->name }}
                                </option>
                                @endif
                            </select>
                            <label class="fs-6 fw-bold form-label" for=""><span class="text-dark">Daftar User
                                    Broadcast</span></label>
                            <table id="table-user-broadcast"
                                class="table table-sm table-hover table-bordered align-middle table-row-dashed fs-6 gy-2 mb-0 p-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="text-center min-w-325px">Nama</th>
                                        <th class="text-center min-w-125px">Member ID</th>
                                        <th class="text-center min-w-100px">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-dark">
                                </tbody>
                            </table>
                        </div>

                        <div class="fv-row mb-6">
                            <input type="hidden" name="user_list" id="user_list"
                                value="{{ old('user_list', @$broadcast->user_list) }}">
                        </div>

                        <div class="fv-row mb-6">
                            <!--begin::Label-->
                            <label class="fs-6 fw-bold form-label" for="media">
                                <span class="required text-dark">Media Broadcast</span>
                                <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip"
                                    title="Masukkan Media Broadcast"></i>
                            </label>
                            <!--end::Label-->
                            <!--begin::Input-->
                            <select name="media" class="form-select" id="media">
                                <option value="">Notifikasi</option>
                                <option value="CHAT" @if (@$broadcast->media == 'CHAT') selected @endif>Chat</option>
                            </select>
                            <!--end::Input-->
                        </div>

                        <div class="fv-row mb-6">
                            <x-form.image-upload label="Foto" maxSize="2MB" name="image"
                                :value="@$broadcast->image ?? null" nullable='1' />
                        </div>
                        <!--end::Input group-->
                        <!--begin::Separator-->

                        <!--end::Separator-->
                        <!--begin::Action buttons-->
                        <div class="d-flex justify-content-end">
                            <!--begin::Button-->
                            <a href="{{ route('broadcast.index') }}">
                                <button type="button" data-kt-contacts-type="cancel"
                                    class="btn btn-secondary me-3">Batal</button>
                            </a>
                            <!--end::Button-->
                            <!--begin::Button-->
                            <button type="submit" data-kt-contacts-type="submit" class="btn btn-primary btn-sm me-3" name="submit_type" value="NORMAL">
                                <span class="indicator-label">Simpan</span>
                                <span class="indicator-progress">Mohon Tunggu...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                            <button type="submit" data-kt-contacts-type="submit" class="btn btn-info btn-sm  me-3" name="submit_type" value="BROADCAST">
                                <span class="indicator-label">Simpan & Kirim Broadcast</span>
                                <span class="indicator-progress">Mohon Tunggu...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>
                            <!--end::Button-->
                        </div>
                        <!--end::Action buttons-->
                    </form>
                    <!--end::Form-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Contacts-->
        </div>
        <!--end::Content-->
    </div>
    <!--end::Contacts App- Add New Contact-->
</div>
<!--end::Container-->
</div>
<!--end::Content-->
<!--end::Wrapper-->
@endsection
@push('js')
<script>
    $(document).ready(() => {
            // var userSelectResults = [];
            updateReference();

            $('#target_type').on('change', function() {
                if (this.value == 'USER') {
                    $('#input-user').removeClass('d-none');
                } else {
                    $('#input-user').addClass('d-none');
                    $('#user_id').val('').change();
                }
            });


            $('#type').on('change', function() {
                updateReference();
            });

            function updateReference() {
                let type = $('#type').val();
                let reference_id = $('#reference_value_id').val();

                // Clear existing options
                $('#reference_id').html('<option value="">--Pilih Broadcast Untuk--</option>');

                // If type is not 'All' or 'PersonalTrainer', fetch and populate options
                if (type !== 'All' && type !== 'PersonalTrainer') {
                    let url = "{{ route('broadcast.get-reference', ':type') }}";
                    url = url.replace(':type', type);
                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function(data) {
                            $.each(data.data, function(key, value) {
                                $('#reference_id').append('<option value="' + value.id + '" ' +
                                    (reference_id == value.id ? 'selected' : '') + '>' +
                                    value.name + '</option>');
                            });
                            $('#reference_input').removeClass(
                                'd-none'); // Show reference_id input after populating options
                        }
                    });
                } else {
                    // Hide reference_id input if type is 'All' or 'PersonalTrainer'
                    $('#reference_input').addClass('d-none');
                }
            }


            $('#user_id').select2({
                placeholder: "Pilih User",
                ajax: {
                    url: "{{ route('select2') }}",
                    dataType: 'json',
                    delay: 300,
                    data: function(params) {
                        var queryParameters = {
                            search: params.term,
                            data_type: "USER",
                        }
                        return queryParameters;
                    },
                    processResults: function(data) {
                        var results = $.map(data, function(item) {
                            return {
                                text: item.name.toUpperCase(),
                                id: item.id,
                                name: item.name,
                                member_id: item.membership_user?.member_id
                            }
                        });
                        // Menyimpan hasil ke dalam variabel
                        window.userSelectResults = results; // Variabel global untuk menyimpan hasil
                        return {
                            results: results
                        };
                    },
                    cache: true
                }
            });
        });
        
        $(window).on('load', function() {
            window.userSelectResults = []; // Inisialisasi variabel global untuk menyimpan hasil user
            $.ajax({
                url: "{{ route('select2') }}",
                dataType: 'json',
                data: {
                    broadcast_id: "{{ @$broadcast->id }}",
                    data_type: "BROADCAST"
                },
                success: function(data) {
                    window.userSelectResults = data.map(item => ({
                        text: item.name.toUpperCase(),
                        id: item.id,
                        name: item.name,
                        member_id: item.membership_user?.member_id
                    }));
                    
                    const userListInput = $('#user_list').val();
                    if (userListInput) {
                        const userList = JSON.parse(userListInput);
                        const table = document.getElementById("table-user-broadcast").getElementsByTagName('tbody')[0];
                        userList.forEach(userId => {
                            const user = window.userSelectResults.find(user => user.id == userId);
                            if (user) {
                                const row = table.insertRow();
                                const cell1 = row.insertCell(0);
                                const cell2 = row.insertCell(1);
                                const cell3 = row.insertCell(2);
                                cell1.classList.add('text-center');
                                cell2.classList.add('text-center');
                                cell3.classList.add('text-center');
                                cell1.textContent = user.name;
                                cell2.textContent = user.member_id;
                                cell3.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="removeFromTable(this)">Delete</button>';
                            }
                        });
                    }
                }
            });
        });

        function addToTable() {
            const select = document.getElementById("user_id");
            const table = document.getElementById("table-user-broadcast").getElementsByTagName('tbody')[0];
            const userIdsInput = document.getElementById("user_list");
            const currentUserIds = userIdsInput.value ? JSON.parse(userIdsInput.value) : []; // Mengubah menjadi array

            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].selected) {
                    const selectedUser = userSelectResults.find(user => user.id == select.options[i].value);
                    if (selectedUser && !currentUserIds.includes(selectedUser.id.toString())) {
                        const row = table.insertRow();
                        const cell1 = row.insertCell(0);
                        const cell2 = row.insertCell(1);
                        const cell3 = row.insertCell(2);
                        cell1.classList.add('text-center'); // Menambahkan class text-center ke cell1
                        cell2.classList.add('text-center'); // Menambahkan class text-center ke cell2
                        cell3.classList.add('text-center'); // Menambahkan class text-center ke cell2
                        cell1.textContent = selectedUser.name; // Menggunakan nama dari userSelectResults
                        cell2.textContent = selectedUser.member_id; // Menggunakan nama dari userSelectResults
                        cell3.innerHTML = '<button type="button" class="btn btn-sm btn-danger" onclick="removeFromTable(this)">Delete</button>';
                        
                        select.options[i].selected = false;  // Unselect the option after adding it
                        select.options[i].disabled = true;   // Disable the option after adding it

                        // Update input hidden dengan ID pengguna yang ditambahkan
                        currentUserIds.push(selectedUser.id);
                        userIdsInput.value = JSON.stringify(currentUserIds); // Simpan sebagai array JSON
                    }
                }
            }
        }

        function removeFromTable(button) {
            const row = button.parentElement.parentElement;
            const item = row.cells[0].textContent;

            // Enable the corresponding option in the select box
            const select = document.getElementById("user_id");
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].text === item) {
                    select.options[i].disabled = false; // Enable the option again
                    break;
                }
            }

            // Remove the row from the table
            row.remove();

            // Update input hidden untuk menghapus ID pengguna yang dihapus
            const userIdsInput = document.getElementById("user_list");
            const currentUserIds = JSON.parse(userIdsInput.value).filter(id => id !== item);
            userIdsInput.value = JSON.stringify(currentUserIds); // Simpan sebagai array JSON

            // Update form user_list dengan ID yang benar
            const userIdToRemove = userSelectResults.find(user => user.name === item)?.id;
            if (userIdToRemove) {
                userIdsInput.value = JSON.stringify(currentUserIds.filter(id => id !== userIdToRemove.toString())); // Simpan sebagai array JSON
            }
        }

</script>
@endpush