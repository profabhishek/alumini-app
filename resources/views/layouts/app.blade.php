<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@yield ('title', 'ICCR Alumni Portal')</title>

    <link rel="stylesheet" href="{{ asset('css/navbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/footer.css') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>.req { color: #e53e3e !important; margin-left: 2px; font-weight: 700; }</style>
    @stack ('styles')
</head>
<body>
    @include ('components.navbar')
    {{-- HTML lives here --}}

    <main>
        @yield ('content')
    </main>

    @include ('components.footer')
    {{-- HTML lives here --}}

    <script src="{{ asset('js/navbar.js') }}"></script>
    @stack ('scripts')

    @if(session('toast'))
        <div id="appToast" class="app-toast app-toast--{{ session('toast')['type'] }}">
            <span class="app-toast__icon">
                @switch(session('toast')['type'])
                    @case('success')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @break
                    @case('error')
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @break
                    @default
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                @endswitch
            </span>
            <span class="app-toast__message">{{ session('toast')['message'] }}</span>
            <button type="button" class="app-toast__close" onclick="document.getElementById('appToast').remove()">&times;</button>
        </div>

        <style>
            .app-toast {
                position: fixed;
                bottom: 24px;
                left: 50%;
                transform: translate(-50%, 16px);
                z-index: 2000;
                display: flex;
                align-items: center;
                gap: 12px;
                max-width: calc(100vw - 32px);
                background: #1C2331;
                color: #fff;
                padding: 14px 18px;
                border-radius: 12px;
                box-shadow: 0 16px 40px rgba(0, 0, 0, 0.25);
                font-size: 14px;
                font-weight: 600;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                opacity: 0;
                transition: opacity 0.25s ease, transform 0.25s ease;
            }
            .app-toast.show {
                opacity: 1;
                transform: translate(-50%, 0);
            }
            .app-toast__icon {
                flex-shrink: 0;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .app-toast--success .app-toast__icon { color: #4ade80; }
            .app-toast--info    .app-toast__icon { color: #60a5fa; }
            .app-toast--error   .app-toast__icon { color: #f87171; }
            .app-toast__message {
                line-height: 1.4;
            }
            .app-toast__close {
                background: none;
                border: none;
                color: rgba(255, 255, 255, 0.6);
                font-size: 18px;
                line-height: 1;
                cursor: pointer;
                flex-shrink: 0;
                padding: 0 0 0 4px;
            }
            .app-toast__close:hover {
                color: #fff;
            }
            @media (max-width: 480px) {
                .app-toast {
                    left: 16px;
                    right: 16px;
                    transform: translateY(16px);
                    max-width: none;
                }
                .app-toast.show {
                    transform: translateY(0);
                }
            }
        </style>

        <script>
            (function () {
                const toast = document.getElementById('appToast');
                if (!toast) return;

                requestAnimationFrame(() => toast.classList.add('show'));

                setTimeout(() => {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }, 5000);
            })();
        </script>
    @endif
</body>
</html>
