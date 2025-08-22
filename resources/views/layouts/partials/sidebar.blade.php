<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <!--begin::Logo-->
    <div class="app-sidebar-logo px-6" id="kt_app_sidebar_logo">
        <!--begin::Logo image-->
        <a href="">
            <img alt="Logo" src="{{ asset('assets/media/logos/logo.png') }}"
                class="h-50px mx-auto app-sidebar-logo-default" />
            <img alt="Logo" src="{{ asset('assets/media/logos/logo.png') }}"
                class="h-20px app-sidebar-logo-minimize" />
        </a>
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <i class="ki-duotone ki-black-left-line fs-3 rotate-180">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>

        <!--end::Sidebar toggle-->
    </div>
    <!--end::Logo-->
    <!--begin::sidebar menu-->
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <!--begin::Menu wrapper-->
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper">
            <!--begin::Scroll wrapper-->
            <div id="kt_app_sidebar_menu_scroll" class="scroll-y my-5 mx-3" data-kt-scroll="true"
                data-kt-scroll-activate="true" data-kt-scroll-height="auto"
                data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
                data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px"
                data-kt-scroll-save-state="true">
                <!--begin::Menu-->
                <div class="menu menu-column menu-rounded menu-sub-indention fw-semibold fs-6 online-only"
                    id="#kt_app_sidebar_menu" data-kt-menu="true" data-kt-menu-expand="false">
                    {{-- <div class="menu-item">
                        <a class="menu-link {{ request()->routeIs('dashboard') ? ' active' : '' }}"
                            href="{{ route('dashboard') }}">
                            <div class="menu-icon">
                                <img src="{{ asset('assets/media/icons/cube.svg') }}" alt="Dashboard">
                            </div>
                            <span class="menu-title">Dashboard</span>
                        </a>
                    </div> --}}

                    {{-- @can('dashboard') --}}
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ request()->routeIs('dashboard.*') ? 'show' : '' }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <img src="{{ asset('assets/media/icons/cube.svg') }}" alt="Dashboard">
                            </span>
                            <span class="menu-title">Dashboard</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div
                            class="menu-sub menu-sub-accordion {{ request()->routeIs(['dashboard.*', 'dashboard-income.*']) ? 'show' : '' }}">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{ request()->routeIs('dashboard.*') ? ' active' : '' }}"
                                    href="{{ route('dashboard.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Overview</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            {{-- <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{ request()->routeIs('dashboard-income.*') ? ' active' : '' }}"
                                    href="{{ route('dashboard-income.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Pendapatan</span>
                                </a>
                                <!--end:Menu link-->
                            </div> --}}
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>
                    {{-- @endcan --}}

                    @canany(['permission', 'role', 'admin'])
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs(['admin.*', 'role.*', 'permission.*']) ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                {{-- <span class="menu-icon">
                                <i class="fa-solid fa-user-shield"></i>
                            </span>
                            <span class="menu-title">Menu Admin</span>
                            <span class="menu-arrow"></span> --}}
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-address-book fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                                <span class="menu-title">User Profile</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div class="menu-sub menu-sub-accordion">
                                <!--begin:Menu item-->
                                @can('permission')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('permission.*') ? ' active' : '' }}"
                                            href="{{ route('permission.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Permission</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('role')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('role.*') ? ' active' : '' }}"
                                            href="{{ route('role.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Role</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('admin')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('admin.index') ? ' active' : '' }}"
                                            href="{{ route('admin.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Admin</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                <!--end:Menu item-->
                            </div>
                            <!--end:Menu sub-->
                        </div>
                    @endcanany
                    @canany(['home-slider', 'home-member', 'home-sector', 'home-ads', 'home-documentation',
                        'home-partner'])
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs(['home-slider.*', 'home-member.*', 'home-sector.*', 'home-ads.*', 'home-documentation.*', 'home-partner.*']) ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ki-duotone ki-home fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                                <span class="menu-title">Beranda</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div class="menu-sub menu-sub-accordion">
                                <!--begin:Menu item-->
                                @can('home-slider')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('home-slider.*') ? ' active' : '' }}"
                                            href="{{ route('home-slider.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Slider</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('home-member')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('home-member.*') ? ' active' : '' }}"
                                            href="{{ route('home-member.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Relasi</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('home-sector')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('home-sector.*') ? ' active' : '' }}"
                                            href="{{ route('home-sector.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Sektor Usaha</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('home-ads')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('home-ads.*') ? ' active' : '' }}"
                                            href="{{ route('home-ads.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Iklan</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('booking-ina')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('booking-ina.*') ? ' active' : '' }}"
                                            href="{{ route('booking-ina.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Booking INA</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('home-documentation')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('home-documentation') ? ' active' : '' }}"
                                            href="{{ route('home-documentation.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Dokumentasi</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('home-partner')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('home-partner.*') ? ' active' : '' }}"
                                            href="{{ route('home-partner.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Partner</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endcanany

                    @canany(['media-corner', 'article'])
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs(['article.*', 'media-corner.*']) ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-newspaper"></i>
                                </span>
                                <span class="menu-title">Media Corner</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div
                                class="menu-sub menu-sub-accordion {{ request()->routeIs(['article.*']) ? 'show' : '' }}">
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link {{ request()->routeIs('article.*') ? ' active' : '' }}"
                                        href="{{ route('article.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Artikel</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <div
                                class="menu-sub menu-sub-accordion {{ request()->routeIs(['media-corner.*']) ? 'show' : '' }}">
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link {{ request()->routeIs('media-corner.*') ? ' active' : '' }}"
                                        href="{{ route('media-corner.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Video</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <!--end:Menu sub-->
                        </div>
                    @endcanany

                    @can('event')
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs('event.*') ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </span>
                                <span class="menu-title">Event Management</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div class="menu-sub menu-sub-accordion {{ request()->routeIs(['event.*']) ? 'show' : '' }}">
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link {{ request()->routeIs('event.*') ? ' active' : '' }}"
                                        href="{{ route('event.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Events</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <!--end:Menu sub-->
                        </div>
                    @endcan

                    @can('bpd')
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs('bpd.*') ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="fas fa-building"></i>
                                </span>
                                <span class="menu-title">BPD</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div class="menu-sub menu-sub-accordion {{ request()->routeIs(['bpd.*']) ? 'show' : '' }}">
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link {{ request()->routeIs('bpd.*') ? ' active' : '' }}"
                                        href="{{ route('bpd.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">BPD</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <!--end:Menu sub-->
                        </div>
                    @endcan
                    @can('benefit')
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs('benefit.*') ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    {{-- <i class="ki-duotone ki-heart fs-2">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i> --}}
                                    <i class="ph ph-handshake"></i>
                                </span>
                                <span class="menu-title">Benefit</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div
                                class="menu-sub menu-sub-accordion {{ request()->routeIs(['benefit.*']) ? 'show' : '' }}">
                                <!--begin:Menu item-->
                                <div class="menu-item">
                                    <!--begin:Menu link-->
                                    <a class="menu-link {{ request()->routeIs('benefit.*') ? ' active' : '' }}"
                                        href="{{ route('benefit.index') }}">
                                        <span class="menu-bullet">
                                            <span class="bullet bullet-dot"></span>
                                        </span>
                                        <span class="menu-title">Benefit</span>
                                    </a>
                                    <!--end:Menu link-->
                                </div>
                                <!--end:Menu item-->
                            </div>
                            <!--end:Menu sub-->
                        </div>
                    @endcan

                    {{-- Meeting Room Menu --}}
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ request()->routeIs('meeting-room.*') ? 'show' : '' }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ph ph-buildings"></i>
                            </span>
                            <span class="menu-title">Meeting Room</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div
                            class="menu-sub menu-sub-accordion {{ request()->routeIs(['meeting-room.*']) ? 'show' : '' }}">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{ request()->routeIs('meeting-room.*') ? ' active' : '' }}"
                                    href="{{ route('meeting-room.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Kelola Venue</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>

                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ request()->routeIs(
                            'aboutus-information.*',
                            'aboutus-history.*',
                            'direction-commitment.*',
                            'honorary-council.*',
                            'regional-coordinator.*',
                            'organization.*',
                        )
                            ? 'show'
                            : '' }}">
                        <!--begin:Menu link-->
                        <span class="menu-link">
                            <span class="menu-icon">
                                <i class="ph ph-info"></i>
                            </span>
                            <span class="menu-title">Tentang Kami</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <!--end:Menu link-->
                        <!--begin:Menu sub-->
                        <div
                            class="menu-sub menu-sub-accordion {{ request()->routeIs([
                                'aboutus-information.*',
                                'aboutus-history.*',
                                'direction-commitment.*',
                                'honorary-council.*',
                                'regional-coordinator.*',
                                'organization.*',
                            ])
                                ? 'show'
                                : '' }}">
                            <!--begin:Menu item-->
                            <div class="menu-item">
                                <!--begin:Menu link-->
                                <a class="menu-link {{ request()->routeIs('aboutus-information.*') ? ' active' : '' }}"
                                    href="{{ route('aboutus-information.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Informasi</span>
                                </a>
                                <a class="menu-link {{ request()->routeIs('aboutus-history.*') ? ' active' : '' }}"
                                    href="{{ route('aboutus-history.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Sejarah</span>
                                </a>
                                <a class="menu-link {{ request()->routeIs('direction-commitment.*') ? ' active' : '' }}"
                                    href="{{ route('direction-commitment.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Arah Komitmen</span>
                                </a>
                                <a class="menu-link {{ request()->routeIs('organization.*') ? ' active' : '' }}"
                                    href="{{ route('organization.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Struktur Organisasi</span>
                                </a>
                                <a class="menu-link {{ request()->routeIs('honorary-council.*') ? ' active' : '' }}"
                                    href="{{ route('honorary-council.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Dewan Kehormatan</span>
                                </a>
                                <a class="menu-link {{ request()->routeIs('regional-coordinator.*') ? ' active' : '' }}"
                                    href="{{ route('regional-coordinator.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">Koordinator Wilayah</span>
                                </a>

                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                        </div>
                        <!--end:Menu sub-->
                    </div>

                    @canany(['contact-us', 'application-setting'])
                        <div data-kt-menu-trigger="click"
                            class="menu-item menu-accordion {{ request()->routeIs(['contact-us.*', 'application-setting.*']) ? 'show' : '' }}">
                            <!--begin:Menu link-->
                            <span class="menu-link">
                                <span class="menu-icon">
                                    <i class="ph ph-gear"></i>
                                </span>
                                <span class="menu-title">App Information</span>
                                <span class="menu-arrow"></span>
                            </span>
                            <!--end:Menu link-->
                            <!--begin:Menu sub-->
                            <div
                                class="menu-sub menu-sub-accordion {{ request()->routeIs(['contact-us.*', 'application-setting.*']) ? 'show' : '' }}">
                                <!--begin:Menu item-->
                                @can('contact-us')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('contact-us.*') ? ' active' : '' }}"
                                            href="{{ route('contact-us.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Pesan Pengguna</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                @can('application-setting')
                                    <div class="menu-item">
                                        <!--begin:Menu link-->
                                        <a class="menu-link {{ request()->routeIs('application-setting.*') ? ' active' : '' }}"
                                            href="{{ route('application-setting.index') }}">
                                            <span class="menu-bullet">
                                                <span class="bullet bullet-dot"></span>
                                            </span>
                                            <span class="menu-title">Pengaturan Aplikasi</span>
                                        </a>
                                        <!--end:Menu link-->
                                    </div>
                                @endcan
                                <!--end:Menu item-->
                            </div>
                            <!--end:Menu sub-->
                        </div>
                    @endcanany

                </div>
                <!--end::Menu-->
            </div>
            <!--end::Scroll wrapper-->
        </div>
        <!--end::Menu wrapper-->
    </div>
    <!--end::sidebar menu-->
</div>
