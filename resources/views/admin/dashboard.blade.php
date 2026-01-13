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
                <button @click="activeTab = 'category'"
                    :class="activeTab === 'category' ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800'"
                    class="w-full text-left px-4 py-3 rounded transition flex items-center gap-3">
                    📂 Manajemen Kategori
                </button>
                <button @click="activeTab = 'users'"
                    :class="activeTab === 'users' ? 'bg-orange-600 text-white' : 'text-gray-400 hover:bg-gray-800'"
                    class="w-full text-left px-4 py-3 rounded transition flex items-center gap-3">
                    👥 Manajemen User
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
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">Manajemen Menu</h2>

                    <div class="flex flex-1 w-full justify-end gap-3">
                        <!-- Search Input -->
                        <div class="relative">
                            <input type="text" x-model="searchQuery" placeholder="Cari menu..."
                                class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 ring-orange-100 w-full md:w-64">
                            <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
                        </div>

                        <button @click="openModal()"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2">
                            <span>+</span> <span class="hidden sm:inline">Tambah</span>
                        </button>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase cursor-pointer select-none">
                            <tr>
                                <th class="p-4 hover:bg-gray-100 transition" @click="sort('name')">
                                    Nama Menu <span x-show="sortBy === 'name'"
                                        x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                                </th>
                                <th class="p-4 hover:bg-gray-100 transition" @click="sort('category')">
                                    Kategori <span x-show="sortBy === 'category'"
                                        x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                                </th>
                                <th class="p-4 hover:bg-gray-100 transition" @click="sort('price')">
                                    Harga <span x-show="sortBy === 'price'"
                                        x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                                </th>
                                <th class="p-4 text-center hover:bg-gray-100 transition" @click="sort('is_available')">
                                    Status <span x-show="sortBy === 'is_available'"
                                        x-text="sortDir === 'asc' ? '↑' : '↓'"></span>
                                </th>
                                <th class="p-4 text-right cursor-default">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="menu in filteredMenus" :key="menu.id">
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="p-4 flex items-center gap-3">
                                        <img :src="menu.image_url || 'https://via.placeholder.com/50'"
                                            class="w-10 h-10 rounded object-cover border bg-gray-100">
                                        <span class="font-bold text-gray-800" x-text="menu.name"></span>
                                    </td>
                                    <td class="p-4">
                                        <span class="bg-gray-100 px-2 py-1 rounded text-xs font-semibold text-gray-600"
                                            x-text="menu.category"></span>
                                    </td>
                                    <td class="p-4 font-mono text-gray-600" x-text="formatRupiah(menu.price)"></td>
                                    <td class="p-4 text-center">
                                        <!-- Toggle Switch UI -->
                                        <button @click="toggleAvailability(menu)"
                                            class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none"
                                            :class="menu.is_available ? 'bg-green-500' : 'bg-gray-300'">
                                            <span class="sr-only">Toggle Status</span>
                                            <span
                                                class="transform transition ease-in-out duration-200 inline-block w-4 h-4 bg-white rounded-full shadow"
                                                :class="menu.is_available ? 'translate-x-6' : 'translate-x-1'"></span>
                                        </button>
                                        <div class="text-[10px] uppercase font-bold mt-1"
                                            :class="menu.is_available ? 'text-green-600' : 'text-gray-400'"
                                            x-text="menu.is_available ? 'Tersedia' : 'Habis'"></div>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <button @click="openModal(menu)"
                                            class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-2 py-1 rounded text-xs font-bold transition">Edit</button>
                                        <button @click="deleteMenu(menu.id)"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded text-xs transition">Hapus</button>
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredMenus.length === 0">
                                <td colspan="5" class="p-8 text-center text-gray-400">
                                    Tidak ada menu yang cocok dengan pencarian.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab: Category Management -->
            <div x-show="activeTab === 'category'"
                x-init="$watch('activeTab', v => { if(v==='category') fetchCategories() })">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Manajemen Kategori</h2>
                    <div class="flex gap-2">
                        <input type="text" x-model="newCategoryName" placeholder="Nama Kategori Baru..."
                            class="border rounded p-2 text-sm outline-none focus:border-orange-500">
                        <button @click="createCategory()"
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded text-sm font-bold">Simpan</button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <template x-for="cat in categories" :key="cat.id">
                        <div
                            class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex justify-between items-center group">
                            <input type="text" x-model="cat.name" @change="updateCategory(cat)"
                                class="font-bold text-gray-700 bg-transparent border-b border-transparent focus:border-orange-500 focus:outline-none w-full mr-2">

                            <button @click="deleteCategory(cat.id)"
                                class="text-gray-300 hover:text-red-500 transition opacity-0 group-hover:opacity-100">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                    fill="currentColor">
                                    <path fill-rule="evenodd"
                                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                        clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Tab: User Management -->
            <div x-show="activeTab === 'users'" x-init="$watch('activeTab', v => { if(v==='users') fetchUsers() })">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Manajemen User (Staff)</h2>
                    <button @click="openUserModal()"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold shadow flex items-center gap-2 text-sm">
                        <span>+</span> <span>Tambah User</span>
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                    <table class="w-full text-left">
                        <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b">
                            <tr>
                                <th class="p-4">Nama</th>
                                <th class="p-4">Email</th>
                                <th class="p-4">Role</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <template x-for="user in users" :key="user.id">
                                <tr class="hover:bg-gray-50 transition border-b last:border-0 border-gray-100">
                                    <td class="p-4">
                                        <div class="font-bold text-gray-800" x-text="user.name"></div>
                                    </td>
                                    <td class="p-4 text-gray-600 text-sm" x-text="user.email"></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 rounded text-[10px] font-bold uppercase" :class="{
                                                'bg-purple-100 text-purple-700': user.role === 'admin',
                                                'bg-blue-100 text-blue-700': user.role === 'cashier',
                                                'bg-orange-100 text-orange-700': user.role === 'kitchen'
                                            }" x-text="user.role"></span>
                                    </td>
                                    <td class="p-4 text-right space-x-2">
                                        <button @click="openUserModal(user)"
                                            class="text-blue-600 hover:text-blue-800 font-bold text-sm">Edit</button>
                                        <template x-if="user.id !== currentUser.id">
                                            <button @click="deleteUser(user.id)"
                                                class="text-red-500 hover:text-red-700 font-bold text-sm">Hapus</button>
                                        </template>
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
                <!-- Report UI (Same as before) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
                        <div class="text-green-100 text-sm font-bold uppercase tracking-wider mb-1">Pendapatan Hari Ini
                        </div>
                        <div class="text-3xl font-bold" x-text="formatRupiah(report.summary.today || 0)"></div>
                        <div class="text-xs text-green-200 mt-2">Update: Realtime</div>
                    </div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 h-fit">
                        <div class="p-4 border-b bg-gray-50">
                            <h3 class="font-bold text-gray-700">📜 Riwayat Transaksi</h3>
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
                                            </td>
                                            <td class="p-4 text-right font-bold text-gray-800 text-sm"
                                                x-text="formatRupiah(txn.total_amount)"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-100 h-fit">
                        <div class="p-4 border-b bg-gray-50">
                            <h3 class="font-bold text-gray-700">🏆 Top Produk</h3>
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
                                            </td>
                                            <td class="p-4 text-center"><span
                                                    class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-1 rounded-full"
                                                    x-text="prod.total_qty"></span></td>
                                            <td class="p-4 text-right font-bold text-gray-800 text-sm"
                                                x-text="formatRupiah(prod.total_revenue)"></td>
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
                    <!-- CATEGORY DROPDOWN DYNAMIC -->
                    <div class="flex-1">
                        <label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label>
                        <select x-model="form.category" class="w-full border rounded p-2 outline-none bg-white">
                            <option value="">-- Pilih Kategori --</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.name" x-text="cat.name" :selected="form.category === cat.name">
                                </option>
                            </template>
                        </select>
                        <div x-show="categories.length === 0" class="text-xs text-red-500 mt-1">
                            Belum ada kategori. <a href="#" @click.prevent="activeTab = 'category'; showModal = false"
                                class="underline font-bold">Buat dulu disini.</a>
                        </div>
                    </div>
                </div>

                <!-- Drag Only Zone -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Gambar Menu</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:bg-gray-50 transition relative"
                        @dragover.prevent="isDragging = true" @dragleave.prevent="isDragging = false"
                        @drop.prevent="handleDrop($event)" :class="isDragging ? 'border-orange-500 bg-orange-50' : ''">
                        <input type="file" @change="handleFile($event)" accept="image/*"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <template x-if="!imagePreview">
                            <div class="space-y-2 text-gray-500 pointer-events-none">
                                <span class="text-4xl block">📷</span>
                                <div class="text-xs">Drag & drop gambar</div>
                            </div>
                        </template>
                        <template x-if="imagePreview">
                            <div class="relative">
                                <img :src="imagePreview" class="w-full h-40 object-cover rounded-lg mx-auto">
                                <button @click.prevent="removeImage"
                                    class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 text-xs shadow-md w-6 h-6 flex items-center justify-center">✕</button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button @click="showModal = false"
                    class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                <button @click="saveMenu"
                    class="px-4 py-2 bg-orange-600 text-white font-bold rounded hover:bg-orange-700">Simpan</button>
            </div>
        </div>
        <!-- Modal Form User -->
        <div x-show="showUserModal" x-cloak class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50"
            style="display: none;">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-md p-6 relative"
                @click.outside="showUserModal = false">
                <h3 class="font-bold text-xl mb-4" x-text="userForm.id ? 'Edit User Staff' : 'Tambah User Staff Baru'">
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Nama Lengkap</label>
                        <input type="text" x-model="userForm.name"
                            class="w-full border rounded p-2 focus:ring-2 ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Email</label>
                        <input type="email" x-model="userForm.email"
                            class="w-full border rounded p-2 focus:ring-2 ring-blue-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Role</label>
                        <select x-model="userForm.role" class="w-full border rounded p-2 outline-none bg-white">
                            <option value="cashier">Cashier</option>
                            <option value="kitchen">Kitchen</option>
                            <option value="admin">Admin (Manager)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Password</label>
                        <input type="password" x-model="userForm.password"
                            class="w-full border rounded p-2 focus:ring-2 ring-blue-500 outline-none"
                            :placeholder="userForm.id ? '(Kosongkan jika tidak ingin ganti)' : 'Masukkan password'">
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button @click="showUserModal = false"
                        class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded">Batal</button>
                    <button @click="saveUser" :disabled="isSavingUser"
                        class="px-4 py-2 bg-blue-600 text-white font-bold rounded hover:bg-blue-700 disabled:opacity-50">
                        <span x-text="isSavingUser ? 'Menyimpan...' : 'Simpan User'"></span>
                    </button>
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
                    categories: [],
                    users: [],
                    newCategoryName: '',
                    showUserModal: false,
                    userForm: { id: null, name: '', email: '', role: 'cashier', password: '' },
                    isSavingUser: false,
                    report: { summary: {}, transactions: [] },
                    showModal: false,
                    form: { id: null, name: '', price: 0, category: '', image_url: '' },
                    searchQuery: '',
                    sortBy: 'name',
                    sortDir: 'asc',
                    isDragging: false,
                    imagePreview: null,
                    uploadedFile: null,

                    // Helper: Get API Base URL dynamically
                    get apiBase() {
                        // Logic: /admin/dashboard -> /api/v1
                        // Logic: /resto-bootcamp/admin/dashboard -> /resto-bootcamp/api/v1
                        const basePath = window.location.pathname.split('/admin/')[0];
                        return basePath + '/api/v1';
                    },

                    // Helper: Get Route Base URL dynamically
                    get routeBase() {
                        return window.location.pathname.split('/admin/')[0];
                    },

                    async init() {
                        if (!this.token) {
                            window.location.href = this.routeBase + '/admin/login';
                            return;
                        }
                        await this.fetchCategories();
                        await this.fetchMenus();
                    },

                    get filteredMenus() {
                        let result = this.menus;
                        if (this.searchQuery) {
                            const lower = this.searchQuery.toLowerCase();
                            result = result.filter(m => m.name.toLowerCase().includes(lower) || m.category.toLowerCase().includes(lower));
                        }
                        result = result.sort((a, b) => {
                            let valA = a[this.sortBy]; let valB = b[this.sortBy];
                            if (typeof valA === 'string') valA = valA.toLowerCase();
                            if (typeof valB === 'string') valB = valB.toLowerCase();
                            if (valA < valB) return this.sortDir === 'asc' ? -1 : 1;
                            if (valA > valB) return this.sortDir === 'asc' ? 1 : -1;
                            return 0;
                        });
                        return result;
                    },

                    sort(column) {
                        if (this.sortBy === column) { this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc'; }
                        else { this.sortBy = column; this.sortDir = 'asc'; }
                    },

                    async fetchCategories() {
                        try {
                            let res = await fetch(`${this.apiBase}/admin/categories`, {
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                            });
                            if (res.ok) { let data = await res.json(); this.categories = data; }
                        } catch (e) { }
                    },

                    async createCategory() {
                        if (!this.newCategoryName) return;
                        try {
                            let res = await fetch(`${this.apiBase}/admin/categories`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                                body: JSON.stringify({ name: this.newCategoryName })
                            });
                            if (res.ok) { this.newCategoryName = ''; await this.fetchCategories(); }
                            else alert('Gagal buat kategori');
                        } catch (e) { }
                    },

                    async updateCategory(cat) {
                        try {
                            await fetch(`${this.apiBase}/admin/categories/${cat.id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                                body: JSON.stringify({ name: cat.name })
                            });
                        } catch (e) { }
                    },

                    async deleteCategory(id) {
                        if (!confirm('Hapus kategori ini?')) return;
                        try {
                            let res = await fetch(`${this.apiBase}/admin/categories/${id}`, {
                                method: 'DELETE',
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                            });
                            if (res.ok) this.fetchCategories();
                        } catch (e) { }
                    },

                    async fetchUsers() {
                        try {
                            let res = await fetch(`${this.apiBase}/admin/users`, {
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                            });
                            if (res.ok) { this.users = await res.json(); }
                        } catch (e) { }
                    },

                    openUserModal(user = null) {
                        if (user) {
                            this.userForm = { id: user.id, name: user.name, email: user.email, role: user.role, password: '' };
                        } else {
                            this.userForm = { id: null, name: '', email: '', role: 'cashier', password: '' };
                        }
                        this.showUserModal = true;
                    },

                    async saveUser() {
                        this.isSavingUser = true;
                        try {
                            let url = `${this.apiBase}/admin/users`;
                            let method = 'POST';
                            if (this.userForm.id) {
                                url += `/${this.userForm.id}`;
                                method = 'PUT';
                            }

                            let res = await fetch(url, {
                                method: method,
                                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                                body: JSON.stringify(this.userForm)
                            });

                            let result = await res.json();
                            if (res.ok) {
                                this.showUserModal = false;
                                await this.fetchUsers();
                            } else {
                                alert('Gagal simpan user: ' + (result.message || 'Error'));
                            }
                        } catch (e) {
                            alert('System error');
                        } finally {
                            this.isSavingUser = false;
                        }
                    },

                    async deleteUser(id) {
                        if (!confirm('Hapus user ini?')) return;
                        try {
                            let res = await fetch(`${this.apiBase}/admin/users/${id}`, {
                                method: 'DELETE',
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                            });
                            if (res.ok) this.fetchUsers();
                            else alert('Gagal hapus user');
                        } catch (e) { }
                    },

                    async fetchMenus() {
                        try {
                            // Fix for default route
                            let res = await fetch(`${this.apiBase}/restaurants/${this.currentUser.restaurant_id || 1}/menu`);
                            let data = await res.json();
                            this.menus = data.menus;
                        } catch (e) { alert('Gagal memuat menu'); }
                    },

                    async fetchReports() {
                        try {
                            let res = await fetch(`${this.apiBase}/admin/reports`, {
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                            });
                            let data = await res.json();
                            this.report = data;
                        } catch (e) { }
                    },

                    openModal(menu = null) {
                        this.fetchCategories();
                        this.uploadedFile = null; this.isDragging = false;
                        if (menu) {
                            this.form = { ...menu };
                            this.imagePreview = menu.image_url;
                        } else {
                            let defaultCat = this.categories.length > 0 ? this.categories[0].name : '';
                            this.form = { id: null, name: '', price: 0, category: defaultCat, image_url: '' };
                            this.imagePreview = null;
                        }
                        this.showModal = true;
                    },

                    handleDrop(e) { this.isDragging = false; if (e.dataTransfer.files.length > 0) this.processFile(e.dataTransfer.files[0]); },
                    handleFile(e) { if (e.target.files.length > 0) this.processFile(e.target.files[0]); },
                    processFile(file) {
                        if (!file.type.startsWith('image/')) return alert('File harus gambar!');
                        this.uploadedFile = file;
                        const reader = new FileReader();
                        reader.onload = (e) => this.imagePreview = e.target.result;
                        reader.readAsDataURL(file);
                    },
                    removeImage() { this.uploadedFile = null; this.imagePreview = null; this.form.image_url = ''; },

                    async saveMenu() {
                        const isEdit = !!this.form.id;
                        const url = isEdit ? `${this.apiBase}/admin/menus/${this.form.id}` : `${this.apiBase}/admin/menus`;
                        const formData = new FormData();
                        formData.append('name', this.form.name);
                        formData.append('price', this.form.price);
                        formData.append('category', this.form.category);
                        if (this.form.image_url) formData.append('image_url', this.form.image_url);
                        if (this.uploadedFile) formData.append('image', this.uploadedFile);
                        if (isEdit) formData.append('_method', 'PUT');

                        try {
                            let res = await fetch(url, {
                                method: 'POST',
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                                body: formData
                            });
                            if (res.ok) { this.showModal = false; this.fetchMenus(); }
                            else { let d = await res.json(); alert('Gagal: ' + (d.message || JSON.stringify(d.errors))); }
                        } catch (e) { alert('Error system'); }
                    },

                    async deleteMenu(id) {
                        if (!confirm('Hapus menu ini?')) return;
                        try {
                            let res = await fetch(`${this.apiBase}/admin/menus/${id}`, {
                                method: 'DELETE',
                                headers: { 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' }
                            });
                            if (res.ok) this.fetchMenus();
                        } catch (e) { }
                    },

                    async toggleAvailability(menu) {
                        try {
                            menu.is_available = !menu.is_available;
                            let res = await fetch(`${this.apiBase}/admin/menus/${menu.id}`, {
                                method: 'PUT',
                                headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${this.token}`, 'Accept': 'application/json' },
                                body: JSON.stringify({ is_available: menu.is_available })
                            });
                            if (!res.ok) { menu.is_available = !menu.is_available; alert('Gagal update'); }
                        } catch (e) { menu.is_available = !menu.is_available; }
                    },
                    formatRupiah(num) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(num); },
                    formatDate(dateStr) { return new Date(dateStr).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' }); },

                    logout() {
                        localStorage.removeItem('admin_token');
                        window.location.href = this.routeBase + '/admin/login';
                    }
                }
            }
        </script>
</body>

</html>