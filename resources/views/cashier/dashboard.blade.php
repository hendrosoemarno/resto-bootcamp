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
                <div class="text-3xl font-bold text-gray-800">-</div> <!-- Todo: Add API for stats -->
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
                                    <button @click="processPayment(order)"
                                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-xs font-bold shadow-sm transition">
                                        Bayar Tunai 💵
                                    </button>
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
                            <th class="p-4 text-center">Nota</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y text-sm">
                        <template x-if="paidOrders.length === 0">
                            <tr>
                                <td colspan="4" class="p-8 text-center text-gray-400 text-xs">Belum ada pesanan yang dibayar hari ini.</td>
                            </tr>
                        </template>

                        <template x-for="order in paidOrders" :key="order.id">
                            <tr class="hover:bg-gray-50 transition">
                                <td class="p-4 font-mono font-bold" x-text="order.order_number"></td>
                                <td class="p-4" x-text="order.customer_name"></td>
                                <td class="p-4 font-bold" x-text="formatRupiah(order.total_amount)"></td>
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
            <h3 class="font-bold text-xl mb-4 text-gray-800">Konfirmasi Pembayaran</h3>

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

            <div class="space-y-3">
                <button @click="confirmPay('CASH')" :disabled="isProcessing"
                    class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 rounded-xl shadow transition flex justify-center">
                    <span x-text="isProcessing ? 'Memproses...' : 'Terima Uang Tunai (CASH)'"></span>
                </button>
                <button @click="selectedOrder = null"
                    class="w-full bg-white border border-gray-300 text-gray-600 font-bold py-3 rounded-xl hover:bg-gray-50 transition">
                    Batal
                </button>
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
                isProcessing: false,

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
                    return this.orders.filter(o => o.payment_status === 'PAID').sort((a,b) => b.id - a.id);
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
                                amount: this.selectedOrder.total_amount
                            })
                        });

                        if (res.ok) {
                            alert('Pembayaran Berhasil!');

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

                logout() {
                    localStorage.removeItem('cashier_token');
                    window.location.href = this.routeBase + '/cashier/login';
                },

                formatRupiah(num) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
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