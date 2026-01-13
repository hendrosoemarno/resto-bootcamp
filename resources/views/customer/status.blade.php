<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Order #{{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Pusher JS (Optional for future realtime) -->
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        body {
            font-family: sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen flex flex-col items-center justify-center p-4">

    <div class="bg-white w-full max-w-md rounded-2xl shadow-xl p-6 text-center"
        x-data="orderStatus('{{ $order->order_number }}', '{{ $order->status }}', '{{ $order->payment_status }}', {{ $order->id }})">

        <!-- Icon Status -->
        <div class="mb-4 flex justify-center">
            <template x-if="status === 'PENDING'">
                <div
                    class="w-16 h-16 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center text-3xl">
                    ⏳</div>
            </template>
            <template x-if="status === 'PAID' || status === 'QUEUED'">
                <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-3xl">
                    👨‍🍳</div>
            </template>
            <template x-if="status === 'COOKING'">
                <div
                    class="w-16 h-16 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-3xl">
                    🔥</div>
            </template>
            <template x-if="status === 'READY'">
                <div
                    class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-3xl text-animate-bounce">
                    🔔</div>
            </template>
            <template x-if="status === 'COMPLETED'">
                <div class="w-16 h-16 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center text-3xl">
                    ✅</div>
            </template>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-1">Order #<span x-text="orderNumber.split('-').pop()"></span>
        </h1>
        <p class="text-sm text-gray-500 mb-6">Terima kasih, <span
                class="font-semibold">{{ $order->customer_name }}</span></p>

        <!-- Status Badge -->
        <div class="inline-block px-4 py-2 rounded-lg font-bold text-sm tracking-wide mb-8 transition-colors duration-300"
            :class="{
                'bg-yellow-100 text-yellow-700': status === 'PENDING',
                'bg-blue-100 text-blue-700': status === 'QUEUED' || status === 'PAID',
                'bg-orange-100 text-orange-700': status === 'COOKING',
                'bg-green-100 text-green-700': status === 'READY',
                'bg-gray-200 text-gray-700': status === 'COMPLETED'
             }">
            <span x-text="statusLabel()"></span>
        </div>

        <div class="border-t border-b py-4 mb-6 text-left">
            <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-500">Total Tagihan</span>
                <span class="font-bold">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Status Pembayaran</span>
                <span class="font-bold" :class="paymentStatus === 'PAID' ? 'text-green-600' : 'text-red-500'"
                    x-text="paymentStatus"></span>
            </div>
        </div>

        <!-- Action: Pay (If Pending) -->
        <template x-if="paymentStatus === 'UNPAID'">
            <div class="space-y-3">
                <p class="text-xs text-gray-400">Silakan lakukan pembayaran secara online atau di kasir.</p>

                <!-- Action: Duitku Online Payment -->
                <button @click="payWithDuitku()" :disabled="isPaying"
                    class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <span x-show="isPaying"
                        class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span x-text="isPaying ? 'Menghubungkan...' : '💳 Bayar Sekarang (Online)'"></span>
                </button>

                <!-- Action: Manual Pay Instruction -->
                <button @click="showCashierInstruction = true"
                    class="w-full bg-white border border-gray-300 text-gray-700 font-bold py-3 rounded-xl shadow-sm transition hover:bg-gray-100">
                    💵 Bayar Tunai di Kasir
                </button>

                <!-- Simulation Button for Dev -->
                <button @click="simulatePay()" class="w-full text-xs text-gray-400 hover:text-gray-600 transition">
                    (Simulasi Bayar QRIS)
                </button>
            </div>
        </template>

        <!-- Cashier Instruction Modal -->
        <div x-show="showCashierInstruction" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-6"
            style="display: none;">
            <div class="bg-white rounded-2xl p-6 max-w-sm text-center shadow-2xl"
                @click.outside="showCashierInstruction = false">
                <div class="text-4xl mb-4">🏪</div>
                <h3 class="font-bold text-lg mb-2">Bayar di Kasir</h3>
                <p class="text-gray-600 text-sm mb-6">Silakan menuju meja kasir dan sebutkan Nomor Order Anda:</p>
                <div class="bg-gray-100 p-4 rounded-xl font-mono text-2xl font-bold text-blue-600 mb-6 tracking-wider">
                    <span x-text="orderNumber.split('-').pop()"></span>
                </div>
                <button @click="showCashierInstruction = false"
                    class="w-full bg-blue-600 text-white font-bold py-3 rounded-xl">Tutup</button>
            </div>
        </div>

        <!-- Action: Ready Message -->
        <template x-if="status === 'READY'">
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 animate-pulse">
                <h3 class="font-bold text-green-800 text-lg">Pesanan Siap!</h3>
                <p class="text-green-600 text-sm">Silakan ambil pesanan Anda di counter pengambilan.</p>
            </div>
        </template>

        <p class="mt-8 text-xs text-gray-400">Halaman ini akan update otomatis.</p>
    </div>

    <script>
        function orderStatus(orderNumber, initialStatus, initialPayment, orderId) {
            return {
                orderNumber: orderNumber,
                orderId: orderId,
                status: initialStatus,
                paymentStatus: initialPayment,
                isPaying: false,
                showCashierInstruction: false,

                // Helper: Get API Base URL dynamically
                get apiBase() {
                    const basePath = window.location.pathname.split('/order/status')[0];
                    return basePath + '/api/v1';
                },

                init() {
                    setInterval(() => {
                        if (this.paymentStatus === 'UNPAID' || (this.status !== 'COMPLETED')) {
                            this.checkStatus();
                        }
                    }, 5000);
                },

                statusLabel() {
                    const map = {
                        'PENDING': 'MENUNGGU PEMBAYARAN',
                        'PAID': 'DIBAYAR',
                        'QUEUED': 'DALAM ANTRIAN',
                        'COOKING': 'SEDANG DIMASAK',
                        'READY': 'SIAP DIAMBIL',
                        'COMPLETED': 'SELESAI'
                    };
                    return map[this.status] || this.status;
                },

                async checkStatus() {
                    try {
                        let res = await fetch(`${this.apiBase}/orders/${this.orderNumber}`);
                        let data = await res.json();
                        if (data.status) {
                            this.status = data.status;
                            this.paymentStatus = data.payment_status;
                        }
                    } catch (e) { console.error('Polling error', e); }
                },

                async payWithDuitku() {
                    const paymentUrl = window.location.pathname + '/payment';
                    window.location.href = paymentUrl;
                },

                async simulatePay() {
                    if (!confirm('Simulasikan pembayaran sukses?')) return;
                    try {
                        let res = await fetch(`${this.apiBase}/payments/callback`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                order_number: this.orderNumber,
                                amount: {{ $order->total_amount }},
                                status: 'settlement',
                                method: 'QRIS_SIMULATOR'
                            })
                        });

                        if (res.ok) {
                            this.checkStatus();
                        } else {
                            alert('Gagal simulasi bayar.');
                        }
                    } catch (e) {
                        alert('Error');
                    }
                }
            }
        }
    </script>

</body>

</html>