<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<x-head />  

<body class="dark:bg-neutral-800 bg-neutral-100">
    <div class="card basic-data-table border-0 overflow-hidden">
        <div class="card-body pb-10 lg:pb-[60px] xl:pb-[80px] px-8 text-center">
            <img src="{{ asset('assets/images/error-img.png') }}" alt="" class="mb-6 mx-auto">
            <h6 class="mb-4">Page not Found</h6>
            <p class="text-secondary-light">Sorry, the page you are looking for doesn't exist </p>
            @php
                $homeRoute = 'login';
                if(auth()->check()){
                    $roles = auth()->user()->getRoleNames();
                    if($roles->contains('guest')) $homeRoute = route('kehadiranmember.index');
                    elseif($roles->contains('admin')) $homeRoute = route('dashboard');
                    elseif($roles->contains('spv')) $homeRoute = route('dashboard');
                }
            @endphp

            <a href="{{ $homeRoute }}" class="btn btn-primary-600 rounded-lg px-5 pb-[11px] mt-10">
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
