<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - {{ $restaurant->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800" x-data="restaurantApp({{ $restaurant->id }}, '{{ $tableNumber }}')">

    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-md mx-auto px-4 py-3 flex justify-between items-center">
            <div>
                <h1 class="font-bold text-lg text-gray-900">{{ $restaurant->name }}</h1>
                <p class="text-xs text-gray-500">Meja: <span class="font-bold text-orange-600">{{ $tableNumber }}</span>
                </p>
            </div>
            <!-- Cart Icon (Top) -->
            <button @click="showCart = true" class="relative p-2 text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                <span x-show="totalItems > 0" x-text="totalItems"
                    class="absolute top-0 right-0 bg-orange-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"></span>
            </button>
        </div>
    </header>

    <!-- Menu List -->
    <main class="max-w-md mx-auto pb-24">
        <!-- Categories (Scrollable) -->
        <div class="flex overflow-x-auto py-4 px-4 gap-2 no-scrollbar bg-white mb-2 shadow-sm">
            <template x-for="cat in categories" :key="cat">
                <button @click="activeCategory = cat"
                    :class="activeCategory === cat ? 'bg-orange-600 text-white' : 'bg-gray-100 text-gray-600'"
                    class="px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors"
                    x-text="cat.toUpperCase()">
                </button>
            </template>
        </div>

        <!-- Loading State -->
        <div x-show="loading" class="p-8 text-center text-gray-400">
            <div
                class="animate-spin inline-block w-8 h-8 border-4 border-orange-500 border-t-transparent rounded-full mb-2">
            </div>
            <p class="text-sm">Memuat Menu...</p>
        </div>

        <!-- Menu Grid -->
        <div class="px-4 grid gap-4">
            <template x-for="menu in filteredMenus" :key="menu.id">
                <div class="bg-white p-4 rounded-xl shadow-sm flex gap-4">
                    <!-- Image Placeholder -->
                    <div class="w-20 h-20 bg-gray-200 rounded-lg flex-shrink-0 bg-cover bg-center"
                        :style="menu.image_url ? `background-image: url('${menu.image_url}')` : ''">
                        <span x-show="!menu.image_url"
                            class="flex w-full h-full items-center justify-center text-gray-400 text-xs">No IMG</span>
                    </div>

                    <div class="flex-1 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-gray-900" x-text="menu.name"></h3>
                            <p class="text-orange-600 font-semibold text-sm" x-text="formatRupiah(menu.price)"></p>
                        </div>
                        <div class="flex justify-end items-center mt-2">
                            <button @click="addToCart(menu)"
                                class="bg-orange-100 text-orange-700 hover:bg-orange-200 px-4 py-1.5 rounded-lg text-sm font-medium flex items-center gap-1 transition-colors">
                                <span>+ Tambah</span>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </main>

    <!-- Floating Cart Button (Bottom) -->
    <div x-show="totalItems > 0" class="fixed bottom-0 left-0 right-0 p-4 max-w-md mx-auto z-20">
        <button @click="showCart = true"
            class="w-full bg-orange-600 text-white p-4 rounded-xl shadow-lg flex justify-between items-center font-bold hover:bg-orange-700 transition">
            <span class="flex items-center gap-2">
                <span class="bg-white text-orange-600 px-2 py-0.5 rounded text-sm" x-text="totalItems"></span>
                <span>items</span>
            </span>
            <span x-text="formatRupiah(totalPrice)"></span>
        </button>
    </div>

    <!-- Cart Modal (Slide Up) -->
    <div x-show="showCart"
        class="fixed inset-0 z-30 flex items-end justify-center sm:items-center bg-black/50 backdrop-blur-sm transition-opacity"
        x-transition.opacity style="display: none;">

        <div class="bg-white w-full max-w-md h-[90vh] sm:h-auto sm:rounded-2xl rounded-t-2xl flex flex-col shadow-2xl"
            @click.outside="showCart = false" x-transition:enter="transform transition ease-in-out duration-300"
            x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
            x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-y-0"
            x-transition:leave-end="translate-y-full">

            <!-- Cart Header -->
            <div class="p-4 border-b flex justify-between items-center bg-gray-50 rounded-t-2xl">
                <h2 class="font-bold text-lg">Keranjang Anda</h2>
                <button @click="showCart = false" class="text-gray-500">Tutup</button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <template x-if="cart.length === 0">
                    <p class="text-center text-gray-400 py-10">Keranjang masih kosong.</p>
                </template>

                <template x-for="(item, index) in cart" :key="index">
                    <div class="flex justify-between items-start border-b pb-4 last:border-0 last:pb-0">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-800" x-text="item.name"></h4>
                            <p class="text-orange-600 text-sm" x-text="formatRupiah(item.price * item.quantity)"></p>
                            <input type="text" x-model="item.note" placeholder="Catatan (opsional)..."
                                class="mt-1 w-full text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:border-orange-500">
                        </div>
                        <div class="flex items-center gap-3 ml-4 bg-gray-100 rounded-lg p-1">
                            <button @click="decrementItem(index)"
                                class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-gray-600 font-bold hover:bg-gray-50">-</button>
                            <span class="text-sm font-semibold w-6 text-center" x-text="item.quantity"></span>
                            <button @click="incrementItem(index)"
                                class="w-7 h-7 flex items-center justify-center bg-white rounded shadow-sm text-orange-600 font-bold hover:bg-gray-50">+</button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Footer Checkout -->
            <div class="p-4 border-t bg-gray-50 rounded-b-2xl">
                <div class="mb-4">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Pemesan</label>
                    <input type="text" x-model="customerName"
                        class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-2 focus:ring-orange-500 focus:outline-none"
                        placeholder="Masukkan nama Anda (Budi)">
                </div>

                <button @click="placeOrder()" :disabled="cart.length === 0 || isSubmitting"
                    class="w-full bg-orange-600 disabled:bg-gray-400 text-white font-bold py-3.5 rounded-xl shadow-lg hover:bg-orange-700 transition flex justify-center items-center gap-2">
                    <span x-show="isSubmitting"
                        class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                    <span x-text="isSubmitting ? 'Memproses...' : 'Pesan Sekarang'"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Script Logic -->
    <script>
        function restaurantApp(restaurantId, tableNumber) {
            return {
                restaurantId: restaurantId,
                tableNumber: tableNumber,
                menus: [],
                categories: ['all'],
                activeCategory: 'all',
                cart: [],
                customerName: '',
                loading: true,
                showCart: false,
                isSubmitting: false,

                // Helper: Get API Base URL dynamically
                get apiBase() {
                    // Extract base path from current URL
                    // /order/1 -> /api/v1
                    // /resto-bootcamp/order/1 -> /resto-bootcamp/api/v1
                    const basePath = window.location.pathname.split('/order/')[0];
                    return basePath + '/api/v1';
                },

                async init() {
                    try {
                        const apiUrl = `${this.apiBase}/restaurants/${this.restaurantId}/menu?table_number=${this.tableNumber}`;
                        console.log('Fetching menu from:', apiUrl);
                        
                        let response = await fetch(apiUrl);
                        console.log('Response status:', response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }
                        
                        let data = await response.json();
                        console.log('Response data:', data);

                        // Validate response structure
                        if (!data || !data.menus) {
                            throw new Error('Invalid response format: missing menus array');
                        }

                        this.menus = data.menus;
                        // Extract categories
                        let cats = [...new Set(this.menus.map(m => m.category))];
                        this.categories = ['all', ...cats];
                    } catch (e) {
                        console.error('Menu loading error:', e);
                        alert('Gagal memuat menu: ' + e.message);
                    } finally {
                        this.loading = false;
                    }
                },

                get filteredMenus() {
                    if (this.activeCategory === 'all') return this.menus;
                    return this.menus.filter(m => m.category === this.activeCategory);
                },

                addToCart(menu) {
                    let existing = this.cart.find(c => c.menu_id === menu.id);
                    if (existing) {
                        existing.quantity++;
                    } else {
                        this.cart.push({
                            menu_id: menu.id,
                            name: menu.name,
                            price: menu.price,
                            quantity: 1,
                            note: ''
                        });
                    }
                    this.showCart = true; // Auto open cart feedback or just showing badge
                },

                incrementItem(index) {
                    this.cart[index].quantity++;
                },

                decrementItem(index) {
                    if (this.cart[index].quantity > 1) {
                        this.cart[index].quantity--;
                    } else {
                        this.cart.splice(index, 1);
                        if (this.cart.length === 0) this.showCart = false;
                    }
                },

                get totalItems() {
                    return this.cart.reduce((sum, item) => sum + item.quantity, 0);
                },

                get totalPrice() {
                    return this.cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },

                formatRupiah(number) {
                    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
                },

                async placeOrder() {
                    if (!this.customerName) {
                        alert('Mohon isi nama pemesan terlebih dahulu.');
                        return;
                    }

                    if (!confirm(`Konfirmasi pesanan dengan total ${this.formatRupiah(this.totalPrice)}?`)) return;

                    this.isSubmitting = true;

                    let payload = {
                        restaurant_id: this.restaurantId,
                        table_number: this.tableNumber,
                        customer_name: this.customerName,
                        items: this.cart.map(i => ({
                            menu_id: i.menu_id,
                            quantity: i.quantity,
                            note: i.note
                        }))
                    };

                    try {
                        let res = await fetch(`${this.apiBase}/orders`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(payload)
                        });

                        let result = await res.json();

                        if (res.ok) {
                            // Redirect to status page (dynamic path)
                            const basePath = window.location.pathname.split('/order/')[0];
                            window.location.href = `${basePath}/order/status/${result.order_number}`;
                        } else {
                            alert('Gagal Order: ' + (result.message || 'Unknown Error'));
                        }
                    } catch (e) {
                        alert('Error system: ' + e.message);
                    } finally {
                        this.isSubmitting = false;
                    }
                }
            }
        }
    </script>
</body>

</html>