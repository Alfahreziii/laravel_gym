<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<x-head/>

<body class="dark:bg-neutral-800 bg-neutral-100">

    <section class="bg-white flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:block hidden">
            <div class="flex items-center flex-col h-full justify-center">
                <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="" class="h-full">
            </div>
        </div>
        <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div>
                    <a href="{{ route('index') }}" class="mb-2.5 max-w-[135px]">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="" class="h-auto w-full">
                    </a>
                    <h4 class="mb-3">Sign In to your Account</h4>
                    <p class="mb-8 text-secondary-light text-lg">Welcome back! please enter your detail</p>
                </div>
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    @error('email')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    @error('password')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl"
                            placeholder="Email" required autofocus>
                        </div>


                    <div class="relative mb-5">
                        <div class="icon-field">
                            <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                            </span>
                            <input type="password" name="password" autocomplete="current-password"
                                class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl"
                                id="your-password" placeholder="Password" required>
                        </div>
                        <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light"
                        data-toggle="#your-password"></span>
                    </div>


                    <div class="mt-7">
                        <div class="flex justify-between gap-2">
                            <div class="flex items-center">
                                <input name="remember" id="remember" type="checkbox"
                                    class="form-check-input border border-neutral-300">
                                <label class="ps-2" for="remember">Remember me</label>
                            </div>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-primary-600 font-medium hover:underline">
                                    Forgot Password?
                                </a>
                            @endif
                        </div>
                    </div>

                    <button type="submit"
                            class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8">
                        Sign In
                    </button>

                    <div class="mt-8 text-center text-sm">
                        <p class="mb-0">Don't have an account? <a href="{{ route('register') }}" class="text-primary-600 font-semibold hover:underline">Sign Up</a></p>
                    </div>
                </form>

            </div>
        </div>
    </section>
                    
    <x-script />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePassword = document.querySelectorAll('.toggle-password');

            togglePassword.forEach(function(toggle) {
                toggle.addEventListener('click', function() {
                    const input = document.querySelector(this.getAttribute('data-toggle'));
                    if (input) {
                        if (input.type === 'password') {
                            input.type = 'text';
                            this.classList.add('ri-eye-off-line');
                        } else {
                            input.type = 'password';
                            this.classList.remove('ri-eye-off-line');
                        }
                    }
                });
            });
        });
    </script>

</body>
</html>
