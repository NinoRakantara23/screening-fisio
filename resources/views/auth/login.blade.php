```blade
{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — FootScreen Pro</title>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600&family=Space+Mono&display=swap"
        rel="stylesheet">

    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('css/screening.css') }}">
</head>

<body style="background:#080C10; min-height:100vh; display:flex; align-items:center; justify-content:center">

    <div class="w-full max-w-sm mx-4">

        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl mx-auto mb-4"
                style="background:#00E676">
                ⚽
            </div>

            <h1 style="font-family:'Bebas Neue',sans-serif; font-size:28px; letter-spacing:4px; color:#00E676">
                FootScreen Pro
            </h1>

            <p class="text-sm mt-1" style="color:#7A8D9E">
                Sistem Screening Fisioterapi Sepak Bola
            </p>
        </div>

        {{-- Login Card --}}
        <div class="rounded-2xl p-8"
            style="background:#0E1419; border:1px solid rgba(0,230,118,0.2)">

            <h2 style="font-family:'Bebas Neue',sans-serif; font-size:22px; letter-spacing:2px; margin-bottom:24px">
                Masuk ke Akun
            </h2>

            {{-- Error Message --}}
            @if($errors->any())
                <div class="mb-4 px-4 py-3 rounded-xl text-sm"
                    style="background:rgba(255,68,68,0.1); border:1px solid rgba(255,68,68,0.3); color:#FF4444">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Login Form --}}
            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Role Selector --}}
                <div class="form-group mb-6">
                    <label for="role">Pilih Role (Opsional)</label>

                    <select
                        id="role"
                        onchange="fillLoginData(this.value)"
                        style="background:#141B22; border:1px solid rgba(0,230,118,0.2); color:#E8F0F7; width:100%; padding:12px; border-radius:8px; font-size:14px; cursor:pointer">

                        <option value="">-- Pilih untuk Quick Login --</option>
                        <option value="admin">👨‍💼 Admin</option>
                        <option value="fisio">🏥 Fisioterapis</option>
                        <option value="pelatih">⚽ Pelatih</option>
                        <option value="pemain">🏃 Pemain</option>

                    </select>

                    <p class="text-xs mt-2" style="color:#7A8D9E">
                        💡 Pilih role untuk mengisi email dan password secara otomatis.
                    </p>
                </div>

                {{-- Divider --}}
                <div class="flex items-center gap-3 mb-6">
                    <div style="flex:1; height:1px; background:rgba(0,230,118,0.2)"></div>

                    <span style="color:#7A8D9E; font-size:12px">ATAU</span>

                    <div style="flex:1; height:1px; background:rgba(0,230,118,0.2)"></div>
                </div>

                {{-- Email atau Nomor HP --}}
                <div class="form-group mb-4">
                    <label for="email">Email atau No. HP</label>

                    <input
                        type="text"
                        id="email"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="email@example.com / 0812xxxxxxx"
                        autocomplete="username"
                        required
                        autofocus>
                </div>

                {{-- Password --}}
                <div class="form-group mb-6">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required>
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 text-sm cursor-pointer"
                        style="color:#7A8D9E">

                        <input type="checkbox" name="remember" class="rounded">
                        Ingat saya
                    </label>

                    <span class="text-sm" style="color:#7A8D9E">
                        Lupa password? Hubungi administrator.
                    </span>
                </div>

                {{-- Submit --}}
                <button type="submit" class="btn-primary w-full py-3">
                    Masuk
                </button>
            </form>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs mt-6" style="color:#7A8D9E">
            FootScreen Pro © {{ date('Y') }}
        </p>

    </div>

    {{-- ===================== JAVASCRIPT ===================== --}}
    <script>
        const loginData = {
            admin: {
                email: 'admin@footscreen.com',
                password: 'password123',
                label: '👨‍💼 Admin'
            },

            fisio: {
                email: 'fisio@footscreen.com',
                password: 'password123',
                label: '🏥 Fisioterapis'
            },

            pelatih: {
                email: 'pelatih@footscreen.com',
                password: 'password123',
                label: '⚽ Pelatih'
            },

            pemain: {
                email: 'pemain@footscreen.com',
                password: 'password123',
                label: '🏃 Pemain'
            }
        };

        function fillLoginData(role) {
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            if (!role) {
                emailInput.value = '';
                passwordInput.value = '';
                return;
            }

            const data = loginData[role];

            if (!data) {
                return;
            }

            emailInput.value = data.email;
            passwordInput.value = data.password;

            showNotification(`✅ Siap login sebagai ${data.label}`);
        }

        function showNotification(message) {
            // Hapus notifikasi sebelumnya
            const existingToast = document.getElementById('login-toast');

            if (existingToast) {
                existingToast.remove();
            }

            // Buat notifikasi
            const toast = document.createElement('div');

            toast.id = 'login-toast';

            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #00E676;
                color: #080C10;
                padding: 12px 20px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                z-index: 9999;
                box-shadow: 0 8px 24px rgba(0,0,0,0.3);
                animation: slideIn 0.3s ease-out;
            `;

            toast.textContent = message;
            document.body.appendChild(toast);

            // Hapus notifikasi setelah 3 detik
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }

        // Animasi notifikasi
        const style = document.createElement('style');

        style.textContent = `
            @keyframes slideIn {
                from {
                    transform: translateX(400px);
                    opacity: 0;
                }

                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
        `;

        document.head.appendChild(style);
    </script>

</body>
</html>
```