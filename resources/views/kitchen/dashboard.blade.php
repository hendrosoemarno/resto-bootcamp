<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="bg-white text-gray-800 h-screen flex flex-col" x-data="kitchenApp()">

    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shadow-sm">
        <h1 class="text-xl font-bold flex items-center gap-2 text-gray-900">
            👨‍🍳 Kitchen Dashboard <span
                class="bg-orange-600 text-white text-[10px] px-2 py-0.5 rounded shadow-sm uppercase tracking-wider">LIVE</span>
        </h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-500 text-sm font-medium" x-text="currentUser.name"></span>
            <button @click="logout" class="text-red-500 hover:text-red-600 text-sm font-bold transition">Logout</button>
        </div>
    </header>

    <!-- Kanban Board -->
    <main class="flex-1 overflow-x-auto p-6 flex gap-6 bg-gray-50/50">

        <!-- Column: Antrian (QUEUED) -->
        <div class="w-1/3 min-w-[320px] flex flex-col bg-gray-100 rounded-2xl border border-gray-200 shadow-inner">
            <div
                class="p-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl sticky top-0 z-10 flex justify-between items-center">
                <h2 class="font-black text-gray-500 flex items-center gap-2 text-sm uppercase tracking-widest">⏳ Antrian
                    Baru</h2>
                <span
                    class="bg-white text-blue-600 border border-blue-100 px-2.5 py-1 rounded-full text-xs font-black shadow-sm"
                    x-text="queuedOrders.length">0</span>
            </div>

            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
                <template x-if="queuedOrders.length === 0">
                    <div class="text-center text-gray-400 py-10 italic text-sm">Belum ada pesanan masuk.</div>
                </template>

                <template x-for="order in queuedOrders" :key="order.id">
                    <div
                        class="bg-white border border-gray-200 p-5 rounded-xl shadow-sm relative group hover:shadow-md transition-all duration-300">
                        <div class="absolute top-5 right-5 text-[10px] font-mono font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded"
                            x-text="timeSince(order.updated_at)"></div>

                        <div class="mb-4">
                            <span
                                class="bg-blue-600 text-white text-[10px] font-black px-2 py-1 rounded-md uppercase shadow-sm"
                                x-text="order.table?.table_number || 'Takeaway'"></span>
                            <span class="font-black text-xl text-gray-900 ml-2">#<span
                                    x-text="order.order_number.split('-').pop()"></span></span>
                        </div>

                        <!-- Items -->
                        <div class="space-y-2 mb-5">
                            <template x-for="item in order.items" :key="item.id">
                                <div
                                    class="flex justify-between items-start text-sm border-b border-gray-50 pb-2 last:border-0 last:pb-0">
                                    <div class="flex-1">
                                        <span class="font-bold text-gray-800"
                                            x-text="item.quantity + 'x ' + item.menu.name"></span>
                                        <div x-show="item.note"
                                            class="text-orange-600 text-[10px] font-bold italic mt-0.5 flex items-center gap-1">
                                            <span class="inline-block w-1 h-1 bg-orange-600 rounded-full"></span>
                                            <span x-text="item.note"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <button @click="updateStatus(order.id, 'start')"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-3 rounded-xl shadow-lg shadow-blue-100 transition-all flex items-center justify-center gap-2 group-hover:scale-[1.02]">
                            <span>🔥 Mulai Masak</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Dimasak (COOKING) -->
        <div class="w-1/3 min-w-[320px] flex flex-col bg-orange-50 rounded-2xl border border-orange-100 shadow-inner">
            <div
                class="p-4 border-b border-orange-100 bg-orange-50/80 rounded-t-2xl sticky top-0 z-10 flex justify-between items-center">
                <h2 class="font-black text-orange-600 flex items-center gap-2 text-sm uppercase tracking-widest">🔥
                    Sedang Dimasak</h2>
                <span
                    class="bg-white text-orange-600 border border-orange-200 px-2.5 py-1 rounded-full text-xs font-black shadow-sm"
                    x-text="cookingOrders.length">0</span>
            </div>

            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
                <template x-if="cookingOrders.length === 0">
                    <div class="text-center text-orange-300 py-10 italic text-sm">Tidak ada yang dimasak.</div>
                </template>

                <template x-for="order in cookingOrders" :key="order.id">
                    <div
                        class="bg-white border-l-8 border-orange-500 border-y border-r border-orange-100 p-5 rounded-r-xl shadow-sm relative hover:shadow-md transition-all">
                        <div class="absolute top-5 right-5 text-[10px] font-mono font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded"
                            x-text="timeSince(order.updated_at)"></div>

                        <div class="mb-4 text-gray-900">
                            <span class="bg-orange-500 text-white text-[10px] font-black px-2 py-1 rounded-md uppercase"
                                x-text="order.table?.table_number"></span>
                            <span class="font-black text-xl ml-2">#<span
                                    x-text="order.order_number.split('-').pop()"></span></span>
                        </div>

                        <div class="space-y-1 mb-5 text-gray-700">
                            <template x-for="item in order.items" :key="item.id">
                                <div class="text-sm font-medium border-b border-gray-50 pb-1 last:border-0 last:pb-0">
                                    <span class="font-black text-gray-900" x-text="item.quantity"></span>x <span
                                        x-text="item.menu.name"></span>
                                    <span x-show="item.note"
                                        class="text-orange-500 text-[10px] font-bold ml-1 italic">(Note)</span>
                                </div>
                            </template>
                        </div>

                        <button @click="updateStatus(order.id, 'ready')"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-black py-3 rounded-xl shadow-lg shadow-green-100 transition-all flex items-center justify-center gap-2">
                            ✅ Selesai (Siap Saji)
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Siap Saji (READY) -->
        <div class="w-1/3 min-w-[320px] flex flex-col bg-green-50 rounded-2xl border border-green-100 shadow-inner">
            <div
                class="p-4 border-b border-green-200 bg-green-50/80 rounded-t-2xl sticky top-0 z-10 flex justify-between items-center">
                <h2 class="font-black text-green-600 flex items-center gap-2 text-sm uppercase tracking-widest">🔔 Siap
                    Diantar</h2>
                <span
                    class="bg-white text-green-700 border border-green-200 px-2.5 py-1 rounded-full text-xs font-black shadow-sm"
                    x-text="readyOrders.length">0</span>
            </div>

            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
                <template x-if="readyOrders.length === 0">
                    <div class="text-center text-green-300 py-10 italic text-sm">Antrian saji kosong.</div>
                </template>

                <template x-for="order in readyOrders" :key="order.id">
                    <div
                        class="bg-white border-l-8 border-green-500 p-5 rounded-r-xl shadow-sm relative opacity-90 hover:opacity-100 transition-all hover:shadow-md">
                        <div class="absolute top-5 right-5 text-[10px] font-mono font-bold text-gray-400 bg-gray-50 px-2 py-0.5 rounded"
                            x-text="timeSince(order.updated_at)"></div>

                        <div class="mb-4">
                            <span class="bg-green-600 text-white text-[10px] font-black px-2 py-1 rounded-md uppercase"
                                x-text="order.table?.table_number"></span>
                            <span class="font-black text-xl text-gray-900 ml-2">#<span
                                    x-text="order.order_number.split('-').pop()"></span></span>
                            <div class="text-xs font-bold text-gray-400 mt-1 uppercase" x-text="order.customer_name">
                            </div>
                        </div>

                        <div
                            class="text-[10px] text-gray-400 mb-5 font-bold uppercase tracking-wider flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span x-text="order.items.length"></span> items siap.
                        </div>

                        <button @click="updateStatus(order.id, 'complete')"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-600 font-black py-3 rounded-xl transition-all border border-gray-200">
                            👋 Sudah Diambil
                        </button>
                    </div>
                </template>
            </div>
        </div>

    </main>

    <script>
        function kitchenApp() {
            return {
                orders: [],
                token: localStorage.getItem('kitchen_token'),
                currentUser: JSON.parse(localStorage.getItem('kitchen_user') || '{}'),

                // Helper: Get API Base URL dynamically
                get apiBase() {
                    const basePath = window.location.pathname.split('/kitchen/')[0];
                    return basePath + '/api/v1';
                },

                // Helper: Get Route Base URL dynamically
                get routeBase() {
                    return window.location.pathname.split('/kitchen/')[0];
                },

                async init() {
                    if (!this.token) {
                        window.location.href = this.routeBase + '/kitchen/login';
                        return;
                    }

                    await this.fetchOrders();

                    // Polling every 10s as backup
                    setInterval(() => this.fetchOrders(), 10000);

                    // TODO: Pusher Listen
                    // channel.bind('order.status.updated', () => this.fetchOrders());
                },

                get queuedOrders() { return this.orders.filter(o => o.status === 'QUEUED'); },
                get cookingOrders() { return this.orders.filter(o => o.status === 'COOKING'); },
                get readyOrders() { return this.orders.filter(o => o.status === 'READY'); },

                async fetchOrders() {
                    try {
                        let res = await fetch(`${this.apiBase}/kitchen/orders`, {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        });

                        if (res.status === 401) this.logout();

                        let data = await res.json();
                        this.orders = data;
                    } catch (e) { console.error('Fetch error'); }
                },

                async updateStatus(id, action) {
                    // action: 'start' or 'ready'
                    try {
                        let res = await fetch(`${this.apiBase}/kitchen/orders/${id}/${action}`, {
                            method: 'PUT',
                            headers: {
                                'Authorization': `Bearer ${this.token}`,
                                'Accept': 'application/json'
                            }
                        });

                        if (res.ok) {
                            await this.fetchOrders();
                        } else {
                            alert('Gagal update status');
                        }
                    } catch (e) { alert('Error network'); }
                },

                logout() {
                    localStorage.removeItem('kitchen_token');
                    window.location.href = this.routeBase + '/kitchen/login';
                },

                timeSince(dateString) {
                    // Simple helper to show "5m ago"
                    const date = new Date(dateString);
                    const seconds = Math.floor((new Date() - date) / 1000);
                    let interval = seconds / 31536000;
                    if (interval > 1) return Math.floor(interval) + "y ago";
                    interval = seconds / 3600;
                    if (interval > 1) return Math.floor(interval) + "h ago";
                    interval = seconds / 60;
                    if (interval > 1) return Math.floor(interval) + "m ago";
                    return Math.floor(seconds) + "s ago";
                }
            }
        }
    </script>
</body>

</html>