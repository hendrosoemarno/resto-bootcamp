<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen" x-data="cashierApp()">

    <!-- Navbar -->
    <header class="bg-white shadow px-6 py-4 flex justify-between items-center sticky top-0 z-10">
        <h1 class="text-xl font-bold flex items-center gap-2 text-gray-800">
            💻 Kasir Dashboard
        </h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-600 text-sm font-semibold" x-text="currentUser.name"></span>
            <button @click="logout" class="text-red-500 hover:text-red-700 text-sm font-bold">Logout</button>
        </div>
    </header>

    <main class="max-w-6xl mx-auto p-6">

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-yellow-500">
                <div class="text-gray-500 text-xs font-bold uppercase">Menunggu Pembayaran</div>
                <div class="text-3xl font-bold text-gray-800" x-text="unpaidOrders.length">0</div>
            </div>
            <div class="bg-white p-6 rounded-xl shadow-sm border-l-4 border-green-500">
                <div class="text-gray-500 text-xs font-bold uppercase">Lunas Hari Ini</div>
                <div class="text-3xl font-bold text-gray-800" x-text="paidOrders.length">0</div>
            </div>
        </div>

        <!-- Order List -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="font-bold text-lg text-gray-800">Daftar Tagihan (Unpaid)</h2>
                <button @click="fetchOrders()" class="text-blue-600 text-sm hover:underline">Refresh</button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="p-4">Order No</th>
                            <th class="p-4">Meja</th>
                            <th class="p-4">Pelanggan</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Waktu</th>
                            <th class="p-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <template x-if="unpaidOrders.length === 0">
                            <tr>
                                <td colspan="6" class="p-8 text-center text-gray-400">Tidak ada tagihan yang belum
                                    dibayar.</td>
                            </tr>
                        </template>

                        <template x-for="order in unpaidOrders" :key="order.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-mono font-bold text-blue-600" x-text="order.order_number"></td>
                                <td class="p-4"><span
                                        class="bg-gray-200 text-gray-700 px-2 py-1 rounded text-xs font-bold"
                                        x-text="order.table?.table_number || 'TAKEAWAY'"></span></td>
                                <td class="p-4 font-medium" x-text="order.customer_name"></td>
                                <td class="p-4 font-bold text-gray-800" x-text="formatRupiah(order.total_amount)"></td>
                                <td class="p-4 text-xs text-gray-500" x-text="timeSince(order.created_at)"></td>
                                <td class="p-4 text-center">
                                    <div class="flex gap-2 justify-center">
                                        <button @click="processPayment(order)"
                                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg text-xs font-bold shadow-sm transition">
                                            Tunai 💵
                                        </button>
                                        <button @click="initOnlinePayment(order)"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-xs font-bold shadow-sm transition flex items-center gap-1">
                                            💳 Online
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recently Paid (To Print) -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mt-8">
            <div class="p-6 border-b flex justify-between items-center">
                <h2 class="font-bold text-lg text-gray-800">Pesanan Selesai / Dibayar (Hari Ini)</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                        <tr>
                            <th class="p-4">Order No</th>
                            <th class="p-4">Pelanggan</th>
                            <th class="p-4">Total</th>
                            <th class="p-4">Metode</th>
                            <th class="p-4">Bayar</th>
                            <th class="p-4">Kembali</th>
                            <th class="p-4 text-center">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <template x-if="paidOrders.length === 0">
                            <tr>
                                <td colspan="7" class="p-8 text-center text-gray-400 text-xs">Belum ada pesanan yang
                                    dibayar hari ini.</td>
                            </tr>
                        </template>

                        <template x-for="order in paidOrders" :key="order.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-mono font-bold" x-text="order.order_number"></td>
                                <td class="p-4" x-text="order.customer_name"></td>
                                <td class="p-4 font-bold" x-text="formatRupiah(order.total_amount)"></td>
                                <td class="p-4">
                                    <template x-if="order.payment">
                                        <div>
                                            <template x-if="order.payment.method === 'CASH'">
                                                <span
                                                    class="bg-blue-100 text-blue-700 px-2 py-1 rounded-md text-[10px] font-bold">TUNAI
                                                    💵</span>
                                            </template>
                                            <template x-if="order.payment.method !== 'CASH'">
                                                <span
                                                    class="bg-purple-100 text-purple-700 px-2 py-1 rounded-md text-[10px] font-bold uppercase"
                                                    x-text="order.payment.method.replace('DUITKU-', '') || 'ONLINE 💳'"></span>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!order.payment">
                                        <span class="text-gray-400 text-xs italic">No data</span>
                                    </template>
                                </td>
                                <td class="p-4 text-green-600 font-medium">
                                    <span x-text="getPaymentDetail(order, 'cash_received')"></span>
                                </td>
                                <td class="p-4 text-orange-600 font-medium">
                                    <span x-text="getPaymentDetail(order, 'change')"></span>
                                </td>
                                <td class="p-4 text-center">
                                    <button @click="printReceipt(order.id)"
                                        class="bg-blue-100 text-blue-700 hover:bg-blue-200 px-3 py-1 rounded-md text-xs font-bold transition">
                                        🖨️ Re-Print
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- Payment Modal -->
    <div x-show="selectedOrder" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
        style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6" @click.outside="selectedOrder = null">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-xl text-gray-800">Konfirmasi Pembayaran</h3>
                <button @click="selectedOrder = null" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl mb-6 space-y-2">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Order No</span>
                    <span class="font-mono font-bold" x-text="selectedOrder?.order_number"></span>
                </div>
                <div class="flex justify-between text-lg font-bold border-t pt-2 mt-2">
                    <span>Total Tagihan</span>
                    <span class="text-orange-600"
                        x-text="selectedOrder ? formatRupiah(selectedOrder.total_amount) : 0"></span>
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Jumlah Bayar (Tunai)</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-gray-400">Rp</span>
                    <input type="number" x-model.number="receivedAmount"
                        class="w-full pl-12 pr-4 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-orange-500 focus:bg-white transition outline-none text-xl font-bold"
                        placeholder="0"
                        @keyup.enter="if(receivedAmount >= selectedOrder.total_amount) confirmPay('CASH')">
                </div>

                <template x-if="receivedAmount > 0">
                    <div class="mt-4 p-4 rounded-xl flex justify-between items-center transition-all shadow-inner"
                        :class="receivedAmount >= (selectedOrder?.total_amount || 0) ? 'bg-green-50 border border-green-100' : 'bg-red-50 border border-red-100'">
                        <span class="text-sm font-medium"
                            :class="receivedAmount >= (selectedOrder?.total_amount || 0) ? 'text-green-600' : 'text-red-600'">
                            <span
                                x-text="receivedAmount >= (selectedOrder?.total_amount || 0) ? 'Kembalian' : 'Kurang'"></span>
                        </span>
                        <span class="text-xl font-black"
                            :class="receivedAmount >= (selectedOrder?.total_amount || 0) ? 'text-green-700' : 'text-red-700'"
                            x-text="formatRupiah(Math.abs(receivedAmount - (selectedOrder?.total_amount || 0)))"></span>
                    </div>
                </template>
            </div>

            <div class="space-y-3">
                <button @click="confirmPay('CASH')"
                    :disabled="isProcessing || !receivedAmount || receivedAmount < (selectedOrder?.total_amount || 0)"
                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-300 text-white font-bold py-4 rounded-2xl shadow transition-all flex justify-center items-center gap-2">
                    <span x-text="isProcessing ? 'Memproses...' : 'Konfirmasi & Print Nota 💵'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Online Payment Modal -->
    <div x-show="showOnlineModal" x-cloak
        class="fixed inset-0 bg-black/60 backdrop-blur-sm z-[60] flex items-center justify-center p-4"
        style="display: none;">
        <div class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden"
            @click.outside="showOnlineModal = false">
            <div class="bg-blue-600 p-6 text-white flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-xl">Bayar Online</h3>
                    <p class="text-blue-100 text-xs mt-1" x-text="'Order #' + selectedOrder?.order_number"></p>
                </div>
                <button @click="showOnlineModal = false"
                    class="bg-white/20 hover:bg-white/30 rounded-full p-2 transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <!-- Total Display -->
                <div class="bg-gray-100 rounded-2xl p-4 flex justify-between items-center mb-6">
                    <span class="text-sm text-gray-500 font-medium">Total Tagihan</span>
                    <span class="text-xl font-black text-gray-800"
                        x-text="selectedOrder ? formatRupiah(selectedOrder.total_amount) : ''"></span>
                </div>

                <div class="space-y-4 max-h-[400px] overflow-y-auto px-1">
                    <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest">Pilih Metode</h4>

                    <template x-if="onlineMethods.length === 0 && !isGeneratingOnline">
                        <div class="text-center py-8">
                            <div class="animate-pulse flex flex-col items-center gap-2 text-gray-400">
                                <svg class="h-8 w-8 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="text-xs">Memuat metode...</span>
                            </div>
                        </div>
                    </template>

                    <div class="grid grid-cols-1 gap-2">
                        <template x-for="m in onlineMethods" :key="m.paymentMethod">
                            <button @click="triggerOnlinePayment(m.paymentMethod)" :disabled="isGeneratingOnline"
                                class="flex items-center gap-4 p-3 rounded-2xl border border-gray-100 hover:border-blue-500 hover:bg-blue-50 transition-all group text-left w-full">
                                <div
                                    class="w-12 h-12 bg-white rounded-xl shadow-sm p-2 flex items-center justify-center border group-hover:border-blue-200">
                                    <img :src="m.paymentImage" class="max-w-full max-h-full object-contain">
                                </div>
                                <div class="flex-1">
                                    <p class="font-bold text-gray-700 text-sm" x-text="m.paymentName"></p>
                                    <p class="text-[10px] text-gray-400">Biaya: <span
                                            x-text="formatRupiah(m.totalFee)"></span></p>
                                </div>
                            </button>
                        </template>
                    </div>

                    <div class="mt-4">
                        <button
                            @click="if(confirm('Konfirmasi sebagai bayar online manual (sudah cek mutasi/rekening)?')) confirmPay('ONLINE_MANUAL')"
                            class="w-full flex items-center justify-center gap-2 p-3 rounded-2xl border-2 border-dashed border-gray-200 text-gray-500 hover:border-gray-400 hover:bg-gray-50 transition-all text-sm font-bold">
                            📝 Konfirmasi Manual (Transfer/Lainnya)
                        </button>
                    </div>
                </div>

                <!-- Sync Button (Backup) -->
                <div class="mt-6 pt-4 border-t text-center" x-show="selectedOrder">
                    <button @click="syncDuitku(selectedOrder.order_number)"
                        class="text-xs text-blue-600 font-bold hover:underline">
                        🔄 Cek Status Manual (Jika sudah bayar)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cashierApp() {
            return {
                token: localStorage.getItem('cashier_token'),
                currentUser: JSON.parse(localStorage.getItem('cashier_user') || '{}'),
                orders: [],
                selectedOrder: null,
                receivedAmount: 0,
                isProcessing: false,
                onlineMethods: [],
                showOnlineModal: false,
                isGeneratingOnline: false,

                // Helper: Get API Base URL dynamically
                get apiBase() {
                    const basePath = window.location.pathname.split('/cashier/')[0];
                    return basePath + '/api/v1';
                },

                // Helper: Get Route Base URL dynamically
                get routeBase() {
                    return window.location.pathname.split('/cashier/')[0];
                },

                async init() {
                    if (!this.token) {
                        window.location.href = this.routeBase + '/cashier/login';
                        return;
                    }
                    await this.fetchOrders();
                    // Polling
                    setInterval(() => this.fetchOrders(), 5000);
                },

                get unpaidOrders() {
                    return this.orders.filter(o => o.payment_status === 'UNPAID' && o.status !== 'COMPLETED');
                },

                get paidOrders() {
                    return this.orders.filter(o => o.payment_status === 'PAID').sort((a, b) => b.id - a.id);
                },

                printReceipt(id) {
                    const printUrl = `${this.routeBase}/cashier/order/${id}/print`;
                    window.open(printUrl, '_blank');
                },

                async fetchOrders() {
                    try {
                        let res = await fetch(`${this.apiBase}/cashier/orders`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        });
                        if (res.status === 401) this.logout();
                        this.orders = await res.json();
                    } catch (e) { console.error('Error fetching'); }
                },

                processPayment(order) {
                    this.selectedOrder = order;
                    this.receivedAmount = 0;
                },

                async confirmPay(method) {
                    this.isProcessing = true;
                    try {
                        let res = await fetch(`${this.apiBase}/cashier/payments/confirm`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                order_id: this.selectedOrder.id,
                                amount: this.selectedOrder.total_amount,
                                received_amount: method === 'CASH' ? this.receivedAmount : this.selectedOrder.total_amount,
                                payment_method: method
                            })
                        });

                        if (res.ok) {
                            alert('Pembayaran Berhasil!');
                            this.showOnlineModal = false;

                            // Buka nota di tab baru
                            const printUrl = `${this.routeBase}/cashier/order/${this.selectedOrder.id}/print`;
                            window.open(printUrl, '_blank');

                            this.selectedOrder = null;
                            this.fetchOrders();
                        } else {
                            let d = await res.json();
                            alert('Gagal: ' + d.message);
                        }
                    } catch (e) {
                        alert('Error system');
                    } finally {
                        this.isProcessing = false;
                    }
                },

                async syncDuitku(orderNumber) {
                    try {
                        let res = await fetch(`${this.apiBase}/payment/duitku/check-status`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ order_number: orderNumber })
                        });
                        let data = await res.json();
                        if (data.success) {
                            alert('Status: ' + data.message);
                            this.fetchOrders();
                        } else {
                            alert('Gagal cek Duitku: ' + (data.message || 'Error'));
                        }
                    } catch (e) {
                        alert('Gagal menghubungkan ke server.');
                    }
                },

                async initOnlinePayment(order) {
                    this.selectedOrder = order;
                    this.showOnlineModal = true;
                    this.onlineMethods = [];
                    try {
                        let res = await fetch(`${this.apiBase}/payment/duitku/methods?amount=${order.total_amount}`);
                        this.onlineMethods = await res.json();
                    } catch (e) {
                        console.error('Failed to fetch methods');
                    }
                },

                async triggerOnlinePayment(methodCode) {
                    this.isGeneratingOnline = true;
                    try {
                        let res = await fetch(`${this.apiBase}/payment/duitku/create`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                order_id: this.selectedOrder.id,
                                payment_method: methodCode
                            })
                        });
                        let result = await res.json();
                        if (res.ok && result.data.payment_url) {
                            window.open(result.data.payment_url, '_blank');
                            this.showOnlineModal = false;
                        } else {
                            alert('Gagal: ' + (result.message || 'Error'));
                        }
                    } catch (e) {
                        alert('System error');
                    } finally {
                        this.isGeneratingOnline = false;
                    }
                },

                logout() {
                    localStorage.removeItem('cashier_token');
                    window.location.href = this.routeBase + '/cashier/login';
                },

                formatRupiah(num) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
                },

                getPaymentDetail(order, key) {
                    if (!order.payment || order.payment.method !== 'CASH' || !order.payment.payment_details) return '-';
                    try {
                        const details = typeof order.payment.payment_details === 'string'
                            ? JSON.parse(order.payment.payment_details)
                            : order.payment.payment_details;
                        return this.formatRupiah(details[key] || 0);
                    } catch (e) {
                        return '-';
                    }
                },

                timeSince(date) {
                    // Simple logic
                    return new Date(date).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                }
            }
        }
    </script>
</body>

</html>