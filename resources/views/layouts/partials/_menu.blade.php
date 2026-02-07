<!--begin::Menu-->
<div class="hover-scroll-overlay-y my-5 my-lg-5" id="kt_aside_menu_wrapper" data-kt-scroll="true"
    data-kt-scroll-activate="{default: false, lg: true}" data-kt-scroll-height="auto"
    data-kt-scroll-dependencies="#kt_aside_toolbar, #kt_aside_footer"
    data-kt-scroll-wrappers="#kt_aside_menu" data-kt-scroll-offset="5px">

    <div class="menu menu-column menu-title-gray-800 menu-state-title-primary menu-state-icon-primary menu-state-bullet-primary menu-arrow-gray-500"
        id="#kt_aside_menu" data-kt-menu="true">

        <!--begin::Menu item - Dashboard-->
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                <span class="menu-icon">
                    <i class="ki-duotone ki-element-11 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                </span>
                <span class="menu-title">Dashboard</span>
            </a>
        </div>
        <!--end::Menu item-->

        <div class="menu-item pt-5">
            <div class="menu-content">
                <span class="menu-heading fw-bold text-uppercase fs-7">Manajemen</span>
            </div>
        </div>

        <!--begin::Menu item - User Management-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('admin.users.*') ? 'here show' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                </span>
                <span class="menu-title">Manajemen User</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.users.index') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Daftar User</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('admin.users.create') ? 'active' : '' }}" href="{{ route('admin.users.create') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Tambah User</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Menu item-->

        <div class="menu-item pt-5">
            <div class="menu-content">
                <span class="menu-heading fw-bold text-uppercase fs-7">Pengaturan</span>
            </div>
        </div>

        <!--begin::Menu item - App Settings-->
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">
                <span class="menu-icon">
                    <i class="ki-duotone ki-setting-2 fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>
                <span class="menu-title">Data Aplikasi</span>
            </a>
        </div>
        <!--end::Menu item-->
    </div>
</div>
<!--end::Menu-->
