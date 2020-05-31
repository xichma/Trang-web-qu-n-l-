
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    @yield("title")
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @yield("before-css")
    @stack("before-css")
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{asset("assets/backend/pluginsfontawesome-free/css/all.min.css")}}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{asset("assets/backend/dist/css/adminlte.min.css")}}">
    <!-- Google Font: Source Sans Pro -->
    <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
    @yield("after-css")
    @stack("after-css")
</head>
<body class="hold-transition sidebar-mini">
<!-- Site wrapper -->
<div class="wrapper">
    <!-- Navbar -->
    @include("Backend.includes.header")
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    @include("Backend.includes.sidebar")
    <!-- Content Wrapper. Contains page content -->
    @yield("content")
    <!-- /.content-wrapper -->

    @include("Backend.includes.footer")

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
@yield("before-script")
<!-- jQuery -->
<script src="{{asset("assets/backend/pluginsjquery/jquery.min.js")}}"></script>
<!-- Bootstrap 4 -->
<script src="{{asset("assets/backend/pluginsbootstrap/js/bootstrap.bundle.min.js")}}"></script>
<!-- AdminLTE App -->
<script src="{{asset("assets/backend/dist/js/adminlte.min.js")}}"></script>
<!-- AdminLTE for demo purposes -->
<script src="{{asset("assets/backend/dist/js/demo.js")}}"></script>
@stack("after-script")
</body>
</html>
