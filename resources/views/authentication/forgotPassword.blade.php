<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<x-head />  

<body class="dark:bg-neutral-800 bg-neutral-100">

    <section class="bg-white flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:block hidden">
            <div class="flex items-center flex-col h-full justify-center">
                <img src="{{ asset('assets/images/auth/forgot-pass-img.png') }}" alt="" class="h-full">
            </div>
        </div>
        <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div>
                    <h4 class="mb-3">Forgot Password</h4>
                    <p class="mb-8 text-secondary-light text-lg">Enter the email address associated with your account and we will send you a link to reset your password.</p>
                </div>
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="icon-field mb-6 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" name="email" required class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" placeholder="Email">
                    </div>
                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl">
                        Continue
                    </button>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-primary-600 font-bold mt-6 hover:underline">Back to Sign In</a>
                    </div>

                    <div class="mt-10 md:mt-[60px] lg:mt-[100px] xl:mt-[120px] text-center text-sm">
                        <p class="mb-0">Already have an account?  <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline">Sign In</a></p>
                    </div>

                </form>
            </div>
        </div>
    </section>

    <x-script/>

    <div id="popup-modal" tabindex="-1" class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-10 w-full max-w-[496px] max-h-full rounded-2xl bg-white">
            <button type="button" class="absolute top-4 end-4 text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="popup-modal">
                <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
                <span class="sr-only">Close modal</span>
            </button>
            <div class="p-2.5 text-center">
                <h6 class="mb-3">Verify your Email</h6>
                <p class="text-secondary-light text-sm mb-0">Thank you, check your email for instructions to reset your password</p>
                <button type="button" data-modal-hide="popup-modal" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8">Skip</button>
            </div>
        </div>
    </div>
@if (session('status'))
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const modal = document.getElementById('popup-modal');

        // Jika kamu pakai Flowbite
        if (window.Flowbite && typeof Flowbite.Modal === 'function') {
            const modalInstance = new Flowbite.Modal(modal);
            modalInstance.show();
        } else {
            // Kalau gak pakai Flowbite, tampilkan manual
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // ✅ Tambahkan event listener agar bisa ditutup manual
        const closeButtons = modal.querySelectorAll('[data-modal-hide="popup-modal"]');
        closeButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });
    });
</script>
@endif


</body>
</html>
