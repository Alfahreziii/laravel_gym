<!-- resources/views/auth/verify-email.blade.php -->
<!DOCTYPE html>
<html lang="en">

<x-head />

<body class="dark:bg-neutral-800 bg-neutral-100">

    <section class="bg-white flex flex-wrap min-h-[100vh]">
        <div class="lg:w-1/2 lg:block hidden">
            <div class="flex items-center flex-col h-full justify-center">
                <img src="{{ asset('assets/images/auth/auth-img.png') }}" alt="">
            </div>
        </div>

        <div class="lg:w-1/2 py-8 px-6 flex flex-col justify-center">
            <div class="lg:max-w-[464px] mx-auto w-full">
                <div>
                    <a href="{{ route('index') }}" class="mb-2.5 max-w-[290px]">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="">
                    </a>
                    <h4 class="mb-3">Verify Your Email</h4>
                    <p class="mb-8 text-secondary-light text-lg">
                        Thanks for signing up! Before getting started, please verify your email by clicking
                        the link we just emailed to you. If you didn’t receive the email, we can send you another.
                    </p>
                </div>

                @if (session('status') == 'verification-link-sent')
                    <div class="mb-4 font-medium text-sm text-green-600">
                        A new verification link has been sent to your email address.
                    </div>
                @endif

                <div class="flex flex-col gap-4">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit"
                            class="btn btn-primary justify-center text-sm btn-sm px-3 py-4 w-full rounded-xl">
                            Resend Verification Email
                        </button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md w-full py-4">
                            Log Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <x-script />

</body>
</html>
