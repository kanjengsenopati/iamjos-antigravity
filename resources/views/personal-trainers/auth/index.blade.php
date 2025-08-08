<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login | {{ env('APP_NAME') }}</title>
<meta charset="utf-8" />
<meta name="description" content="{{ env('APP_NAME') }}">
<meta name="author" content="{{ env('APP_NAME') }}">
<meta name="robots" content="noindex, nofollow">

<!-- Open Graph Meta -->
<meta property="og:title" content="{{ env('APP_NAME') }}">
<meta property="og:site_name" content="{{ env('APP_NAME') }}">
<meta property="og:description" content="{{ env('APP_NAME') }}">
<meta property="og:type" content="website">
<meta property="og:url" content="">
<meta property="og:image" content="">

<link rel="canonical" href="KSP" />
<link rel="shortcut icon" href="assets/media/logos/favicon.png" />
<!--begin::Fonts-->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700" />
<link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
<style>
    .h-logo {
        height: 4.5rem !important;
        margin-bottom: 4rem;
    }

    #kt_app_root {
        min-height: 100% !important;
        max-height: 64rem !important;
    }

    h1.title {
        font-size: 2.4375rem;
        line-height: 3.125rem;
        /* 128.205% */
        letter-spacing: -0.00056rem;
    }

    p,
    input,
    a {
        font-size: 14px !important;
    }

    #kt_sign_in_submit.btn-primary {
        font-size: 16px !important;
        font-weight: 500;
        width: 8rem;
        border-radius: 1.5rem;
        padding: .5rem 1rem !important;
    }

    .btn-primary {
        border-radius: var(--radius-xl, 1.25rem);
        padding: .5rem 1rem !important;
    }

    .btn-password {
        right: .5rem !important;
        top: .875rem !important;
    }

    #password.form-control.is-valid,
    #password.was-validated .form-control:valid,
    #password.form-control.is-invalid,
    #password.was-validated .form-control:invalid {
        background-position: right calc(2.25em + 0.3875rem) center !important;
    }

    .input_form {
        max-width: 28.125rem;
    }

    /* responsive */
    @media only screen and (max-width: 767.98px) {
        h1.title {
            font-size: 2rem;
        }
    }

    @media only screen and (max-width: 991.98px) {
        .h-logo {
            margin-bottom: 0;
        }

        .input_form {
            max-width: 24rem;
        }
    }

    @media only screen and (max-width: 1199.98px) {
        h1.title {
            font-size: 2.25rem;
        }
    }
