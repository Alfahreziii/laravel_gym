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

    {{-- Modal konfirmasi email terkirim --}}
    <x-modal id="popup-modal" title="Verify your Email">
        <x-slot:body>
            <div class="text-center py-2">
                <p class="text-secondary-light text-sm mb-0">Thank you, check your email for instructions to reset your password</p>
                <button type="button" data-close-modal="popup-modal" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8">
                    Skip
                </button>
            </div>
        </x-slot:body>
    </x-modal>

    @if (session('status'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            HexaModal.show('popup-modal');
        });
    </script>
    @endif

</body>
</html>
