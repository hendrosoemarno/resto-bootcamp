<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Metode Pembayaran - #{{ $order->order_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .glass {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>
</head>

<body class="bg-slate-50 min-h-screen pb-12">
    <div class="max-w-md mx-auto" x-data="paymentSelector()">
        <!-- Header -->
        <div class="bg-white p-6 shadow-sm sticky top-0 z-10">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('customer.status', $order->order_number) }}"
                    class="p-2 hover:bg-gray-100 rounded-full transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </a>
                <h1 class="text-xl font-bold text-gray-800">Metode Pembayaran</h1>
            </div>

            <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm text-orange-600 font-medium">Total Tagihan</span>
                    <span class="text-lg font-bold text-orange-700">Rp
                        {{ number_format($order->total_amount, 0, ',', '.') }}</span>
                </div>
                <p class="text-xs text-orange-400">Order #{{ $order->order_number }}</p>
            </div>
        </div>

        <div class="p-6">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Metode Tersedia</h2>

            <div class="space-y-3">
                @forelse($methods as $method)
                    <button
                        @click="selectMethod('{{ $method['paymentMethod'] }}', '{{ $method['paymentName'] }}', '{{ $method['paymentImage'] }}')"
                        class="w-full bg-white p-4 rounded-2xl border border-transparent shadow-sm hover:border-orange-500 transition-all flex items-center justify-between group">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 flex items-center justify-center bg-gray-50 rounded-xl p-2">
                                <img src="{{ $method['paymentImage'] }}" alt="{{ $method['paymentName'] }}"
                                    class="max-w-full max-h-full object-contain">
                            </div>
                            <div class="text-left">
                                <p class="font-bold text-gray-800 group-hover:text-orange-600 transition">
                                    {{ $method['paymentName'] }}</p>
                                <p class="text-xs text-gray-500">Biaya: Rp
                                    {{ number_format($method['totalFee'] ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-gray-300 group-hover:text-orange-500 transition" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                @empty
                    <div class="text-center py-12 bg-white rounded-2xl border border-dashed">
                        <p class="text-gray-400">Tidak ada metode pembayaran aktif.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Selection Modal -->
        <div x-show="selectedMethod" x-cloak class="fixed inset-0 z-50 flex flex-col justify-end">
            <div @click="selectedMethod = null"
                class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity"></div>

            <div class="glass relative rounded-t-[32px] p-8 pb-12 translate-y-0 transition-transform shadow-2xl">
                <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto mb-8"></div>

                <h3 class="text-xl font-bold text-gray-800 mb-6 text-center">Konfirmasi Pembayaran</h3>

                <div class="flex items-center justify-between bg-white/50 p-4 rounded-2xl mb-8">
                    <div class="flex items-center gap-4">
                        <img :src="selectedImage" class="w-10 h-10 object-contain">
                        <span class="font-bold text-gray-700" x-text="selectedName"></span>
                    </div>
                    <button @click="selectedMethod = null" class="text-sm text-gray-400 font-medium">Ganti</button>
                </div>

                <button @click="processPayment()" :disabled="isProcessing"
                    class="w-full bg-orange-600 hover:bg-orange-700 disabled:bg-gray-300 text-white font-bold py-4 rounded-2xl shadow-lg shadow-orange-200 transition-all flex items-center justify-center gap-3">
                    <template x-if="isProcessing">
                        <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </template>
                    <span x-text="isProcessing ? 'Memproses...' : 'Bayar Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        function paymentSelector() {
            return {
                selectedMethod: null,
                selectedName: '',
                selectedImage: '',
                isProcessing: false,
                orderId: {{ $order->id }},

                get apiBase() {
                    const basePath = window.location.pathname.split('/order/status')[0];
                    return basePath + '/api/v1';
                },

                selectMethod(code, name, image) {
                    this.selectedMethod = code;
                    this.selectedName = name;
                    this.selectedImage = image;
                },

                async processPayment() {
                    if (this.isProcessing) return;
                    this.isProcessing = true;

                    try {
                        const response = await fetch(`${this.apiBase}/payment/duitku/create`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: this.orderId,
                                payment_method: this.selectedMethod
                            })
                        });

                        const result = await response.json();

                        if (response.ok && result.data.payment_url) {
                            window.location.href = result.data.payment_url;
                        } else {
                            alert('Kesalahan: ' + (result.message || 'Gagal membuat invoice'));
                            this.isProcessing = false;
                        }
                    } catch (e) {
                        alert('System Error: ' + e.message);
                        this.isProcessing = false;
                    }
                }
            }
        }
    </script>
</body>

</html>