<!DOCTYPE html>
<html lang="en" class="h-100">


<head>
    <meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>@yield('title', config('app.name'))</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon.png">
    <link href="/assets/errors/css/style.css" rel="stylesheet">
    
</head>

<body class="vh-100">
    <div class="authincation h-100">
        <div class="container h-100">
                @yield('content')
        </div>
    </div>
<!--**********************************
	Scripts
***********************************-->
<!-- Required vendors -->
<script src="/assets/errors/vendor/global/global.min.js"></script>
<script src="/assets/errors/vendor/bootstrap-select/dist/js/bootstrap-select.min.js"></script>
<script src="/assets/errors/js/custom.min.js"></script>
<script src="/assets/errors/js/deznav-init.js"></script>

</body>

</html>