@extends('layouts.pt-master', ['title' => 'Profile'])
@push('css')
<!--end::Fonts-->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
  integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA=="
  crossorigin="anonymous" referrerpolicy="no-referrer" />
<link href="{{asset('assets/plugins/custom/fullcalendar/fullcalendar.bundle.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/plugins/custom/datatables/datatables.bundle.css')}}" rel="stylesheet" type="text/css" />

<style>
  :root,
  [data-bs-theme=light] {
    --color-gray-3: #4B5675;
    --color-gray-5: #4B5675;
    --color-gray-6: #78829D;
    --color-gray-7: #8C8C8C;
    --color-gray-8: #F1F1F2;
    --color-gray-9: #F5F5F5;
    --color-gray-10: #F9F9F9;
    --color-gray-11: #1C1C1C;
    --color-gray-12: #141414;
    --color-green: #99CD15;
    --color-light: #262626;
    --filter: invert(30%) sepia(42%) saturate(408%) hue-rotate(187deg) brightness(97%) contrast(87%);
    /* shadow */
  }

  [data-bs-theme=dark] {
    --color-gray-3: #F5F5F5;
    --color-gray-5: #D9D9D9;
    --color-gray-6: #BFBFBF;
    --color-gray-7: #8C8C8C;
    --color-gray-8: #595959;
    --color-gray-9: #434343;
    --color-gray-10: #262626;
    --color-gray-11: #1C1C1C;
    --color-gray-12: #141414;
    --color-light: #fff;
    --color-green: #99CD15;
    --filter: invert(94%) sepia(1%) saturate(23%) hue-rotate(354deg) brightness(95%) contrast(88%);
    /* shadow */
    --shadow-form: 0px 1px 2px 0px rgba(0, 0, 0, 0.12), 0px 0px 2px 0px rgba(0, 0, 0, 0.12);

  }

  .img_avatar {
    width: 160px;
    height: 100%;
    border-radius: 14px;
    object-fit: cover;
  }

  .main-profile {
    height: 160px;
  }

  .btn-primary {
    border-radius: var(--radius-xl, 1.25rem);
    padding: .5rem 1rem !important;
  }

  .edit-profile p {
    font-size: 14px;
    margin-bottom: 0 !important;
  }

  .edit-profile p.labels {
    color: var(--color-gray-7) !important;
    font-weight: 500 !important;
  }

  .badge-status {
    color: var(--color-green) !important;
    border-radius: var(--radius-s, 0.5rem);
    background: var(--color-gray-10);
    padding: 2px 4px;
    font-weight: 500 !important;
    font-size: 12px !important;
  }

  .edit-profile.card {
    border-radius: 20px !important;
  }

  .name {
    font-size: 20px !important;
    font-weight: 600 !important;
    line-height: 1.75rem;
    /* 140% */
    letter-spacing: -0.00019rem;
    margin-bottom: 8px;
  }

  .box {
    padding: 12px 16px;
    border-radius: var(--radius-s, 0.5rem);
  }

  .box h6 {
    font-size: 20px;
    line-height: 1.75rem;
    /* 140% */
    letter-spacing: -0.00019rem;
    font-weight: 600 !important;
  }

  .box p {
    font-size: 14px !important;
    font-weight: 500;
    line-height: 1.375rem;
    /* 157.143% */
    letter-spacing: -0.00006rem;
    color: var(--color-gray-6);
  }

  label.form-label {
    color: var(--color-gray-3) !important;
    font-size: 14px !important;
  }

  .color-gray-5 {
    color: var(--color-gray-5) !important;
  }

  .form-control {
    border-radius: 0.625rem !important;
    background: var(--color-gray-10) !important;
    color: var(--color-gray-3) !important;
    /* shadow/2 */
    box-shadow: var(--shadow-form) !important;
    border: none !important;
  }

  .form-control::placeholder {
    color: var(--color-gray-3) !important;
  }

  .btn-secondary {
    border-radius: var(--radius-xl, 1.25rem);
    color: var(--color-light) !important;
    background: var(--color-gray-10) !important;
    padding: .5rem 1rem !important;
  }

  .border_bottom {
    border-bottom: 1px dashed var(--color-gray-10);
  }

  .avatar {
    width: 125px;
    height: 125px;
  }

  .avatar img.avatar_img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
  }

  .avatar_content p {
    color: var(--color-gray-5, #D9D9D9);
    font-size: 12px;
    margin-top: 14px;
  }

  .btn-edit,
  .btn-hapus {
    background: var(--color-gray-10);
    border-radius: 100%;
    border: none !important;
    width: 28px;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 !important;
    right: -12px;
  }

  .btn-edit {
    top: -12px;
    cursor: pointer;
  }

  .btn-hapus {
    bottom: -12px;
  }

  .btn-edit img {
    width: 16px;
    height: 16px;
  }

  .gray-3 {
    color: var(--color-gray-3) !important;
  }

  .btn-password {
    right: .5rem !important;
    top: .875rem !important;
  }

  p.text-info-password {
    color: var(--color-gray-5) !important;
    margin-top: 12px;
    font-size: 12px !important;
  }

  .qr-code {
    width: 200px;
    height: 200px;
    background: white;
    margin-bottom: 34px;
    border-radius: 8px;
    overflow: hidden;
  }

  .qr-code img.img_qr {
    width: 100%;
    height: 100%;
    object-fit: cover;
  }

  .qr-code .logos {
    width: 100%;
    height: 100%;
    left: 0;
    top: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .qr-code .logos img {
    height: 44px;
    border-radius: var(--radius-s, 8px);
    border: var(--spacing-00, 1px) solid var(--color-gray-11);
    background: var(--color-gray-12);
  }

  /* modal */
  #kt_modal_id_card .modal-dialog {
    max-width: 328px;
  }

  .gray-10 {
    background: var(--color-gray-10) !important;
  }

  p.badge-light-success {
    color: var(--color-green) !important;
    border-radius: 12px !important;
    padding: 4px 8px !important;
    margin-bottom: 12px !important;
  }

  #kt_modal_id_card h4 {
    font-size: 20px;
    margin-bottom: 4px;
    font-weight: 600;
  }

  #kt_modal_id_card p {
    font-size: 14px;
    font-weight: 500;
    color: var(--color-gray-6);
  }

  .cancel-img {
    width: 20px;
    height: 20px;
  }

  .bg-gray-8 {
    background-color: var(--color-gray-8) !important;
  }

  label.labels {
    font-weight: 600 !important;
    font-size: 14px !important;
  }
  .icons_filter {
    filter: var(--filter);
  }
  /* Responsive */
  @media only screen and (max-width: 1399.98px) {
    .main-profile {
      height: auto;
    }

    .img_avatar {
      height: 160px;
    }
  }

  @media only screen and (max-width: 575.98px) {
    #kt_modal_id_card .modal-dialog {
      max-width: 100% !important;
      margin: 0 1rem !important;
    }
  }
