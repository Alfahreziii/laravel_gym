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
                        <img src="{{ asset('assets/images/logo.png') }}" alt="">
                    </a>
                    <h4 class="mb-3">Sign Up to your Account</h4>
                    <p class="mb-8 text-secondary-light text-lg">Welcome back! please enter your detail</p>
                </div>
                <form method="POST" action="{{ route('register') }}">
                    @csrf
                    @error('name')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    @error('email')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    @error('password')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    @error('password_confirmation')
                        <span class="text-danger-600 text-sm">{{ $message }}</span>
                    @enderror
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="f7:person"></iconify-icon>
                        </span>
                        <input name="name" autocomplete="name" value="{{ old('name') }}" required type="text" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" placeholder="Username">
                    </div>
                    <div class="icon-field mb-4 relative">
                        <span class="absolute start-4 top-1/2 -translate-y-1/2 pointer-events-none flex text-xl">
                            <iconify-icon icon="mage:email"></iconify-icon>
                        </span>
                        <input type="email" name="email" autocomplete="email" required value="{{ old('email') }}" class="form-control h-[56px] ps-11 border-neutral-300 bg-neutral-50 rounded-xl" placeholder="Email">
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
                    <div class=" mt-6">
                        <div class="flex justify-between gap-2">
                            <div class="form-check style-check flex items-start gap-2">
                                <input class="form-check-input border border-neutral-300 mt-1.5" type="checkbox" value="" id="condition">
                                <label class="text-sm" for="condition">
                                    By creating an account means you agree to the
                                    <a href="javascript:void(0)" class="text-primary-600 font-semibold">Terms & Conditions</a> and our
                                    <a href="javascript:void(0)" class="text-primary-600 font-semibold">Privacy Policy</a>
                                </label>
                            </div>

                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl mt-8"> Sign Up</button>
                    
                    <div class="mt-8 text-center text-sm">
                        <p class="mb-0">Already have an account?  <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline">Sign In</a></p>
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