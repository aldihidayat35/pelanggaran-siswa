<!--begin::Page title-->
@hasSection('breadcrumb')
@else
<div class="toolbar py-2" id="kt_toolbar">
    <div id="kt_toolbar_container" class="container-fluid d-flex flex-stack">
        @yield('toolbar')
    </div>
</div>
@endif
<!--end::Page title-->
