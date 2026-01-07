<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-900 h-screen flex items-center justify-center">

    <div class="bg-gray-800 p-8 rounded-xl shadow-2xl w-full max-w-sm border border-gray-700" x-data="loginApp()">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">🚀 Owner Login</h1>

        <form @submit.prevent="login">
            <div class="mb-4">
                <label class="block text-gray-400 text-sm mb-2 font-bold">Email</label>
                <input type="email" x-model="email"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded p-3 focus:border-orange-500 focus:outline-none"
                    required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-400 text-sm mb-2 font-bold">Password</label>
                <input type="password" x-model="password"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded p-3 focus:border-orange-500 focus:outline-none"
                    required>
            </div>

            <button type="submit" :disabled="isLoading"
                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 rounded transition disabled:opacity-50">
                <span x-text="isLoading ? 'Loading...' : 'Masuk Dashboard'"></span>
            </button>
        </form>
    </div>

    <script>
        function loginApp() {
            return {
                email: 'admin@resto.com',
                password: 'password',
                isLoading: false,

                async login() {
                    this.isLoading = true;
                    try {
                        // FIX: Gunakan {{ url(...) }} agar sesuai dengan subfolder hosting
                        let res = await fetch('{{ url("/api/v1/login") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify({ email: this.email, password: this.password })
                        });

                        let data = await res.json();

                        if (res.ok) {
                            if (data.user.role !== 'admin') {
                                alert('Akses ditolak: Bukan akun Admin.');
                                return;
                            }
                            localStorage.setItem('admin_token', data.token);
                            localStorage.setItem('admin_user', JSON.stringify(data.user));
                            window.location.href = '{{ route('admin.dashboard') }}';
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