</style>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center overflow-x-hidden">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::Root-->
    <div id="kt_app_root">
        <!--begin::Page bg image-->
        <style>
            body {
                background-image: url('assets/media/auth/bg10.jpeg');
            }

            [data-bs-theme="dark"] body {
                background-image: url('assets/media/auth/bg10-dark.jpeg');
            }
        </style>
        <!--end::Page bg image-->
        <div class="py-20 position-absolute w-100 mx-auto d-flex justify-content-center d-lg-none">
            <img src="{{ asset('assets/media/logos/logo.svg') }}" class="h-logo" alt="" />
        </div>

        <!--begin::Authentication - Sign-in -->
        <div class="row align-items-center h-100 px-0">
            <!--begin::Aside-->
            <div class="col-lg-6 px-0 d-flex justify-content-center">
                <!--begin::Aside-->
                <!--begin::Body-->
                <!--begin::Wrapper-->
                <div class="d-flex flex-column flex-center rounded-4">
                    <!--begin::Content-->
                    <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                        <!--begin::Wrapper-->
                        <div class="d-flex flex-center flex-column flex-column-fluid pb-15 pb-lg-20">
                            <!--begin::Form-->
                            <form class="form input_form" novalidate="novalidate" id="kt_sign_in_form" action="{{ route('personal-trainer.authenticate') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <!--begin::Heading-->
                                <div class="mb-8">
                                    <img src="{{ asset('assets/media/logos/logo.svg') }}" class="h-logo d-none d-lg-block" alt="" />
                                    <!--begin::Title-->
                                    <h1 class="text-dark title fw-bold mb-2">Masuk</h1>
                                    <!--end::Title-->
                                    <!--begin::Subtitle-->
                                    <p class="text-gray-500 fw-semibold ">Pantau aktivitas dan jadwal trainer Anda,
                                        mudah dan efisien</p>
                                    <!--end::Subtitle=-->
                                </div>
                                <!--begin::Heading-->
                                <!--begin::Input group=-->
                                <div class="fv-row mb-6">
                                    <!--begin::Email-->
                                    <input type="text" placeholder="Email" name="email" autocomplete="off" class="form-control bg-transparent" value="{{ old('email') }}">
                                    <!--end::Email-->
                                </div>
                                <!--end::Input group=-->
                                <div class="fv-row mb-6">
                                    <!--begin::Password-->
                                    <input id="password" type="password" placeholder="Password" name="password" autocomplete="off" class="form-control position-relative bg-transparent w-100" />
                                    <button type="button" class="bg-transparent position-absolute end-0 btn-password py-0 border-0" onclick="togglePasswordVisibility()">
                                        <img id="passwordToggleIcon" src="{{ asset('assets/media/icons/eye-slash.svg') }}" alt="">
                                    </button>
                                    <!--end::Password-->
                                </div>
                                <!--end::Input group=-->
                                <!--begin::Forgot Password-->
                                <div class="text-end">
                                    <a href="{{ route('forgot-password',['type' => 'PT']) }}" class="text-primary fw-medium">Lupa
                                        password?</a>
                                </div>
                                <!--end::Forgot Password-->
                                <!--begin::Wrapper-->
                                <div class="d-flex flex-stack flex-wrap gap-3 fs-base fw-semibold mb-6">
                                    <div></div>
                                </div>
                                <!--end::Wrapper-->
                                <!--begin::Submit button-->
                                <div class="d-grid">
                                    <button type="submit" id="kt_sign_in_submit" class="btn btn-primary max-w-max">
                                        <!--begin::Indicator label-->
                                        <span class="indicator-label">Masuk</span>
                                        <!--end::Indicator label-->
                                        <!--begin::Indicator progress-->
                                        <span class="indicator-progress">Memuat...
                                            <!--end::Indicator progress-->
                                    </button>
                                </div>
                                <!--end::Submit button-->
                                <!--begin::Sign up-->
                                <!--end::Sign up-->
                            </form>
                            <!--end::Form-->
                        </div>
                        <!--end::Wrapper-->
                    </div>
                    <!--end::Content-->
                </div>
                <!--end::Wrapper-->
                <!--end::Body-->
            </div>
            <!--begin::Content-->
            <div class="col-6 h-100 px-0 d-none d-lg-block">
                <img src="{{ asset('assets/media/login_bg.svg') }}" class="w-100 h-100 object-fit-cover" alt="" />
            </div>
            <!--end::Content-->
        </div>
        <!--end::Authentication - Sign-in-->
    </div>
    <!--end::Root-->
    <!--begin::Javascript-->
    <script>
        var hostUrl = "assets/";
    </script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{asset('assets/plugins/global/plugins.bundle.js')}}"></script>
    <script src="{{asset('assets/js/scripts.bundle.js')}}"></script>
    <!--end::Javascript-->
    @foreach (['success', 'error', 'warning', 'info'] as $message)
    @if (session($message))
    <script>
        Swal.fire({
            title: '{{ ucfirst($message) }}',
            text: "<?= session($message) ?>",
            icon: '{{ $message }}',
            confirmButtonText: 'Ok'
        })
    </script>
    @endif
    @endforeach
    <script>
        "use strict";

        // Class definition
        // show hide password
        const togglePasswordVisibility = () => {
            const passwordInput = document.getElementById("password");
            const passwordToggleIcon = document.getElementById("passwordToggleIcon");

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                passwordToggleIcon.src = "{{ asset('assets/media/icons/eye.svg') }}";
            } else {
                passwordInput.type = "password";
                passwordToggleIcon.src = "{{ asset('assets/media/icons/eye-slash.svg') }}";
            }
        }
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
                            'email': {
                                validators: {
                                    notEmpty: {
                                        message: 'Email harus diisi'
                                    },
                                    emailAddress: {
                                        message: 'Format email tidak sesuai'
                                    },

                                }
                            },
                            'password': {
                                validators: {
                                    notEmpty: {
                                        message: 'Password harus diisi'
                                    }
                                }
                            }
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
                        } else {
                            Swal.fire({
                                text: "Maaf, Inputan anda tidak valid, silahkan cek kembali",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok",
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
                    form = document.querySelector('#kt_sign_in_form');
                    submitButton = document.querySelector('#kt_sign_in_submit');

                    handleForm();
                }
            };
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function() {
            KTSigninGeneral.init();
        });
    </script>
</body>

</html>