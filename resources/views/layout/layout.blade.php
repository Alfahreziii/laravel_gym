<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en" data-theme="light">

<x-head />

<body class="dark:bg-neutral-800 bg-neutral-100">

    <!-- ..::  header area start ::.. -->
    <x-sidebar />
    <!-- ..::  header area end ::.. -->

    <main class="dashboard-main">

        <!-- ..::  navbar start ::.. -->
        <x-navbar />
        <!-- ..::  navbar end ::.. -->
        <div class="dashboard-main-body">

            <!-- ..::  breadcrumb  start ::.. -->
            <x-breadcrumb title='{{ isset($title) ? $title : "" }}' subTitle='{{ isset($subTitle) ? $subTitle : "" }}' />
            <!-- ..::  header area end ::.. -->

            @yield('content')

        </div>
        <!-- ..::  footer  start ::.. -->
        <x-footer />
        <!-- ..::  footer area end ::.. -->

    </main>


    @auth
        @if (auth()->user()->hasRole('admin') || auth()->user()->hasRole('spv'))
            <!-- ===== GLOBAL ABSEN NOTIF SYSTEM ===== -->
            <div id="global-absen-overlay"
                style="display:none; position:fixed; top:1.25rem; right:1.25rem; z-index:9999; pointer-events:none;"></div>

            <style>
                @keyframes gNotifIn {
                    from {
                        opacity: 0;
                        transform: translateX(120%)
                    }

                    to {
                        opacity: 1;
                        transform: translateX(0)
                    }
                }

                @keyframes gNotifOut {
                    from {
                        opacity: 1;
                        transform: translateX(0)
                    }

                    to {
                        opacity: 0;
                        transform: translateX(120%)
                    }
                }

                #global-absen-card {
                    animation: gNotifIn .4s cubic-bezier(.34, 1.56, .64, 1) both;
                }
            </style>

            <script>
                (function() {
                    const POLL_URL = '{{ route('absen_notif.latest') }}';
                    const POLL_MS = 3000;
                    const NOTIF_SEC = 8;

                    let lastSeen = Math.floor(Date.now() / 1000);
                    let notifTimer = null;
                    let countTimer = null;
                    let ttsQueue = [];
                    let ttsRunning = false;

                    // ─── TTS ─────────────────────────────────────────────────────────
                    function speakText(text) {
                        if (!window.speechSynthesis) return;
                        ttsQueue.push(text);
                        if (!ttsRunning) flushTTS();
                    }

                    function flushTTS() {
                        if (!ttsQueue.length) {
                            ttsRunning = false;
                            return;
                        }
                        ttsRunning = true;
                        const text = ttsQueue.shift();
                        const utter = new SpeechSynthesisUtterance(text);
                        utter.lang = 'id-ID';
                        utter.rate = 0.95;
                        utter.pitch = 1.05;
                        utter.volume = 1;
                        utter.onend = flushTTS;
                        utter.onerror = flushTTS;

                        function doSpeak() {
                            const voices = window.speechSynthesis.getVoices();
                            const v = voices.find(v => v.lang === 'id-ID') ||
                                voices.find(v => v.lang.startsWith('id')) ||
                                null;
                            if (v) utter.voice = v;
                            window.speechSynthesis.cancel();
                            window.speechSynthesis.speak(utter);
                        }
                        if (window.speechSynthesis.getVoices().length > 0) doSpeak();
                        else window.speechSynthesis.onvoiceschanged = doSpeak;
                    }

                    // ─── Build TTS text ───────────────────────────────────────────────
                    function buildTTS(d) {
                        const st = d.status === 'in' ? 'Check In' : 'Check Out';
                        const sk = d.is_aktif ? 'Member Aktif' : 'Member Tidak Aktif';
                        let t = st + '. ' + d.nama + '. ' + sk + '.';
                        if (d.is_aktif && d.sisa_hari !== null && d.sisa_hari <= 7)
                            t += ' Peringatan, membership hampir habis, sisa ' + d.sisa_hari + ' hari.';
                        else if (!d.is_aktif)
                            t += ' Harap perpanjang membership Anda.';
                        return t;
                    }

                    // ─── Show Notif Card ──────────────────────────────────────────────
                    function showNotif(d) {
                        clearInterval(countTimer);
                        clearTimeout(notifTimer);

                        const isAktif = d.is_aktif;
                        const grad = isAktif ?
                            'linear-gradient(135deg,#0f4c2a 0%,#1a7a40 60%,#22c55e 100%)' :
                            'linear-gradient(135deg,#4c0f0f 0%,#7a1a1a 60%,#ef4444 100%)';
                        const icon = d.status === 'in' ? 'mdi:location-enter' : 'mdi:location-exit';
                        const shieldIcon = isAktif ? 'mdi:shield-check' : 'mdi:shield-alert';
                        const statusLabel = isAktif ? '✓ AKTIF' : '✗ TIDAK AKTIF';
                        const statusBg = isAktif ? 'rgba(34,197,94,.4)' : 'rgba(239,68,68,.4)';
                        const shieldBg = isAktif ? 'rgba(34,197,94,.3)' : 'rgba(239,68,68,.3)';

                        let membershipLine = '';
                        if (isAktif) {
                            let sisaStr = '';
                            if (d.sisa_hari !== null) {
                                const col = d.sisa_hari <= 7 ? '#fde047' : 'rgba(255,255,255,.8)';
                                sisaStr =
                                    ` &bull; <span style="color:${col};font-weight:${d.sisa_hari<=7?'700':'400'}">${d.sisa_hari} hari lagi</span>`;
                            }
                            membershipLine = `<p style="color:rgba(255,255,255,.8);font-size:.75rem;margin:.25rem 0 0">
                    Aktif hingga <strong>${d.tgl_selesai}</strong>${sisaStr}</p>`;
                        } else {
                            membershipLine =
                                `<p style="color:rgba(255,255,255,.8);font-size:.75rem;margin:.25rem 0 0">${d.alasan_tidak_aktif}</p>`;
                        }

                        const fotoHtml = d.foto ?
                            `<img src="${d.foto}" alt="${d.nama}" style="width:64px;height:64px;border-radius:1rem;object-fit:cover;border:2px solid rgba(255,255,255,.4);box-shadow:0 4px 12px rgba(0,0,0,.3)">` :
                            `<div style="width:64px;height:64px;border-radius:1rem;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center">
                     <iconify-icon icon="mdi:account" style="color:white;font-size:2rem"></iconify-icon>
                   </div>`;

                        const html = `
            <div id="global-absen-card"
                 style="position:relative;width:340px;border-radius:1.25rem;
                        overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,.35);background:${grad};pointer-events:auto;">

                <div style="position:absolute;top:-40px;right:-40px;width:160px;height:160px;
                            border-radius:50%;background:rgba(255,255,255,.15)"></div>
                <div style="position:absolute;bottom:-32px;left:-32px;width:128px;height:128px;
                            border-radius:50%;background:rgba(255,255,255,.08)"></div>

                <!-- Header -->
                <div style="padding:1.25rem 1.5rem .75rem;display:flex;align-items:center;justify-content:space-between;position:relative;z-index:1">
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.2);
                                    display:flex;align-items:center;justify-content:center">
                            <iconify-icon icon="${icon}" style="color:white;font-size:1.1rem"></iconify-icon>
                        </div>
                        <span style="color:white;font-weight:700;font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;opacity:.9">
                            CHECK ${d.status.toUpperCase()}
                        </span>
                    </div>
                    <button onclick="window._closeAbsenNotif()" 
                            style="width:28px;height:28px;border:none;background:rgba(255,255,255,.15);border-radius:50%;
                                   cursor:pointer;display:flex;align-items:center;justify-content:center;color:white;
                                   font-size:1rem;transition:background .2s"
                            onmouseover="this.style.background='rgba(255,255,255,.3)'"
                            onmouseout="this.style.background='rgba(255,255,255,.15)'">
                        <iconify-icon icon="iconamoon:sign-times-light"></iconify-icon>
                    </button>
                </div>

                <!-- Body -->
                <div style="padding:0 1.5rem 1.5rem;position:relative;z-index:1">
                    <!-- Foto + Nama -->
                    <div style="display:flex;align-items:center;gap:1rem;margin-bottom:1rem">
                        ${fotoHtml}
                        <div>
                            <p style="color:rgba(255,255,255,.7);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin:0">Nama Member</p>
                            <h2 style="color:white;font-weight:700;font-size:1.2rem;margin:.15rem 0 0;line-height:1.2">${d.nama}</h2>
                        </div>
                    </div>

                    <!-- Status Keanggotaan -->
                    <div style="border-radius:.75rem;padding:1rem;background:rgba(0,0,0,.2);margin-bottom:1rem">
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div style="width:40px;height:40px;border-radius:50%;background:${shieldBg};
                                        flex-shrink:0;display:flex;align-items:center;justify-content:center">
                                <iconify-icon icon="${shieldIcon}" style="color:white;font-size:1.3rem"></iconify-icon>
                            </div>
                            <div style="flex:1">
                                <p style="color:rgba(255,255,255,.7);font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin:0 0 .25rem">
                                    Status Keanggotaan
                                </p>
                                <span style="padding:.2rem .75rem;border-radius:999px;font-size:.75rem;font-weight:700;
                                             background:${statusBg};color:white">
                                    ${statusLabel}
                                </span>
                                ${membershipLine}
                            </div>
                        </div>
                    </div>

                    <!-- Timestamp + Progress -->
                    <div style="display:flex;align-items:center;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:.7rem;display:flex;align-items:center;gap:.25rem">
                            <iconify-icon icon="mdi:clock-outline"></iconify-icon>
                            ${d.waktu}
                        </span>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="width:80px;height:5px;border-radius:999px;background:rgba(255,255,255,.2);overflow:hidden">
                                <div id="g-notif-bar" style="height:100%;background:rgba(255,255,255,.75);width:100%;transition:width linear"></div>
                            </div>
                            <span id="g-notif-cd" style="color:rgba(255,255,255,.6);font-size:.7rem">${NOTIF_SEC}s</span>
                        </div>
                    </div>
                </div>
            </div>`;

                        const overlay = document.getElementById('global-absen-overlay');
                        overlay.innerHTML = html;
                        overlay.style.display = 'block';

                        // Countdown
                        let rem = NOTIF_SEC * 1000;
                        countTimer = setInterval(() => {
                            rem -= 100;
                            const pct = (rem / (NOTIF_SEC * 1000)) * 100;
                            const bar = document.getElementById('g-notif-bar');
                            const cd = document.getElementById('g-notif-cd');
                            if (bar) bar.style.width = pct + '%';
                            if (cd) cd.textContent = Math.ceil(rem / 1000) + 's';
                            if (rem <= 0) {
                                clearInterval(countTimer);
                                closeCard();
                            }
                        }, 100);
                    }

                    function closeCard() {
                        const card = document.getElementById('global-absen-card');
                        if (card) card.style.animation = 'gNotifOut .3s ease forwards';
                        setTimeout(() => {
                            const overlay = document.getElementById('global-absen-overlay');
                            if (overlay) {
                                overlay.style.display = 'none';
                                overlay.innerHTML = '';
                            }
                        }, 300);
                    }

                    window._closeAbsenNotif = function() {
                        clearInterval(countTimer);
                        closeCard();
                    };

                    // ─── Polling ──────────────────────────────────────────────────────
                    async function poll() {
                        try {
                            const res = await fetch(POLL_URL + '?since=' + lastSeen, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });
                            if (!res.ok) return;
                            const json = await res.json();
                            if (json.has_new && json.data) {
                                lastSeen = json.data.timestamp;
                                showNotif(json.data);
                                speakText(buildTTS(json.data));
                            }
                        } catch (e) {}
                    }

                    // Mulai polling setelah DOM ready
                    // Skip jika halaman sudah punya polling sendiri (flag dari halaman absen)
                    document.addEventListener('DOMContentLoaded', () => {
                        if (!window._absenNotifGlobal) {
                            window._absenNotifGlobal = true;
                            setInterval(poll, POLL_MS);
                        }
                    });
                })
                ();
            </script>
        @endif
    @endauth
    <!-- ===== END GLOBAL ABSEN NOTIF ===== -->

    @yield('scripts')
    <!-- ..::  scripts  start ::.. -->
    <x-script script='{!! isset($script) ? $script : "" !!}' />
    <!-- ..::  scripts  end ::.. -->

</body>

</html>