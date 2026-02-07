<!--begin::Aside-->
<div
	id="kt_aside"
	class="aside"
	data-kt-drawer="true"
	data-kt-drawer-name="aside"
	data-kt-drawer-activate="{default: true, lg: false}"
	data-kt-drawer-overlay="true"
	data-kt-drawer-width="{default:'200px', '300px': '250px'}"
	data-kt-drawer-direction="start"
	data-kt-drawer-toggle="#kt_aside_mobile_toggle"
	>
    <!--begin::Aside Toolbarl-->
    <div class="aside-toolbar flex-column-auto" id="kt_aside_toolbar">
        <!--begin::Aside user-->
@include('layout/aside/_toolbar')
        <!--end::Aside user-->
    </div>
    <!--end::Aside Toolbarl-->
    <!--begin::Aside menu-->
	<div class="aside-menu flex-column-fluid">
@include('layout/aside/_menu')
    </div>
    <!--end::Aside menu-->
    <!--begin::Footer-->
    <div class="aside-footer flex-column-auto py-5" id="kt_aside_footer">
        <a href="https://preview.keenthemes.com/html/metronic/docs" class="btn btn-flex btn-custom btn-primary w-100" data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-dismiss-="click" title="200+ in-house components and 3rd-party plugins">
            <span class="btn-label">
                Docs & Components
            </span>
            <i class="ki-duotone ki-document ms-2 fs-2"><span class="path1"></span><span class="path2"></span></i>        </a>
    </div>
    <!--end::Footer-->
</div>
<!--end::Aside-->
