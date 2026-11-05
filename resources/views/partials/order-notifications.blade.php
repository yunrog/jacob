@auth
    <style>
        .order-connection-status {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 1090;
            max-width: calc(100vw - 2rem);
            padding: .55rem .85rem;
            border: 1px solid #cbd5e1;
            border-radius: 999px;
            background: #fff;
            color: #334155;
            box-shadow: 0 4px 14px rgba(15, 23, 42, .12);
            font: 500 .875rem/1.3 Arial, sans-serif;
        }

        .order-connection-status[data-state="connected"] {
            border-color: #86efac;
            color: #166534;
        }

        .order-connection-status[data-state="error"] {
            border-color: #fca5a5;
            color: #991b1b;
        }

        .order-notification-list {
            position: fixed;
            top: 4.25rem;
            right: 1rem;
            z-index: 1090;
            display: grid;
            gap: .75rem;
            width: min(24rem, calc(100vw - 2rem));
        }

        .order-notification-toast {
            padding: 1rem;
            border: 1px solid #86efac;
            border-left: 4px solid #16a34a;
            border-radius: .75rem;
            background: #fff;
            color: #0f172a;
            box-shadow: 0 12px 30px rgba(15, 23, 42, .16);
            font: 400 .95rem/1.45 Arial, sans-serif;
        }

        .order-notification-toast strong {
            display: block;
            margin-bottom: .2rem;
        }

        @media print {
            .order-connection-status,
            .order-notification-list {
                display: none !important;
            }
        }
    </style>

    <div class="order-connection-status" id="order-connection-status" role="status" aria-live="polite" data-state="connecting">
        Menghubungkan ke Reverb...
    </div>
    <div class="order-notification-list" id="order-notification-list" aria-live="polite" aria-relevant="additions"></div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pusher/8.3.0/pusher.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/laravel-echo/1.16.1/echo.iife.js"></script>
    <script>
        (() => {
            const status = document.getElementById('order-connection-status');
            const notifications = document.getElementById('order-notification-list');
            const configuration = {
                key: @json(config('reverb.apps.apps.0.key')),
                host: @json(config('reverb.apps.apps.0.options.host')),
                port: @json(config('reverb.apps.apps.0.options.port')),
                scheme: @json(config('reverb.apps.apps.0.options.scheme')),
                userId: @json((string) auth()->id()),
                csrfToken: @json(csrf_token()),
            };

            const setStatus = (message, state) => {
                status.textContent = message;
                status.dataset.state = state;
            };

            if (!configuration.key) {
                setStatus('REVERB_APP_KEY belum dikonfigurasi.', 'error');
                return;
            }

            if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') {
                setStatus('Library notifikasi real-time gagal dimuat.', 'error');
                return;
            }

            window.Pusher = Pusher;
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: configuration.key,
                wsHost: configuration.host,
                wsPort: configuration.port,
                wssPort: configuration.port,
                forceTLS: configuration.scheme === 'https',
                enabledTransports: configuration.scheme === 'https' ? ['wss'] : ['ws', 'wss'],
                authEndpoint: @json(url('/broadcasting/auth')),
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': configuration.csrfToken,
                    },
                },
            });

            const connection = window.Echo.connector?.pusher?.connection;

            if (!connection) {
                setStatus('Status koneksi Reverb tidak dapat dibaca.', 'error');
                return;
            }

            connection.bind('state_change', ({ current }) => {
                const states = {
                    connected: ['Reverb tersambung; menunggu transaksi.', 'connected'],
                    connecting: ['Menghubungkan ke Reverb...', 'connecting'],
                    unavailable: ['Server Reverb tidak tersedia.', 'error'],
                    failed: ['Koneksi ke Reverb gagal.', 'error'],
                    disconnected: ['Koneksi ke Reverb terputus.', 'error'],
                };
                const [message, state] = states[current] || ['Status koneksi Reverb tidak diketahui.', 'error'];
                setStatus(message, state);
            });

            connection.bind('error', () => setStatus('Koneksi ke Reverb mengalami kesalahan.', 'error'));

            const channel = window.Echo.private(`user.${configuration.userId}`);

            channel.subscribed(() => setStatus('Reverb tersambung; menunggu transaksi.', 'connected'));
            channel.error(() => setStatus('Gagal mengautentikasi kanal notifikasi.', 'error'));
            channel.listen('.order.placed', (event) => {
                if (!event || event.order_id === undefined || !Number.isFinite(Number(event.total))) {
                    console.error('Data notifikasi transaksi dari server tidak valid.', event);
                    return;
                }

                const toast = document.createElement('div');
                toast.className = 'order-notification-toast';
                toast.setAttribute('role', 'status');

                const title = document.createElement('strong');
                title.textContent = 'Transaksi berhasil';

                const message = document.createElement('span');
                const total = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    maximumFractionDigits: 0,
                }).format(Number(event.total));
                message.textContent = `Order #${event.order_id} telah ditempatkan. Total: ${total}`;

                toast.append(title, message);
                notifications.prepend(toast);
                window.setTimeout(() => toast.remove(), 8000);
            });
        })();
    </script>
@endauth
