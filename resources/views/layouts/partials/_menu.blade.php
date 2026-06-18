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
                <span class="menu-heading fw-bold text-uppercase fs-7">Pelanggaran Siswa</span>
            </div>
        </div>

        <!--begin::Menu item - Data Siswa-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('pelanggaran-siswa.siswa.*') ? 'here show' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-people fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                </span>
                <span class="menu-title">Data Siswa</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.siswa.index') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.siswa.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Daftar Siswa</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.siswa.create') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.siswa.create') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Tambah Siswa</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu item - Kategori Pelanggaran-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('pelanggaran-siswa.kategori.*') ? 'here show' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-category fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                </span>
                <span class="menu-title">Kategori Pelanggaran</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.kategori.index') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.kategori.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Daftar Kategori</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.kategori.create') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.kategori.create') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Tambah Kategori</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu item - Jenis Pelanggaran-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('pelanggaran-siswa.pelanggaran.*') ? 'here show' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-shield-cross fs-2"><span class="path1"></span><span class="path2"></span></i>
                </span>
                <span class="menu-title">Jenis Pelanggaran</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.pelanggaran.index') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.pelanggaran.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Daftar Pelanggaran</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.pelanggaran.create') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.pelanggaran.create') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Tambah Pelanggaran</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu item - Riwayat Pelanggaran-->
        <div data-kt-menu-trigger="click" class="menu-item menu-accordion {{ request()->routeIs('pelanggaran-siswa.riwayat.*') ? 'here show' : '' }}">
            <span class="menu-link">
                <span class="menu-icon">
                    <i class="ki-duotone ki-note-2 fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                </span>
                <span class="menu-title">Riwayat Pelanggaran</span>
                <span class="menu-arrow"></span>
            </span>
            <div class="menu-sub menu-sub-accordion">
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.riwayat.index') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.riwayat.index') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Daftar Riwayat</span>
                    </a>
                </div>
                <div class="menu-item">
                    <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.riwayat.create') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.riwayat.create') }}">
                        <span class="menu-bullet"><span class="bullet bullet-dot"></span></span>
                        <span class="menu-title">Catat Pelanggaran</span>
                    </a>
                </div>
            </div>
        </div>
        <!--end::Menu item-->

        <!--begin::Menu item - Laporan Pelanggaran-->
        <div class="menu-item">
            <a class="menu-link {{ request()->routeIs('pelanggaran-siswa.laporan.*') ? 'active' : '' }}" href="{{ route('pelanggaran-siswa.laporan.index') }}">
                <span class="menu-icon">
                    <i class="ki-duotone ki-chart-simple fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                </span>
                <span class="menu-title">Laporan</span>
            </a>
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
