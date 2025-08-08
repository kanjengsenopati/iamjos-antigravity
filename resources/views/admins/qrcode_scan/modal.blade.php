<form action="{{ route('add-checkin-user') }}" method="post" enctype="multipart/form-data" id="form-add-checkin-user">
    @csrf
    <div class="modal fade" id="modal-add-checkin-user" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" id="custom-modal-user-checkin">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Tambah Checkin User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="gym_place_id" value="{{ auth()->user()->gym_place_id }}">
                    <div class="mb-5">
                        <label for="user_type_checkin" class="form-label">Tipe User</label>
                        <select class="form-select" name="locker_id" id="user_type_checkin"
                            onchange="changeUserTypeCheckin(this.value)" data-placeholder="Pilih Tipe User">
                            <option value="">--Pilih Tipe User--</option>
                            <option value="member">Member Nest Gym</option>
                            <option value="guest">Pengunjung / Guest</option>
                        </select>
                    </div>
                    <div class="d-none" id="user-checkin">
                        <hr class="mb-4">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Nama User</label>
                            <select class="form-select" name="user_id" id="user_id" data-placeholder="Pilih User">
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="locker_checkin_id" class="form-label">Loker</label>
                            <select class="form-select" name="locker_id" id="locker_checkin_id"
                                data-placeholder="Pilih Loker">
                                <option value="">--Pilih Loker--</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="check_in" class="form-label">Waktu Checkin</label>
                            <input type="datetime-local" class="form-control" name="check_in" id="check_in">
                        </div>
                        <div class="mb-3">
                            <h6 class="mb-2">Fasilitas Tambahan</h6>
                            <div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" value="true" id="sauna"
                                        name="is_sauna">
                                    <label class="form-check-label" for="sauna">
                                        Sauna
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="true" id="ice_bath"
                                        name="is_ice_bath">
                                    <label class="form-check-label" for="ice_bath">
                                        Ice Bath
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row d-none" id="guest-checkin">
                        <hr class="mb-4">
                        <div class="col-xl-6">
                            <div class="mb-3">
                                <label class="fs-6 fw-bold form-label mt-3" for="guest_name">
                                    <span class="required">Nama Pengunjung</span>
                                </label>
                                <input type="text" class="form-control" name="guest_name" id="guest_name">
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold form-label mt-3" for="guest_phone">
                                    <span class="required">No Telepon</span>
                                </label>
                                <input type="text" class="form-control" name="guest_phone" id="guest_phone">
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold form-label mt-3" for="guest_gender">
                                    <span class="required">Jenis Kelamin</span>
                                </label>
                                <select name="guest_gender" class="form-select" id="guest_gender">
                                    <option value="">--Pilih Jenis Kelamin--</option>
                                    <option value="MALE">Laki-laki</option>
                                    <option value="FEMALE">Perempuan</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="locker_checkin_id" class="form-label">Loker</label>
                                <select class="form-select" name="guest_locker_id" id="guest_locker_checkin_id"
                                    data-placeholder="Pilih Loker">
                                    <option value="">--Pilih Loker--</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="fs-6 fw-bold form-label mt-3" for="check_in">
                                    <span class="required">Waktu Checkin</span>
                                </label>
                                <input type="datetime-local" class="form-control" name="guest_check_in"
                                    id="guest_check_in">
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="mb-3">
                                <label class="fs-6 fw-bold form-label mt-3" for="">
                                    <span class="">Fasilitas Tambahan</span>
                                </label>
                                <div>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" value="true"
                                            id="sauna" name="guest_is_sauna">
                                        <label class="form-check-label" for="sauna">
                                            Sauna
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" value="true"
                                            id="ice_bath" name="guest_is_ice_bath">
                                        <label class="form-check-label" for="ice_bath">
                                            Ice Bath
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="fv-row mb-6">
                                <!--begin::Label-->
                                <label class="fs-6 fw-bold form-label mt-3" for="tag">
                                    <span class="required">Pilih Metode Pembayaran di Tempat</span>
                                </label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="QRIS" id="QRIS"
                                        name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="QRIS">
                                        QRIS
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="DEBIT BNI" id="DEBITBNI"
                                        name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="DEBITBNI">
                                        DEBIT BNI
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="CREDIT BNI" id="CREDITBNI"
                                        name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="CREDITBNI">
                                        CREDIT BNI
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="QRIS BNI" id="QRISBNI"
                                        name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="QRISBNI">
                                        QRIS BNI
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="DEBIT OCBC" id="DEBITOCBC"
                                        name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="DEBITOCBC">
                                        DEBIT OCBC
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="CREDIT OCBC"
                                        id="CREDITOCBC" name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="CREDITOCBC">
                                        CREDIT OCBC
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="QRIS OCBC" id="QRISOCBC"
                                        name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="QRISOCBC">
                                        QRIS OCBC
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" value="TRANSFER BCA"
                                        id="TRANSFERBCA" name="guest_offline_payment_method" />
                                    <label class="form-check-label fw-semibold fs-6" for="TRANSFERBCA">
                                        TRANSFER BCA
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        Tambah Sekarang</button>
                </div>
            </div>
        </div>
    </div>

