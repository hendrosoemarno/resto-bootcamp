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

<body class="bg-gray-900 text-white h-screen flex flex-col" x-data="kitchenApp()">

    <!-- Navbar -->
    <header class="bg-gray-800 border-b border-gray-700 px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold flex items-center gap-2">
            👨‍🍳 Kitchen Dashboard <span class="bg-orange-600 text-xs px-2 py-0.5 rounded">LIVE</span>
        </h1>
        <div class="flex items-center gap-4">
            <span class="text-gray-400 text-sm" x-text="currentUser.name"></span>
            <button @click="logout" class="text-red-400 hover:text-red-300 text-sm font-semibold">Logout</button>
        </div>
    </header>

    <!-- Kanban Board -->
    <main class="flex-1 overflow-x-auto p-6 flex gap-6">

        <!-- Column: Antrian (QUEUED) -->
        <div class="w-1/3 min-w-[320px] flex flex-col bg-gray-800/50 rounded-xl border border-gray-700">
            <div class="p-4 border-b border-gray-700 bg-gray-800 rounded-t-xl sticky top-0 z-10 flex justify-between">
                <h2 class="font-bold text-gray-300">⏳ ANTRIAN BARU</h2>
                <span class="bg-blue-900 text-blue-200 px-2 py-0.5 rounded text-xs font-bold"
                    x-text="queuedOrders.length">0</span>
            </div>

            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
                <template x-if="queuedOrders.length === 0">
                    <div class="text-center text-gray-500 py-10">Belum ada pesanan masuk.</div>
                </template>

                <template x-for="order in queuedOrders" :key="order.id">
                    <div class="bg-gray-800 border border-gray-600 p-4 rounded-lg shadow-lg relative group">
                        <div class="absolute top-4 right-4 text-xs font-mono text-gray-500"
                            x-text="timeSince(order.updated_at)"></div>

                        <div class="mb-3">
                            <span class="bg-blue-900 text-blue-200 text-xs font-bold px-2 py-1 rounded me-2"
                                x-text="order.table?.table_number || 'Takeaway'"></span>
                            <span class="font-bold text-lg text-white">#<span
                                    x-text="order.order_number.split('-').pop()"></span></span>
                        </div>

                        <!-- Items -->
                        <ul class="space-y-2 mb-4">
                            <template x-for="item in order.items" :key="item.id">
                                <li class="flex justify-between items-start text-sm">
                                    <div class="flex-1">
                                        <span class="font-bold text-gray-200"
                                            x-text="item.quantity + 'x ' + item.menu.name"></span>
                                        <div x-show="item.note" class="text-orange-400 text-xs italic mt-0.5">Note:
                                            <span x-text="item.note"></span>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>

                        <button @click="updateStatus(order.id, 'start')"
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-bold py-2 rounded transition flex items-center justify-center gap-2">
                            <span>🔥 Mulai Masak</span>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Dimasak (COOKING) -->
        <div class="w-1/3 min-w-[320px] flex flex-col bg-gray-800/50 rounded-xl border border-gray-700">
            <div class="p-4 border-b border-gray-700 bg-gray-800 rounded-t-xl sticky top-0 z-10 flex justify-between">
                <h2 class="font-bold text-orange-400">🔥 SEDANG DIMASAK</h2>
                <span class="bg-orange-900 text-orange-200 px-2 py-0.5 rounded text-xs font-bold"
                    x-text="cookingOrders.length">0</span>
            </div>

            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
                <template x-if="cookingOrders.length === 0">
                    <div class="text-center text-gray-500 py-10">Tidak ada yang dimasak.</div>
                </template>

                <template x-for="order in cookingOrders" :key="order.id">
                    <div
                        class="bg-gray-800 border-l-4 border-orange-500 border-y border-r border-gray-700 p-4 rounded-r-lg shadow-lg relative">
                        <div class="absolute top-4 right-4 text-xs font-mono text-gray-500"
                            x-text="timeSince(order.updated_at)"></div>

                        <div class="mb-3">
                            <span class="bg-orange-900 text-orange-200 text-xs font-bold px-2 py-1 rounded me-2"
                                x-text="order.table?.table_number"></span>
                            <span class="font-bold text-lg text-white">#<span
                                    x-text="order.order_number.split('-').pop()"></span></span>
                        </div>

                        <ul class="space-y-1 mb-4 text-gray-300">
                            <template x-for="item in order.items" :key="item.id">
                                <li class="text-sm">
                                    <span x-text="item.quantity"></span>x <span x-text="item.menu.name"></span>
                                    <span x-show="item.note" class="text-orange-400 text-xs ml-1">(Note)</span>
                                </li>
                            </template>
                        </ul>

                        <button @click="updateStatus(order.id, 'ready')"
                            class="w-full bg-green-600 hover:bg-green-500 text-white font-bold py-2 rounded transition">
                            ✅ Selesai (Siap Saji)
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <!-- Column: Siap Saji (READY) -->
        <div class="w-1/3 min-w-[320px] flex flex-col bg-green-900/20 rounded-xl border border-green-800">
            <div
                class="p-4 border-b border-green-800 bg-green-900/50 rounded-t-xl sticky top-0 z-10 flex justify-between">
                <h2 class="font-bold text-green-400">🔔 SIAP DIANTAR</h2>
                <span class="bg-green-800 text-green-200 px-2 py-0.5 rounded text-xs font-bold"
                    x-text="readyOrders.length">0</span>
            </div>

            <div class="p-4 space-y-4 overflow-y-auto flex-1 scrollbar-hide">
                <template x-if="readyOrders.length === 0">
                    <div class="text-center text-gray-500 py-10">Antrian saji kosong.</div>
                </template>

                <template x-for="order in readyOrders" :key="order.id">
                    <div
                        class="bg-gray-800 border-l-4 border-green-500 p-4 rounded-r-lg shadow-lg relative opacity-90 hover:opacity-100 transition">
                        <div class="absolute top-4 right-4 text-xs font-mono text-gray-500"
                            x-text="timeSince(order.updated_at)"></div>

                        <div class="mb-3">
                            <span class="bg-green-900 text-green-200 text-xs font-bold px-2 py-1 rounded me-2"
                                x-text="order.table?.table_number"></span>
                            <span class="font-bold text-lg text-white">#<span
                                    x-text="order.order_number.split('-').pop()"></span></span>
                            <div class="text-sm text-gray-400 mt-1" x-text="order.customer_name"></div>
                        </div>

                        <div class="text-xs text-gray-500 mb-4 italic">
                            <span x-text="order.items.length"></span> items siap.
                        </div>

                        <button @click="updateStatus(order.id, 'complete')"
                            class="w-full bg-gray-700 hover:bg-gray-600 text-gray-200 font-bold py-2 rounded transition border border-gray-600">
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

                async init() {
                    if (!this.token) {
                        window.location.href = '{{ route('kitchen.login') }}';
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
                        let res = await fetch('{{ url('/api/v1/kitchen/orders') }}', {
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
                        let res = await fetch(`{{ url('/api/v1/kitchen/orders') }}/${id}/${action}`, {
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
                    window.location.href = '{{ route('kitchen.login') }}';
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