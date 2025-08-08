<div class="card edit-profile" id="section_auth" style="display: none">
  <div class="card-header">
    <!--begin::Card title-->
    <h3 class="card-title align-items-start flex-column">
      <span class="card-label fw-bold fs-3 mb-0">Ubah Password</span>
    </h3>
    <!--end::Card title-->
  </div>
  <div class="card-body">
    {{-- <div class="pb-6 border_bottom">
      <p class="fw-bold gray-3">Username</p>
      <input type="text" class="border-0 p-0 bg-transparent" disabled value="loganweav" />
    </div> --}}
    <div>
      {{-- List Password --}}
      <div id="list-password">
        <div class="d-flex justify-content-between align-items-center">
          <div>
            <p class="fw-bold gray-3">Password</p>
            <input type="password" class="border-0 p-0 bg-transparent" value="12345678" disabled />
          </div>
          <button id="btn-change-password" class="btn btn-secondary text-white btn-password">Ubah Password</button>
        </div>
      </div>

      {{-- Change Password --}}
      <form style="display: none" method="POST" novalidate="novalidate" id="change-password"
        action="{{ route('personal-trainer.profile.store', $pt->id) }}">
        @csrf
        <div class="row">
          <div class="fv-row col-xl-4 mb-6 mb-xl-0">
            <!--begin::Password-->
            <label for="old_password" class="form-label mb-2 fw-bold gray-3">Password Lama</label>
            <div class="position-relative">
              <input id="old_password" type="password" placeholder="Password Lama" name="old_password"
                autocomplete="off" class="form-control bg-transparent w-100" />
              <button type="button" class="bg-transparent position-absolute end-0 btn-password py-0 border-0"
                onclick="toggleOldPasswordVisibility()">
                <img id="passwordToggleIcon1" src="{{ asset('assets/media/icons/eye-slash.svg') }}" alt="">
              </button>
            </div>
            <!--end::Password-->
          </div>
          <div class="fv-row col-xl-4 mb-6 mb-xl-0">
            <!--begin::Password-->
            <label for="new_password" class="form-label mb-2 fw-bold gray-3">Password Baru</label>
            <div class="position-relative">
              <input id="new_password" type="password" placeholder="Password Baru" name="new_password"
                autocomplete="off" class="form-control bg-transparent w-100" />
              <button type="button" class="bg-transparent position-absolute end-0 btn-password py-0 border-0"
                onclick="toggleNewPasswordVisibility()">
                <img id="passwordToggleIcon2" src="{{ asset('assets/media/icons/eye-slash.svg') }}" alt="">
              </button>
            </div>
            <!--end::Password-->
          </div>
          <div class="fv-row col-xl-4 mb-6 mb-xl-0">
            <!--begin::Password-->
            <label for="confirm_password" class="form-label mb-2 fw-bold gray-3">Konfirmasi Password Baru</label>
            <div class="position-relative">
              <input id="confirm_password" type="password" placeholder="Konfirmasi Password Baru"
                name="confirm_password" autocomplete="off" class="form-control bg-transparent w-100" />
              <button type="button" class="bg-transparent position-absolute end-0 btn-password py-0 border-0"
                onclick="toggleConfirmPasswordVisibility()">
                <img id="passwordToggleIcon3" src="{{ asset('assets/media/icons/eye-slash.svg') }}" alt="">
              </button>
            </div>
            <!--end::Password-->
          </div>
        </div>
        <p class="text-info-password">Password terdiri dari minimal 8 karakter</p>
        <div class="d-flex mt-4 gap-2 align-items-center justify-content-end">
          <button id="kt_change_password" type="submit" class="btn btn-primary">Ubah Password</button>
          <button type="button" class="btn btn-secondary" id="btn_cancel">Batal</button>
        </div>
      </form>
    </div>
  </div>
</div>