</form>

<form action="{{ route('import-checkin-checkout') }}" method="post" enctype="multipart/form-data">
    @csrf
    <div class="modal fade" id="modal-import-checkin-checkout" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staticBackdropLabel">Import Checkin & Checkout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" name="gym_place_id" value="{{ auth()->user()->gym_place_id }}" hidden
                        required>
                    <input type="file" class="form-control" name="file" accept=".xlsx" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <a target="_BLANK" class="btn btn-sm d-flex gap-2 align-items-center btn-primary"
                        href="{{ asset('/assets/media/template_imports/template-import-checkin-checkout-nestgym.xlsx') }}">
                        <i class="ki-duotone ki-exit-up fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Download Template
                    </a>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-primary">
                        <i class="ki-duotone ki-exit-down fs-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Import Sekarang</button>
                </div>
            </div>
        </div>
    </div>
</form>


<form id="form-confirm-transaction" method="post">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-confirm-transaction" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informasi Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <td>Nama User</td>
                                <td id="user_confirm"></td>
                            </tr>
                            <tr>
                                <td>Kode Pembayaran</td>
                                <td id="payment_code_confirm"></td>
                            </tr>
                            <tr>
                                <td>Metode Pembayaran</td>
                                <td id="payment_method_confirm"></td>
                            </tr>
                            <tr>
                                <td>Program Yang Dibeli</td>
                                <td id="program_confirm"></td>
                            </tr>
                            <tr>
                                <td>Nominal Bayar</td>
                                <th id="pay_amount_confirm"></th>
                            </tr>
                            <tr>
                                <td>Status Pembayaran</td>
                                <td id="status_confirm"></td>
                            </tr>
                        </tbody>
                    </table>
                    <!--begin::Input group-->
                    <div class="fv-row mb-6">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label mt-3" for="tag">
                            <span class="required">Pilih Metode Pembayaran di Tempat</span>
                        </label>
                        <!--end::Label-->
                        {{-- <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="Cash" id="Cash"
                                name="offline_payment_method" required />
                            <label class="form-check-label fw-semibold fs-6" for="Cash">
                                Cash
                            </label>
                        </div> --}}
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="QRIS" id="QRIS"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="QRIS">
                                QRIS
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="DEBIT BNI" id="DEBITBNI"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="DEBITBNI">
                                DEBIT BNI
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="CREDIT BNI" id="CREDITBNI"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="CREDITBNI">
                                CREDIT BNI
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="QRIS BNI" id="QRISBNI"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="QRISBNI">
                                QRIS BNI
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="DEBIT OCBC" id="DEBITOCBC"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="DEBITOCBC">
                                DEBIT OCBC
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="CREDIT OCBC" id="CREDITOCBC"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="CREDITOCBC">
                                CREDIT OCBC
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="QRIS OCBC" id="QRISOCBC"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="QRISOCBC">
                                QRIS OCBC
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="TRANSFER BCA" id="TRANSFERBCA"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="TRANSFERBCA">
                                TRANSFER BCA
                            </label>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal"
                        id="cancel-confirm-transaction">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import">
                        Konfirmasi Pembayaran</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="form-confirm-shop" method="post">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-confirm-shop" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informasi Pesanan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped table-bordered">
                        <tbody>
                            <tr>
                                <td>Nama User</td>
                                <td id="user_shop_confirm"></td>
                            </tr>
                            <tr>
                                <td>Kode Pembayaran</td>
                                <td id="payment_code_confirm_shop"></td>
                            </tr>
                            <tr>
                                <td>Metode Pembayaran</td>
                                <td id="payment_method_confirm_shop"></td>
                            </tr>
                            <tr>
                                <td>Produk</td>
                                <td id="program_confirm_shop"></td>
                            </tr>
                            <tr>
                                <td>Nominal Bayar</td>
                                <th id="pay_amount_confirm_shop"></th>
                            </tr>
                            <tr>
                                <td>Status Pembayaran</td>
                                <td id="status_confirm_shop"></td>
                            </tr>
                        </tbody>
                    </table>
                    <!--begin::Input group-->
                    <div class="fv-row mb-6" id="offline_payment_method_shop" style="display: none;">
                        <!--begin::Label-->
                        <label class="fs-6 fw-bold form-label mt-3" for="tag">
                            <span class="required">Pilih Metode Pembayaran di Tempat</span>
                        </label>
                        <!--end::Label-->
                        {{-- <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="Cash" id="shop-Cash"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-Cash">
                                Cash
                            </label>
                        </div> --}}
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="QRIS" id="shop-QRIS"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-QRIS">
                                QRIS
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="DEBIT BNI" id="shop-DEBITBNI"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-DEBITBNI">
                                DEBIT BNI
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="CREDIT BNI" id="shop-CREDITBNI"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-CREDITBNI">
                                CREDIT BNI
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="QRIS BNI" id="shop-QRISBNI"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-QRISBNI">
                                QRIS BNI
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="DEBIT OCBC" id="shop-DEBITOCBC"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-DEBITOCBC">
                                DEBIT OCBC
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="CREDIT OCBC" id="shop-CREDITOCBC"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-CREDITOCBC">
                                CREDIT OCBC
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="QRIS OCBC" id="shop-QRISOCBC"
                                name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-QRISOCBC">
                                QRIS OCBC
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" value="TRANSFER BCA"
                                id="shop-TRANSFERBCA" name="offline_payment_method" />
                            <label class="form-check-label fw-semibold fs-6" for="shop-TRANSFERBCA">
                                TRANSFER BCA
                            </label>
                        </div>
                    </div>
                    <!--end::Input group-->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal"
                        id="cancel-confirm-shop">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import"
                        id="btn-confirm-shop">
                        Konfirmasi Pembayaran</button>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="form-confirm-membership" method="post">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-confirm-membership" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-cs-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="membership_check_in_type"></h5>
                    <button type="button" class="btn-close" id="close-checkin" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Membership Detail</h5>
                            <table class="table table-striped table-bordered">
                                <tbody>
                                    <tr>
                                        <td>Tempat Gym</td>
                                        <td id="gym_place_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Avatar</td>
                                        <td id="avatar_user"></td>
                                    </tr>
                                    <tr>
                                        <td>Nama User</td>
                                        <td id="user_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Membership ID</td>
                                        <td id="user_member_id"></td>
                                    </tr>
                                    <tr>
                                        <td>Membership</td>
                                        <td id="name_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Personal trainer</td>
                                        <td id="name_personal_trainer"></td>
                                    </tr>
                                    <tr>
                                        <td>Sisa Sesi</td>
                                        <td id="remaining_session_personal_trainer"></td>
                                    </tr>
                                    {{-- <tr>
                                        <td>Total Sesi</td>
                                        <td id="session_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Sesi Dipakai</td>
                                        <td id="taken_session_membership"></td>
                                    </tr>
                                    <tr>
                                        <td>Sisa Sesi</td>
                                        <td id="remaining_session_membership"></td>
                                    </tr> --}}
                                    <tr>
                                        <td>Loker</td>
                                        <td id="locker">
                                            <select name="locker_id" id="locker_id" class="form-select">
                                                <option value="">--Pilih Loker--</option>
                                            </select>
                                            <div id="info-locker"></div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Fasilitas Tambahan </td>
                                        <td>
                                            <p id="info-additional-facility"></p>
                                            <div id="additional_facility">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" value="true"
                                                        id="membership_detail_sauna" name="is_sauna">
                                                    <label class="form-check-label" for="sauna">
                                                        Sauna
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" value="true"
                                                        id="membership_detail_ice_bath" name="is_ice_bath">
                                                    <label class="form-check-label" for="ice_bath">
                                                        Ice Bath
                                                    </label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <div id="gates">
                                <h5>Gates Activity</h5>
                                <table class="table table-sm table-striped table-bordered" id="table-gates">
                                </table>
                            </div>
                            <h5>Notes</h5>
                            <table class="table table-sm table-striped table-bordered" id="table-notes">
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="action-confirm-membership">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal"
                        id="cancel-checkin">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import">
                        Konfirmasi</button>
                </div>
            </div>
        </div>
    </div>
