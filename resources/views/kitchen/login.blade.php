<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kitchen Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="bg-gray-800 h-screen flex items-center justify-center">

    <div class="bg-gray-900 border border-gray-700 p-8 rounded-xl shadow-2xl w-full max-w-sm" x-data="loginApp()">
        <h1 class="text-2xl font-bold text-white mb-6 text-center">👨‍🍳 Kitchen Login</h1>

        <form @submit.prevent="login">
            <div class="mb-4">
                <label class="block text-gray-400 text-sm mb-2">Email</label>
                <input type="email" x-model="email"
                    class="w-full bg-gray-800 border border-gray-600 text-white rounded p-2 focus:border-orange-500 focus:outline-none"
                    required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-400 text-sm mb-2">Password</label>
                <input type="password" x-model="password"
                    class="w-full bg-gray-800 border border-gray-600 text-white rounded p-2 focus:border-orange-500 focus:outline-none"
                    required>
            </div>

            <button type="submit" :disabled="isLoading"
                class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-2 rounded transition disabled:opacity-50">
                <span x-text="isLoading ? 'Loading...' : 'Masuk Dapur'"></span>
            </button>
        </form>
    </div>

    <script>
        function loginApp() {
            return {
                email: 'chef@resto.com',
                password: 'password',
                isLoading: false,

                async login() {
                    this.isLoading = true;
                    // FIX: Pure JS Path Detection (No Blade)
                    // Ambil base URL tempat aplikasi berada
                    const basePath = window.location.pathname.split('/kitchen/')[0];
                    const apiUrl = basePath + '/api/v1/login';

                    try {
                        let res = await fetch(apiUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ email: this.email, password: this.password })
                        });

                        let data = await res.json();

                        if (res.ok) {
                            localStorage.setItem('kitchen_token', data.token);
                            localStorage.setItem('kitchen_user', JSON.stringify(data.user));
                            // Redirect manual tanpa Blade
                            window.location.href = basePath + '/kitchen/dashboard';
                        } else {
                            alert('Gagal: ' + (data.message || (data.email ? data.email[0] : 'Kredensial salah')));
                            console.error('Login failed:', data);
                        }
                    } catch (e) {
                        alert('Network Error: ' + e.message + '. Ensure backend is running.');
                        console.error(e);
                    } finally {
                        this.isLoading = false;
                    }
                }
            }
        }
    </script>
</body>

</html>