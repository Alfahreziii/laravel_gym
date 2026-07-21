<head>
    @props(['title' => null])
    @php
        $tenant     = app()->bound('tenant') ? app('tenant') : null;
        $gymName    = $tenant->nama_gym ?? 'HexaGym';
        $faviconUrl = $tenant && $tenant->logo
            ? asset('storage/' . $tenant->logo)
            : asset('assets/images/favicon.png');
    @endphp
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' - ' . $gymName : $gymName }}</title>
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}" sizes="16x16">
    <!-- google fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap"
        rel="stylesheet">
    <!-- remix icon font css  -->
    <link rel="stylesheet" href="{{ asset('assets/css/remixicon.css') }}">

    <!-- Apex Chart css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/apexcharts.css') }}">
    <!-- Data Table css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/dataTables.min.css') }}">
    <!-- Text Editor css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor-katex.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.atom-one-dark.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/editor.quill.snow.css') }}">
    <!-- Date picker css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/flatpickr.min.css') }}">
    <!-- Calendar css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/full-calendar.css') }}">
    <!-- Vector Map css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/jquery-jvectormap-2.0.5.css') }}">
    <!-- Popup css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/magnific-popup.css') }}">
    <!-- Slick Slider css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/slick.css') }}">
    <!-- prism css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/prism.css') }}">
    <!-- file upload css -->
    <link rel="stylesheet" href="{{ asset('assets/css/lib/file-upload.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/lib/audioplayer.css') }}">
    <!-- main css -->
    @vite(['public/assets/scss/style.scss'])

    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        if (localStorage.getItem('color-theme') === 'dark') {
            document.documentElement.classList.add('dark')
        }
    </script>
</head>