</form>

<div class="modal fade" id="modal-confirm-membership-offline" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="membership_check_in_type_offline"></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5>Membership Detail</h5>
                        <table class="table table-striped table-bordered">
                            <tbody>

                                <tr>
                                    <td>Membership ID</td>
                                    <td id="user_member_id_offline"></td>
                                </tr>
                                <input type="text" hidden id="member_id_offline">
                                {{-- <tr>
                                    <td>Loker</td>
                                    <td id="locker">
                                        <select name="locker_id" id="locker_id_offline" class="form-select">
                                            <option value="">--Pilih Loker--</option>
                                        </select>
                                    </td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="action-confirm-membership">
                <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import"
                    onclick="confirmCheckinOffline()">
                    Konfirmasi</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modal-show-gym-class" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="check_in_type">Kelas Gym</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <h5>Kelas Detail</h5>
                        <table class="table table-striped table-bordered">
                            <tbody>
                                <tr>
                                    <td class="w-25">Tempat Gym</td>
                                    <td id="gym_place_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Avatar</td>
                                    <td id="avatar_user_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Nama User</td>
                                    <td id="user_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Membership ID</td>
                                    <td id="user_member_id_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Nama Kelas</td>
                                    <td id="name_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Tanggal</td>
                                    <td id="date_gym_class"></td>
                                </tr>
                                <tr>
                                    <td>Personal trainer</td>
                                    <td id="name_personal_trainer_gym_class"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="modal-show-gate-activity" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="check_in_type">Gate Activity</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-sm table-striped table-bordered" id="table-gates">
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade show" id="modal-failed-import" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-cs-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title text-danger">Data Gagal Import <i
                        class="fa fa-exclamation-circle fa-x2 text-danger" aria-hidden="true"></i>
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Nama</th>
                                    <th>Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (session('failed_import') ?? [] as $failed)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $failed['name'] }}</td>
                                        <td>{{ $failed['reason'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="form-confirm-merchandise" method="post">
    @csrf
    @method('PATCH')
    <div class="modal fade" id="modal-confirm-merchandise" tabindex="-1" data-bs-backdrop="static"
        data-bs-keyboard="false" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Informasi Pengambilan Merchandise</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h4 class="mb-3">Detail Merchandise</h4>
                    <table class="table table-striped table-bordered mb-5">
                        <tbody>
                            <tr>
                                <td>Nama User</td>
                                <td id="merchandise_user"></td>
                            </tr>
                            <tr>
                                <td>Tempat Gym</td>
                                <td id="merchandise_gym"></td>
                            </tr>
                            <tr>
                                <td>Kode Claim</td>
                                <td id="merchandise_qrcode"></td>
                            </tr>
                            <tr>
                                <td>Keterangan Merchandise</td>
                                <td id="merchandise_description"></td>
                            </tr>
                        </tbody>
                    </table>
                    <h5 class="mb-3">Merchandise</h5>
                    <table class="table table-sm table-striped table-bordered" id="table-merchandise-list">
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-batal" data-bs-dismiss="modal"
                        id="cancel-confirm-transaction">Batal</button>
                    <button type="submit" class="btn btn-sm d-flex gap-2 align-items-center btn-import"
                        id="submit-merchandise">
                        Konfirmasi Pengambilan Merchandise</button>
                </div>
            </div>
        </div>
    </div>
</form>
