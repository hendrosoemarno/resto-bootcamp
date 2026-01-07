<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashier Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-100 h-screen flex items-center justify-center">

    <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-sm border border-gray-200" x-data="loginApp()">
        <h1 class="text-2xl font-bold text-gray-800 mb-6 text-center">💻 Kasir Login</h1>

        <form @submit.prevent="login">
            <div class="mb-4">
                <label class="block text-gray-600 text-sm mb-2 font-bold">Email</label>
                <input type="email" x-model="email"
                    class="w-full bg-gray-50 border border-gray-300 text-gray-800 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-600 text-sm mb-2 font-bold">Password</label>
                <input type="password" x-model="password"
                    class="w-full bg-gray-50 border border-gray-300 text-gray-800 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    required>
            </div>

            <button type="submit" :disabled="isLoading"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition disabled:bg-gray-400">
                <span x-text="isLoading ? 'Loading...' : 'Masuk Dashboard'"></span>
            </button>
        </form>
    </div>

    <script>
        function loginApp() {
            return {
                email: 'kasir@resto.com',
                password: 'password',
                isLoading: false,

                async login() {
                    this.isLoading = true;
                    try {
                        let res = await fetch('/api/v1/login', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: this.email, password: this.password })
                        });

                        let data = await res.json();

                        if (res.ok) {
                            if (data.user.role !== 'cashier' && data.user.role !== 'admin') {
                                alert('Akses ditolak: Bukan akun kasir.');
                                return;
                            }
                            localStorage.setItem('cashier_token', data.token);
                            localStorage.setItem('cashier_user', JSON.stringify(data.user));
                            window.location.href = '{{ route('cashier.dashboard') }}';
                        } else {
                            alert('Gagal: ' + (data.message || 'Error'));
                        }
                    } catch (e) {
                        alert('Jaringan Error');
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
</body>

</html>