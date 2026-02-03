@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            💪 {{ config('app.name') }}
        @endcomponent
    @endslot

    {{-- Body --}}
    {{-- Greeting --}}
    @if (!empty($greeting))
        {{ $greeting }}
    @else
        @if (isset($level) && $level === 'error')
            Whoops!
        @else
            Hello!
        @endif
    @endif

    {{-- Intro Lines --}}
    @if (isset($introLines))
        @foreach ($introLines as $line)
            {{ $line }}
        @endforeach
    @endif

    {{-- Action Button --}}
    @isset($actionText)
        {{ $actionText }}: {{ $actionUrl }}
    @endisset

    {{-- Outro Lines --}}
    @if (isset($outroLines))
        @foreach ($outroLines as $line)
            {{ $line }}
        @endforeach
    @endif

    {{-- Salutation --}}
    @if (!empty($salutation))
        {{ $salutation }}
    @else
        Salam Sehat,
        Tim {{ config('app.name') }}
    @endif

    {{-- Subcopy --}}
    @isset($actionText)
        @slot('subcopy')
            @component('mail::subcopy')
                Jika Anda mengalami kesulitan mengklik tombol "{{ $actionText }}", salin dan tempel URL di bawah ini ke browser web
                Anda:
                {{ $actionUrl }}
            @endcomponent
        @endslot
    @endisset

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            © {{ date('Y') }} Kenzo Fitness Center. All rights reserved.

            Build Your Body, Build Your Life
        @endcomponent
    @endslot
@endcomponent
