<!--begin::Aside-->
<div id="kt_aside" class="aside"
    data-kt-drawer="true"
    data-kt-drawer-name="aside"
    data-kt-drawer-activate="{default: true, lg: false}"
    data-kt-drawer-overlay="true"
    data-kt-drawer-width="{default:'200px', '300px': '250px'}"
    data-kt-drawer-direction="start"
    data-kt-drawer-toggle="#kt_aside_mobile_toggle">

    <!--begin::Aside Toolbar-->
    <div class="aside-toolbar flex-column-auto" id="kt_aside_toolbar">
        <div class="aside-user d-flex align-items-sm-center justify-content-center py-5">
            <div class="symbol symbol-50px">
                @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="avatar"/>
                @else
                    <div class="symbol-label fs-2 fw-semibold bg-primary text-inverse-primary">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
            </div>
            <div class="aside-user-info flex-row-fluid flex-wrap ms-5">
                <div class="d-flex">
                    <div class="flex-grow-1 me-2">
                        <a href="#" class="text-white text-hover-primary fs-6 fw-bold">{{ auth()->user()->name }}</a>
                        <span class="text-gray-600 fw-semibold d-block fs-8 mb-1">{{ ucfirst(auth()->user()->role) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Aside Toolbar-->

    <!--begin::Aside menu-->
    <div class="aside-menu flex-column-fluid">
        @include('layouts.partials._menu')
    </div>
    <!--end::Aside menu-->

    <!--begin::Footer-->
    <div class="aside-footer flex-column-auto py-5" id="kt_aside_footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-flex btn-custom btn-danger w-100" data-bs-toggle="tooltip" title="Logout dari aplikasi">
                <span class="btn-label">Logout</span>
                <i class="ki-duotone ki-entrance-left ms-2 fs-2"><span class="path1"></span><span class="path2"></span></i>
            </button>
        </form>
    </div>
    <!--end::Footer-->
</div>
<!--end::Aside-->
