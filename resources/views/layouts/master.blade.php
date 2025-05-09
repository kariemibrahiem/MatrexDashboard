<!DOCTYPE html>
<html lang="en">
<head>
    @include("layouts.head")
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    @stack('styles')
</head>
<body class="sb-nav-fixed">
@include("layouts.header")

<div id="layoutSidenav">
    <div id="layoutSidenav_nav">
        @include("layouts.sidebar")
    </div>
    <div id="layoutSidenav_content">
        <main>
            <div class="container-fluid px-4">
                @yield("content")
            </div>
        </main>
        @include("layouts.footer")
    </div>
</div>

{{-- Core Scripts --}}
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Use only one Bootstrap JS version (5.3.3 in this case) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

{{-- SB Admin Scripts --}}
<script src="{{ asset('admin/js/scripts.js') }}"></script>

{{-- Chart Scripts --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.8.0/Chart.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('admin/assets/demo/chart-area-demo.js') }}"></script>
<script src="{{ asset('admin/assets/demo/chart-bar-demo.js') }}"></script>

{{-- Simple DataTables --}}
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>

{{-- AJAX Section --}}
@yield('ajaxCalls')
</body>
</html>
