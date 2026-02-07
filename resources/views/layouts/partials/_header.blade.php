<!--begin::Header-->
<div id="kt_header" class="header align-items-stretch">
    <!--begin::Brand-->
    <div class="header-brand">
        <a href="{{ route('admin.dashboard') }}">
            @if(app_setting('app_logo'))
                <img alt="Logo" src="{{ asset('storage/' . app_setting('app_logo')) }}" class="h-25px h-lg-25px"/>
            @else
                <span class="text-white fw-bold fs-4">{{ app_setting('app_name', config('app.name')) }}</span>
            @endif
        </a>

        <div id="kt_aside_toggle"
            class="btn btn-icon w-auto px-0 btn-active-color-primary aside-minimize"
            data-kt-toggle="true"
            data-kt-toggle-state="active"
            data-kt-toggle-target="body"
            data-kt-toggle-name="aside-minimize">
            <i class="ki-duotone ki-entrance-right fs-1 me-n1 minimize-default"><span class="path1"></span><span class="path2"></span></i>
            <i class="ki-duotone ki-entrance-left fs-1 minimize-active"><span class="path1"></span><span class="path2"></span></i>
        </div>

        <div class="d-flex align-items-center d-lg-none me-n2" title="Show aside menu">
            <div class="btn btn-icon btn-active-color-primary w-30px h-30px" id="kt_aside_mobile_toggle">
                <i class="ki-duotone ki-abstract-14 fs-1"><span class="path1"></span><span class="path2"></span></i>
            </div>
        </div>
    </div>
    <!--end::Brand-->

    <!--begin::Toolbar-->
    <div class="toolbar d-flex align-items-stretch">
        <div class="container-fluid py-6 py-lg-0 d-flex flex-column flex-lg-row align-items-lg-stretch justify-content-lg-between">
            <div class="page-title d-flex justify-content-center flex-column me-5">
                <h1 class="d-flex flex-column text-gray-900 fw-bold fs-3 mb-0">
                    @yield('page-title', 'Dashboard')
                </h1>
                @yield('breadcrumb')
            </div>

            <div class="d-flex align-items-stretch overflow-auto pt-3 pt-lg-0">
                <div class="d-flex align-items-center">
                    <!--begin::Theme mode-->
                    <div class="d-flex align-items-center ms-3">
                        <a href="#" class="btn btn-icon btn-custom btn-active-color-primary"
                            data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                            data-kt-menu-placement="bottom-end">
                            <i class="ki-duotone ki-night-day theme-light-show fs-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span></i>
                            <i class="ki-duotone ki-moon theme-dark-show fs-1"><span class="path1"></span><span class="path2"></span></i>
                        </a>
                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                            data-kt-menu="true" data-kt-element="theme-mode-menu">
                            <div class="menu-item px-3 my-0">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-duotone ki-night-day fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span><span class="path6"></span><span class="path7"></span><span class="path8"></span><span class="path9"></span><span class="path10"></span></i>
                                    </span>
                                    <span class="menu-title">Light</span>
                                </a>
                            </div>
                            <div class="menu-item px-3 my-0">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-duotone ki-moon fs-2"><span class="path1"></span><span class="path2"></span></i>
                                    </span>
                                    <span class="menu-title">Dark</span>
                                </a>
                            </div>
                            <div class="menu-item px-3 my-0">
                                <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                                    <span class="menu-icon" data-kt-element="icon">
                                        <i class="ki-duotone ki-screen fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                                    </span>
                                    <span class="menu-title">System</span>
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Theme mode-->

                    <!--begin::User menu-->
                    <div class="d-flex align-items-center ms-1 ms-lg-3">
                        <div class="cursor-pointer symbol symbol-30px symbol-md-40px"
                            data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                            data-kt-menu-placement="bottom-end">
                            @if(auth()->user()->avatar)
                                <img alt="avatar" src="{{ asset('storage/' . auth()->user()->avatar) }}"/>
                            @else
                                <div class="symbol-label fs-5 fw-semibold bg-primary text-inverse-primary">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>

                        <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px"
                            data-kt-menu="true">
                            <div class="menu-item px-3">
                                <div class="menu-content d-flex align-items-center px-3">
                                    <div class="symbol symbol-50px me-5">
                                        @if(auth()->user()->avatar)
                                            <img alt="avatar" src="{{ asset('storage/' . auth()->user()->avatar) }}"/>
                                        @else
                                            <div class="symbol-label fs-2 fw-semibold bg-primary text-inverse-primary">
                                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column">
                                        <div class="fw-bold d-flex align-items-center fs-5">
                                            {{ auth()->user()->name }}
                                            <span class="badge badge-light-success fw-bold fs-8 px-2 py-1 ms-2">{{ ucfirst(auth()->user()->role) }}</span>
                                        </div>
                                        <a href="#" class="fw-semibold text-muted text-hover-primary fs-7">{{ auth()->user()->email }}</a>
                                    </div>
                                </div>
                            </div>
                            <div class="separator my-2"></div>
                            <div class="menu-item px-5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="menu-link px-5 border-0 bg-transparent w-100 text-start">
                                        Sign Out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!--end::User menu-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Toolbar-->
</div>
<!--end::Header-->
