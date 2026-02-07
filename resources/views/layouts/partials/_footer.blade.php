<!--begin::Footer-->
<div class="footer py-4 d-flex flex-lg-column" id="kt_footer">
    <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between">
        <div class="text-gray-900 order-2 order-md-1">
            <span class="text-muted fw-semibold me-1">{{ date('Y') }} &copy;</span>
            <a href="{{ url('/') }}" class="text-gray-800 text-hover-primary">{{ app_setting('app_name', config('app.name')) }}</a>
        </div>
        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1">
            <li class="menu-item">
                <span class="menu-link px-2 text-muted">{{ app_setting('app_version', 'v1.0.0') }}</span>
            </li>
        </ul>
    </div>
</div>
<!--end::Footer-->