</style>
@endpush

@section('content')
<div class="content pt pt-5 d-flex flex-column flex-column-fluid" id="kt_content">
  <div class="app-container container-xxl">
    <!--begin::Toolbar-->
    <div class="toolbar" id="kt_toolbar">
      <!--begin::Container-->
      <div id="kt_toolbar_container" class="d-flex flex-stack">
        <!--begin::Page title-->
        <div data-kt-swapper="true" data-kt-swapper-mode="prepend"
          data-kt-swapper-parent="{default: '#kt_content_container', 'lg': '#kt_toolbar_container'}"
          class="page-title d-flex flex-column flex-wrap me-3 mb-5 mb-lg-0">
          <!--begin::Title-->
          <h2 class="d-flex text-dark fw-bolder fs-3 align-items-center my-1">Profile </h2>
          <p class="text-grey"><span class="text-primary">Home</span> - Profile</p>
          <!--end::Title-->
        </div>
        <!--end::Page title-->
      </div>
      <!--end::Container-->
    </div>
    <!--end::Toolbar-->

    {{-- Main Content --}}
    @include('personal-trainers.components.main-profile')
    {{-- End Main Content --}}

    {{-- Content Detail Profile --}}
    @include('personal-trainers.components.detail-profile')
    {{-- End Content Detail Profile --}}

    {{-- Content Edit Profile --}}
    @include('personal-trainers.components.edit-profile')
    {{-- End Content Edit Profile --}}

    {{-- Card Authentikasi --}}
    @include('personal-trainers.components.authentikasi')
    {{-- End Card Authentikasi --}}

    <!--begin::Modal - ID Card-->
    @include('personal-trainers.components.id-card')
    <!--end::Modal - ID Card-->
  </div>
</div>
@endsection

