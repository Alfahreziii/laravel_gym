<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absen</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}" sizes="16x16">
    <!-- google fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
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
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
        
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="dark:bg-neutral-800 bg-neutral-100">
    <!-- ✅ NAVBAR HEADER -->
    <div class="flex">
        <div class="bg-white">
            <a href="{{ route('index') }}" class="sidebar-logo">
                <img src="{{ asset('assets/images/logo.png') }}" alt="site logo" class="light-logo">
                <img src="{{ asset('assets/images/logo-light.png') }}" alt="site logo" class="dark-logo">
                <img src="{{ asset('assets/images/logo-icon.png') }}" alt="site logo" class="logo-icon">
            </a>
        </div>
        <div class="navbar-header border-b border-neutral-200 w-full">
            <div class="flex items-center justify-end py-2 px-6">
                <h6 class="text-lg font-semibold text-neutral-700">Sistem Absensi Member</h6>
            </div>
        </div>
    </div>

@if(session('success'))
    <div class="alert alert-success bg-success-50 dark:bg-success-600/25 
        text-success-600 dark:text-success-400 border-success-50 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{ session('success') }}
        </div>
        <button class="remove-button text-success-600 text-2xl">
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif
@if(session('danger'))
    <div class="alert alert-danger bg-danger-100 dark:bg-danger-600/25 
        text-danger-600 dark:text-danger-400 border-danger-100 
        px-6 py-[11px] mb-4 font-semibold text-lg rounded-lg flex items-center justify-between">
        {{ session('danger') }}
        <button class="remove-button text-danger-600 text-2xl"> 
            <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
        </button>
    </div>
@endif

<div class="wrapper w-full p-6">
    <div class="grid grid-cols-12">
        <div class="col-span-12">
            <div class="card border-0 overflow-hidden">
                <div class="card-header flex items-center justify-between">
                    <h6 class="card-title mb-0 text-lg">Kehadiran Member</h6>
                    <button type="button" data-modal-target="popup-modal" data-modal-toggle="popup-modal" class="text-primary-600 focus:bg-primary-600 hover:bg-primary-700 border border-primary-600 hover:text-white focus:text-white focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2 text-center inline-flex items-center dark:text-primary-400 dark:hover:text-white dark:focus:text-white dark:focus:ring-primary-800">+ Tambah Data</button>
                </div>
                <div class="card-body">
                    <table id="selection-table" class="border border-neutral-200 rounded-lg border-separate w-full">
                        <thead>
                            <tr>
                                <th>S.L</th>
                                <th>RFID</th>
                                <th>Foto</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Time</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kehadiranmembers as $index => $item)
                            <tr>
                                <td class="whitespace-nowrap">{{ $index + 1 }}</td>
                                <td class="whitespace-nowrap">{{ $item->rfid }}</td>
                                <td class="whitespace-nowrap">
                                    @if($item->foto)
                                        <img src="{{ asset('storage/' . $item->foto) }}" 
                                            alt="Photo {{ $item->anggota->name }}" 
                                            class="w-10 h-10 rounded-lg object-cover cursor-pointer item-photo"
                                            data-photo="{{ asset('storage/' . $item->foto) }}">
                                    @else
                                        <span class="text-gray-400 italic">No photo</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ $item->anggota->name }}</td>
                                <td class="whitespace-nowrap">{{ $item->status }}</td>
                                <td class="whitespace-nowrap">{{ $item->created_at }}</td>
                                <td class="whitespace-nowrap">
                                    <!-- Form Delete -->
                                    <form action="{{ route('kehadiranmember.destroy', $item->id) }}" method="POST" class="inline-block delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="w-8 h-8 bg-danger-100 text-danger-600 rounded-full inline-flex items-center justify-center delete-btn">
                                            <iconify-icon icon="mingcute:delete-2-line"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add Kehadiran -->
<div id="popup-modal" tabindex="-1"
    class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
    <div class="rounded-2xl bg-white max-w-[800px] w-full">
        <div class="py-4 px-6 border-b border-neutral-200 flex items-center justify-between">
            <h1 class="text-xl">Tambah Kehadiran Member</h1>
            <button data-modal-hide="popup-modal" type="button"
                class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
        </div>

        <div class="p-6">
            <form id="kehadiranForm" action="{{ route('kehadiranmember.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

                <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
                    <div class="col-span-12">
                        <label for="rfid" class="inline-block font-semibold text-neutral-600 text-sm mb-2">RFID</label>
                        <input type="text" id="rfid" name="rfid" class="form-control rounded-lg"
                            placeholder="Masukkan RFID" required>
                    </div>

                    <!-- Live Webcam Preview -->
                    <div class="col-span-12 flex flex-col items-center">
                        <label class="inline-block font-semibold text-neutral-600 text-sm mb-2">Kamera</label>
                        <video id="webcam" autoplay playsinline class="rounded-lg border border-neutral-300 w-64 h-48"></video>
                        <canvas id="canvas" class="hidden"></canvas>
                    </div>

                    <div class="col-span-12">
                        <div class="flex items-center justify-start gap-3 mt-6">
                            <button type="reset" data-modal-hide="popup-modal"
                                class="border border-danger-600 hover:bg-danger-100 text-danger-600 text-base px-10 py-[11px] rounded-lg">
                                Cancel
                            </button>
                            <button type="submit"
                                class="btn btn-primary border border-primary-600 text-base px-6 py-3 rounded-lg">
                                Save
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ✅ TAMBAHKAN SCRIPT DI SINI SEBELUM CLOSING BODY -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const video = document.getElementById('webcam');
    const canvas = document.getElementById('canvas');
    const form = document.getElementById('kehadiranForm');

    // 🟢 Minta akses kamera
    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => video.srcObject = stream)
        .catch(err => console.error('Tidak bisa mengakses kamera:', err));

    // 🟢 Tangkap foto otomatis saat submit
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // Gambar frame dari video ke canvas
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Ubah ke Blob agar bisa dikirim seperti file
        canvas.toBlob((blob) => {
            const file = new File([blob], "foto.png", { type: "image/png" });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);

            // Buat input file tersembunyi jika belum ada
            let fileInput = document.querySelector('input[name="foto"]');
            if (!fileInput) {
                fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'foto';
                fileInput.hidden = true;
                form.appendChild(fileInput);
            }

            fileInput.files = dataTransfer.files;

            // Submit form setelah foto siap
            form.submit();
        }, 'image/png');
    });

    // ✅ Script Delete dengan SweetAlert
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        const btn = form.querySelector('.delete-btn');
        btn.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Apakah kamu yakin?',
                text: "Data item yang dihapus tidak bisa dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e3342f',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // ✅ Remove alert button
    const removeButtons = document.querySelectorAll('.remove-button');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if(alert) {
                alert.remove();
            }
        });
    });

    // ✅ Script untuk menampilkan pop-up foto anggota
    const photos = document.querySelectorAll('.item-photo');
    photos.forEach(photo => {
        photo.addEventListener('click', function () {
            const imageUrl = this.getAttribute('data-photo');
            Swal.fire({
                imageUrl: imageUrl,
                imageAlt: 'Foto Trainer',
                showConfirmButton: false,
                background: 'transparent',
                width: 'auto',
                padding: '0',
                showCloseButton: true,
            });
        });
    });
});
</script>

<!-- ✅ PENTING: Load script DataTable terakhir -->
<x-script script='{!! isset($script) ? $script : "" !!}' />
<script src="{{ asset('assets/js/data-table.js') }}"></script>

</body>
</html>