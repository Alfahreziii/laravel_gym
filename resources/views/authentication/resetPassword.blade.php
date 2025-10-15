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
                    <h4 class="mb-3">Reset Password</h4>
                    <p class="mb-8 text-secondary-light text-lg">Enter the email address associated with your account and we will send you a link to reset your password.</p>
                </div>
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf
                    @error('email')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">
                   
                    <div class="icon-field mb-6 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" name="email" value="{{ old('email', $request->email) }}" required class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" placeholder="Email">
                    </div>

                    <div class="mb-5">
                        <div class="relative">
                            <div class="icon-field">
                                <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                    <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                </span>
                                <input type="password" name="password" required autocomplete="new-password" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" id="password" placeholder="Password">
                            </div>
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#password"></span>
                        </div>
                        
                    </div>
                    <div class="mb-5">
                        <div class="relative">
                            <div class="icon-field">
                                <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                                    <iconify-icon icon="solar:lock-password-outline"></iconify-icon>
                                </span>
                                <input type="password" name="password_confirmation" required autocomplete="new-password" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" id="password_confirmation" placeholder="Confirm Password">
                            </div>
                            <span class="toggle-password ri-eye-line cursor-pointer absolute end-0 top-1/2 -translate-y-1/2 me-4 text-secondary-light" data-toggle="#password_confirmation"></span>
                        </div>
                        
                    </div>

                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl">
                        Reset Password
                    </button>

                    <div class="text-center">
                        <a href="{{ route('login') }}" class="text-primary-600 font-bold mt-6 hover:underline">Back to Sign In</a>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <x-script/>

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