@push('js')
<script src="assets/plugins/global/plugins.bundle.js"></script>
<script src="assets/js/scripts.bundle.js"></script>
<!--end::Javascript-->
<script>
  // when change avatar
    $(document).ready(function() {
      $('#edit-avatar').on('change', function(e) {
        const file = e.target.files[0]
        if(file.size <= 10485760) {
          $('#img-avatar').attr('src', window.URL.createObjectURL(file))
        } else {
          Swal.fire({
            title: "Gagal!",
            text: "Ukuran file melebihi 10 mb, silahkan cek kembali",
            icon: "error",
            buttonsStyling: false,
            confirmButtonText: "OK",
            customClass: {
                confirmButton: "btn btn-primary"
            }
          });
        }
      })
    })

  // when button "Edit Profil" clicked
    document.getElementById('btn_edit_profile').addEventListener('click', function() {
      var sectionEdit = document.getElementById('section_edit');
      var sectionDetail = document.getElementById('section_detail');
      var sectionAuth = document.getElementById('section_auth');

      if(sectionEdit) {
        sectionEdit.style.display = 'block';
        sectionAuth.style.display = 'block';
        sectionDetail.style.display = 'none';
      }
    })

    // when button "Batal" clicked
    document.getElementById('cancel-edit').addEventListener('click', function() {
      var sectionEdit = document.getElementById('section_edit');
      var sectionDetail = document.getElementById('section_detail');
      var sectionAuth = document.getElementById('section_auth');

      if(sectionEdit) {
        sectionEdit.style.display = 'none';
        sectionAuth.style.display = 'none';
        sectionDetail.style.display = 'block';
      }
    })
    // cancel change password
    document.getElementById('btn_cancel').addEventListener('click', function() {
      var changePasswordElement = document.getElementById('change-password');
      var listPassword = document.getElementById('list-password');
      if (changePasswordElement) {
        changePasswordElement.style.display = 'none';
        listPassword.style.display = 'block';
      }
    });

    // when button "Ubah Password" clicked
    document.getElementById('btn-change-password').addEventListener('click', function() {
      var changePasswordElement = document.getElementById('change-password');
      var listPassword = document.getElementById('list-password');
      if (changePasswordElement) {
        changePasswordElement.style.display = 'block';
        listPassword.style.display = 'none';
      }
    });

    // show hide password
    const toggleOldPasswordVisibility = () => {
        const passwordInput = document.getElementById("old_password");
        const passwordToggleIcon = document.getElementById("passwordToggleIcon1");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passwordToggleIcon.src = "{{ asset('assets/media/icons/eye.svg') }}"; 
        } else {
            passwordInput.type = "password";
            passwordToggleIcon.src = "{{ asset('assets/media/icons/eye-slash.svg') }}";
        }
    };
    const toggleNewPasswordVisibility = () => {
        const passwordInput = document.getElementById("new_password");
        const passwordToggleIcon = document.getElementById("passwordToggleIcon2");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passwordToggleIcon.src = "{{ asset('assets/media/icons/eye.svg') }}"; 
        } else {
            passwordInput.type = "password";
            passwordToggleIcon.src = "{{ asset('assets/media/icons/eye-slash.svg') }}";
        }
    };
    const toggleConfirmPasswordVisibility = () => {
        const passwordInput = document.getElementById("confirm_password");
        const passwordToggleIcon = document.getElementById("passwordToggleIcon3");

        if (passwordInput.type === "password") {
            passwordInput.type = "text";
            passwordToggleIcon.src = "{{ asset('assets/media/icons/eye.svg') }}"; 
        } else {
            passwordInput.type = "password";
            passwordToggleIcon.src = "{{ asset('assets/media/icons/eye-slash.svg') }}";
        }
    };

    var KTSigninGeneral = function() {
      // Elements
      var form;
      var submitButton;
      var validator;

      // Handle form
      var handleForm = function(e) {
        validator = FormValidation.formValidation(
          form, {
            fields: {
              'old_password': {
                validators: {
                  notEmpty: {
                    message: 'Password lama harus diisi'
                  },
                  stringLength: {
                      min: 8,
                      message: 'Password terdiri dari minimal 8 karakter'
                  }
                }
              },
              'new_password': {
                validators: {
                  notEmpty: {
                    message: 'Password baru harus diisi'
                  },
                  stringLength: {
                      min: 8,
                      message: 'Password terdiri dari minimal 8 karakter'
                  }
                }
              },
              'confirm_password': {
                validators: {
                  notEmpty: {
                    message: 'Konfirmasi password baru harus diisi'
                  },
                  identical: {
                      compare: function() {
                          return form.querySelector('[name="new_password"]').value;
                      },
                      message: 'Konfirmasi password baru tidak sesuai'
                  }
                }
              },
            },
            plugins: {
              trigger: new FormValidation.plugins.Trigger(),
              bootstrap: new FormValidation.plugins.Bootstrap5({
                  rowSelector: '.fv-row'
              })
            }
          }
        );

          // Handle form submit
          submitButton.addEventListener('click', function(e) {
              // Prevent button default action
              e.preventDefault();

              // Validate form
              validator.validate().then(function(status) {
                if (status == 'Valid') {
                  // Show loading indication
                  submitButton.setAttribute('data-kt-indicator', 'on');

                  // Disable button to avoid multiple click
                  submitButton.disabled = true;
                  form.submit();
                  Swal.fire({
                    title: "Berhasil Mengubah Password",
                    text: "Jangan beritahukan password kepada siapapun ya",
                    imageUrl: "{{ asset('assets/media/illustration-profile.svg') }}",
                    buttonsStyling: false,
                    confirmButtonText: "OK",
                    customClass: {
                        confirmButton: "btn btn-primary"
                    }
                  });
                } else {
                    Swal.fire({
                      title: "Gagal Mengubah Password",
                      text: "Maaf, inputan Anda tidak valid, silahkan cek kembali",
                      icon: "error",
                      buttonsStyling: false,
                      confirmButtonText: "OK",
                      customClass: {
                          confirmButton: "btn btn-primary"
                      }
                    });
                }
              });
          });
      }

      // Public functions
      return {
        // Initialization
        init: function() {
          form = document.querySelector('#change-password');
          submitButton = document.querySelector('#kt_change_password');

          handleForm();
        }
      };
  }();

  KTUtil.onDOMContentLoaded(function() {
    KTSigninGeneral.init();
  });
</script>
@endpush