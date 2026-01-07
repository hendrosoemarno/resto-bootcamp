<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian - {{ $restaurant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Oswald', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .blink-soft {
            animation: blinker 2s linear infinite;
        }

        @keyframes blinker {
            50% {
                opacity: 0.5;
            }
        }
    </style>
</head>

<body class="bg-gray-900 text-white h-screen overflow-hidden flex flex-col" x-data="displayApp({{ $restaurant->id }})">

    <!-- Header -->
    <header class="bg-black/50 p-6 flex justify-between items-center border-b border-gray-800">
        <h1 class="text-3xl font-bold tracking-wider">{{ strtoupper($restaurant->name) }}</h1>
        <div class="text-xl text-gray-400" x-text="currentTime"></div>
    </header>

    <!-- Content -->
    <main class="flex-1 flex divide-x divide-gray-800">

        <!-- Column Preparing -->
        <div class="w-1/2 p-8 bg-gray-900">
            <h2 class="text-4xl font-bold text-gray-400 mb-8 flex items-center gap-4">
                <span>👨‍🍳 SEDANG DISIAPKAN</span>
            </h2>

            <div class="grid grid-cols-2 gap-6">
                <template x-for="order in preparing" :key="order.order_number">
                    <div class="bg-gray-800 rounded-xl p-6 border-l-8 border-yellow-500 shadow-lg">
                        <div class="text-6xl font-bold text-white tracking-widest text-center"
                            x-text="formatNumber(order.order_number)"></div>
                        <div class="text-center text-gray-500 mt-2 text-xl" x-text="maskName(order.customer_name)">
                        </div>
                    </div>
                </template>
            </div>
            <template x-if="preparing.length === 0">
                <p class="text-gray-600 text-2xl mt-10 text-center uppercase">Antrian Dapur Kosong</p>
            </template>
        </div>

        <!-- Column Ready -->
        <div class="w-1/2 p-8 bg-green-900/10">
            <h2 class="text-5xl font-bold text-green-500 mb-8 flex items-center gap-4">
                <span class="animate-pulse">🔔 SIAP DIAMBIL</span>
            </h2>

            <div class="grid grid-cols-1 gap-6">
                <template x-for="order in ready" :key="order.order_number">
                    <div class="bg-green-600 rounded-2xl p-8 border-4 border-green-400 shadow-2xl transform scale-105">
                        <div class="flex justify-between items-center">
                            <div class="text-8xl font-bold text-white tracking-widest"
                                x-text="formatNumber(order.order_number)"></div>
                            <div class="text-right">
                                <div class="text-3xl text-green-100 font-bold mb-1" x-text="order.customer_name"></div>
                                <div class="text-green-200 text-lg">Silakan ke Counter</div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>

            <template x-if="ready.length === 0">
                <p class="text-gray-600 text-2xl mt-10 text-center uppercase">Belum ada pesanan siap</p>
            </template>
        </div>
    </main>

    <!-- Running Text Footer -->
    <footer class="bg-orange-600 p-2 overflow-hidden whitespace-nowrap">
        <div class="animate-[marquee_20s_linear_infinite] inline-block text-white font-bold text-lg">
            SELAMAT DATANG DI {{ strtoupper($restaurant->name) }} — SILAKAN SCAN QR DI MEJA UNTUK MEMESAN — TERIMA KASIH
            ATAS KUNJUNGAN ANDA —
            SELAMAT DATANG DI {{ strtoupper($restaurant->name) }} — SILAKAN SCAN QR DI MEJA UNTUK MEMESAN — TERIMA KASIH
            ATAS KUNJUNGAN ANDA
        </div>
    </footer>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    keyframes: {
                        marquee: { '0%': { transform: 'translateX(100%)' }, '100%': { transform: 'translateX(-100%)' } }
                    }
                }
            }
        }

        function displayApp(restaurantId) {
            return {
                restaurantId: restaurantId,
                ready: [],
                preparing: [],
                currentTime: '',

                init() {
                    this.updateTime();
                    setInterval(() => this.updateTime(), 1000);

                    this.fetchData();
                    setInterval(() => this.fetchData(), 5000);

                    // Fullscreen request on click (optional)
                    document.body.addEventListener('click', () => {
                        if (!document.fullscreenElement) document.documentElement.requestFullscreen().catch(() => { });
                    });
                },

                updateTime() {
                    this.currentTime = new Date().toLocaleTimeString('id-ID', { hour12: false });
                },

                async fetchData() {
                    try {
                        let res = await fetch(`{{ url('/api/v1/restaurants') }}/${this.restaurantId}/display/orders`);
                        let data = await res.json();

                        this.ready = data.ready;
                        this.preparing = data.preparing;

                        // Optional: Play sound if new ready item appears
                        // Logic needed to track previous state
                    } catch (e) {
                        console.error('Fetch error');
                    }
                },

                formatNumber(orderStr) {
                    return orderStr.split('-').pop(); // Show last 4 chars
                },

                maskName(name) {
                    // "Budi Santoso" -> "Budi S."
                    if (!name) return '';
                    let parts = name.split(' ');
                    if (parts.length > 1) return parts[0] + ' ' + parts[1][0] + '.';
                    return name;
                }
            }
        }
    </script>
</body>

</html>