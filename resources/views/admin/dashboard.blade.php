<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Resto</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen" x-data="adminApp()">

    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6 font-bold text-xl border-b border-gray-800">🚀 Owner Dashboard</div>
            <nav class="flex-1 p-4 space-y-2">
                <button @click="activeTab = 'menu'"
                    :class="activeTab === 'menu' ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800'"
                    class="w-full text-left px-4 py-3 rounded transition flex items-center gap-3">
                    🍔 Manajemen Menu
                </button>
                <button @click="activeTab = 'sales'"
                    :class="activeTab === 'sales' ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800'"
                    class="w-full text-left px-4 py-3 rounded transition flex items-center gap-3">
                    📈 Laporan Penjualan
                </button>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <div class="text-xs text-gray-500 mb-2" x-text="currentUser.name"></div>
                <button @click="logout" class="text-red-400 text-sm hover:text-white">Logout</button>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto p-8">

            <!-- Tab: Menu Management -->
            <div x-show="activeTab === 'menu'">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Manajemen Menu</h2>
                    <button @click="openModal()"
                        class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-bold shadow">+
                        Tambah Menu</button>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                            <tr>
                                <th class="p-4">Nama Menu</th>
                                <th class="p-4">Kategori</th>
                                <th class="p-4">Harga</th>
                                <th class="p-4 text-center">Status</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="menu in menus" :key="menu.id">
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4 font-bold" x-text="menu.name"></td>
                                    <td class="p-4"><span class="bg-gray-100 px-2 py-1 rounded text-xs"
                                            x-text="menu.category"></span></td>
                                    <td class="p-4" x-text="formatRupiah(menu.price)"></td>
                                    <td class="p-4 text-center">
                                        <button @click="toggleAvailability(menu)"
                                            :class="menu.is_available ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                            class="px-3 py-1 rounded-full text-xs font-bold"
                                            x-text="menu.is_available ? 'Tersedia' : 'Habis'"></button>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <button @click="openModal(menu)"
                                            class="text-blue-600 hover:underline text-xs font-bold">Edit</button>
                                        <button @click="deleteMenu(menu.id)"
                                            class="text-red-500 hover:underline text-xs">Hapus</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Sales Report -->
            <div x-show="activeTab === 'sales'"
                x-init="$watch('activeTab', value => { if (value === 'sales') fetchReports(); })">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Laporan Keuangan</h2>
                    <button @click="fetchReports()"
                        class="text-blue-600 hover:underline text-sm font-bold flex items-center gap-1">
                        🔄 Refresh Data
                    </button>
                </div>

                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <!-- Today -->
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="text-green-100 text-sm font-bold uppercase tracking-wider mb-1">Pendapatan Hari Ini
                        </div>
                        <div class="text-3xl font-bold" x-text="formatRupiah(report.summary.today || 0)"></div>
                        <div class="text-xs text-green-200 mt-2">Update: Realtime</div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

                    <!-- Recent Transactions Table -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 h-fit">
                        <div class="p-4 border-b bg-gray-50">
                            <h3 class="font-bold text-gray-700">📜 Riwayat Transaksi (Terbaru)</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                                    <tr>
                                        <th class="p-4">Waktu</th>
                                        <th class="p-4">Order</th>
                                        <th class="p-4 text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <template x-for="txn in report.transactions" :key="txn.id">
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-4 text-xs text-gray-500" x-text="formatDate(txn.created_at)">
                                            </td>
                                            <td class="p-4 font-mono font-bold text-blue-600 text-sm">
                                                <div x-text="txn.order_number"></div>
                                                <div class="text-xs text-gray-400 font-normal"
                                                    x-text="txn.customer_name"></div>
                                            </td>
                                            <td class="p-4 text-right font-bold text-gray-800 text-sm"
                                                x-text="formatRupiah(txn.total_amount)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Product Sales Table -->
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 h-fit">
                        <div class="p-4 border-b bg-gray-50">
                            <h3 class="font-bold text-gray-700">🏆 Top Produk (Bulan Ini)</h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                                    <tr>
                                        <th class="p-4">Menu</th>
                                        <th class="p-4 text-center">Terjual</th>
                                        <th class="p-4 text-right">Omset</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <template x-for="(prod, index) in report.product_sales" :key="index">
                                        <tr class="hover:bg-gray-50">
                                            <td class="p-4">
                                                <div class="font-bold text-gray-800 text-sm" x-text="prod.menu_name">
                                                </div>
                                                <div class="text-xs text-gray-400" x-text="prod.category"></div>
                                            </td>
                                            <td class="p-4 text-center">
                                                <span
                                                    class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded-full"
                                                    x-text="prod.total_qty + ' items'"></span>
                                            </td>
                                            <td class="p-4 text-right font-bold text-gray-800 text-sm"
                                                x-text="formatRupiah(prod.total_revenue)"></td>
                                        </tr>
                                    </template>
                                    <template x-if="!report.product_sales || report.product_sales.length === 0">
                                        <tr>
                                            <td colspan="3" class="p-8 text-center text-gray-400">Belum ada data
                                                penjualan produk.</td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- Modal Form Menu -->
    <div x-show="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative" @click.outside="showModal = false">
            <h3 class="font-bold text-xl mb-4" x-text="form.id ? 'Edit Menu' : 'Tambah Menu Baru'"></h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Menu</label>
                    <input type="text" x-model="form.name"
                        class="w-full border rounded p-2 focus:ring-2 ring-orange-500 outline-none">
                </div>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Harga</label>
                        <input type="number" x-model="form.price" class="w-full border rounded p-2 outline-none">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                        <select x-model="form.category" class="w-full border rounded p-2 outline-none bg-white">
                            <option value="food">Makanan</option>
                            <option value="drink">Minuman</option>
                            <option value="snack">Cemilan</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">URL Gambar (Opsional)</label>
                    <input type="text" x-model="form.image_url" class="w-full border rounded p-2 text-xs outline-none">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button @click="showModal = false"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button @click="saveMenu"
                    class="px-4 py-2 bg-orange-600 text-white font-bold rounded hover:bg-orange-700">Simpan</button>
            </div>
        </div>
    </div>

    <script>
        function adminApp() {
            return {
                token: localStorage.getItem('admin_token'),
                currentUser: JSON.parse(localStorage.getItem('admin_user') || '{}'),
                activeTab: 'menu',
                menus: [],
                report: { summary: {}, transactions: [] },
                showModal: false,
                form: { id: null, name: '', price: 0, category: 'food', image_url: '' },

                async init() {
                    if (!this.token) {
                        window.location.href = '{{ route('admin.login') }}';
                        return;
                    }
                    await this.fetchMenus();
                },

                async fetchMenus() {
                    // Reuse customer endpoint for reading, but we need restaurant ID
                    // Better to use dedicated admin endpoint if available, but let's reuse READ
                    try {
                        let res = await fetch(`{{ url('/api/v1/restaurants') }}/${this.currentUser.restaurant_id}/menu`);
                        let data = await res.json();
                        this.menus = data.menus;
                    } catch (e) {
                        alert('Gagal memuat menu');
                    }
                },

                async fetchReports() {
                    try {
                        let res = await fetch('/api/v1/admin/reports', {
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        });
                        let data = await res.json();
                        this.report = data;
                    } catch (e) { console.error('Report fetch err'); }
                },

                openModal(menu = null) {
                    if (menu) {
                        this.form = { ...menu };
                    } else {
                        this.form = { id: null, name: '', price: 0, category: 'food', image_url: '' };
                    }
                    this.showModal = true;
                },

                async saveMenu() {
                    const isEdit = !!this.form.id;
                    const url = isEdit ? `/api/v1/admin/menus/${this.form.id}` : '/api/v1/admin/menus';

                    try {
                        let res = await fetch(url, {
                            method: isEdit ? 'PUT' : 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.form)
                        });

                        if (res.ok) {
                            this.showModal = false;
                            this.fetchMenus();
                        } else {
                            let d = await res.json();
                            alert('Gagal: ' + d.message);
                        }
                    } catch (e) { alert('Error system'); }
                },

                async deleteMenu(id) {
                    if (!confirm('Hapus menu ini?')) return;
                    try {
                        let res = await fetch(`/api/v1/admin/menus/${id}`, {
                            method: 'DELETE',
                            headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                        });
                        if (res.ok) this.fetchMenus();
                    } catch (e) { }
                },

                async toggleAvailability(menu) {
                    try {
                        let res = await fetch(`/api/v1/admin/menus/${menu.id}`, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Authorization': `Bearer ${this.token}`,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ is_available: !menu.is_available })
                        });
                        if (res.ok) this.fetchMenus();
                    } catch (e) { }
                },

                formatRupiah(num) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num);
                },

                formatDate(dateStr) {
                    return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
                },

                logout() {
                    localStorage.removeItem('admin_token');
                    window.location.href = '{{ route('admin.login') }}';
                }
            }
        }
    </script>
</body>

</html>