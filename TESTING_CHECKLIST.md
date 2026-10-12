# ✅ Checklist Testing — FootScreen Pro

> Project: `C:\xampp\htdocs\screening-fisio` | Laravel 12 + MySQL (`screening_fisio`)
> Cara pakai: centang `[x]` setelah tiap langkah lolos di browser. Jalankan `php artisan serve` lalu buka `http://127.0.0.1:8000`.

---

## 0) Prasyarat

- [ ] XAMPP: Apache & MySQL **Running**
- [ ] Database `screening_fisio` ada (buat di phpMyAdmin jika belum)
- [ ] `.env` sudah benar:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=screening_fisio
  DB_USERNAME=root
  DB_PASSWORD=
  SESSION_DRIVER=database   # atau file untuk dev simpel
  ```

## 1) Setup Awal (sekali saja)

```bash
cd C:\xampp\htdocs\screening-fisio
copy .env.example .env        # jika .env belum ada
php artisan key:generate
php artisan migrate --seed    # buat tabel + 3 user + 5 atlet
php artisan storage:link
npm install
npm run build                 # atau npm run dev saat develop
php artisan serve             # http://127.0.0.1:8000
```

- [ ] `php artisan migrate --seed` sukses (tidak error)
- [ ] `php artisan storage:link` sukses
- [ ] `npm run build` sukses (atau `npm run dev` jalan)
- [ ] `php artisan serve` jalan tanpa error
- [ ] Buka `http://127.0.0.1:8000` → redirect ke `/login` (guest)

**Akun default:**

| Role | Email | Password |
|------|-------|----------|
| Admin | `admin@footscreen.com` | `password123` |
| Fisio | `fisio@footscreen.com` | `password123` |
| Pelatih | `pelatih@footscreen.com` | `password123` |

---

## 2) Auth & Redirect Berdasarkan Role

| # | Langkah | Expected | Status |
|---|---------|----------|--------|
| 2.1 | Buka `/` tanpa login | Redirect ke `/login` | [ ] |
| 2.2 | Login sebagai **admin** | Redirect ke `/dashboard` | [ ] |
| 2.3 | Logout → login sebagai **fisio** | Redirect ke `/tests` (Semua Tes) | [ ] |
| 2.4 | Logout → login sebagai **pelatih** | Redirect ke `/mental` | [ ] |
| 2.5 | Akses `/dashboard` tanpa login | Redirect ke `/login` | [ ] |
| 2.6 | Register akun baru (default role fisio) | Redirect ke `/tests` | [ ] |
| 2.7 | Login salah password | Muncul error `Email atau password salah` | [ ] |
| 2.8 | Klik **Logout** | Kembali ke `/login`, session hilang | [ ] |
| 2.9 | Akses `/` setelah login sesuai role | `/` redirect ke halaman role masing-masing | [ ] |

---

## 3) Role Admin — Akses Penuh

Login: `admin@footscreen.com`

- [ ] Sidebar menampilkan **semua menu**: Overview + Screening Fisioterapi + Tes Fungsional + Output + Assessment Mental + Assessment Taktik + Laporan Assessment
- [ ] `/dashboard` tampil normal (stats, chart, recent athletes)
- [ ] `/athletes` — daftar atlet tampil (tidak error `endforeach`)
- [ ] `/athletes/{id}` — detail atlet tampil (profil + radar + tabel hasil)
- [ ] `/tests` bisa dibuka
- [ ] Semua `/forms/*` bisa dibuka (10 form)
- [ ] `/report` bisa dibuka + export PDF/Excel
- [ ] `/mental` & `/taktik` bisa dibuka (CRUD)
- [ ] `/reports` bisa dibuka + export

---

## 4) Role Fisio — Screening Fisioterapi

Login: `fisio@footscreen.com`

- [ ] Sidebar hanya: Overview + Screening Fisioterapi + Tes Fungsional + Output (tidak ada menu Mental/Taktik)
- [ ] `/dashboard` tampil (hanya data fisik, tidak ada mental/taktik)
- [ ] `/athletes` tampil + tombol **Tambah Atlet** ada
- [ ] `/tests` (Semua Tes) tampil
- [ ] 10 Form bisa dibuka & submit:
  - [ ] `forms/endurance` (YoYo)
  - [ ] `forms/speed` (50M)
  - [ ] `forms/agility`
  - [ ] `forms/strength`
  - [ ] `forms/power`
  - [ ] `forms/accuracy`
  - [ ] `forms/reaction`
  - [ ] `forms/coordination`
  - [ ] `forms/flexibility`
  - [ ] `forms/balance`
- [ ] Tiap submit → data masuk `test_results`, redirect sukses, toast muncul
- [ ] `/report` tampil + export PDF/Excel berhasil download
- [ ] `/report/athlete/{id}` tampil
- [ ] Topbar tombol **+ Input Tes Fisik** muncul

---

## 5) Role Pelatih — Mental & Taktik

Login: `pelatih@footscreen.com`

- [ ] Sidebar hanya: Overview + Assessment Mental + Assessment Taktik + Output (tidak ada menu Screening)
- [ ] `/dashboard` tampil (hanya mental/taktik, tidak ada fisik)
- [ ] `/athletes` tampil **tanpa** tombol Tambah/Edit/Hapus (read-only)
- [ ] `/mental` — list tampil
- [ ] `/mental/create` — form tampil, pilih atlet + tanggal + 6 skor (1-5) + catatan + rekomendasi → submit sukses
- [ ] `/mental/{id}` — detail tampil
- [ ] `/mental/{id}/edit` — edit & update sukses
- [ ] DELETE mental → sukses
- [ ] Sama untuk `/taktik` (8 kategori: dribbling, passing, shooting, positioning, reading, decision, defense, set pieces)
- [ ] `/reports` tampil + export PDF/Excel
- [ ] Topbar tombol **+ Mental** dan **+ Taktik** muncul (bukan + Input Tes Fisik)
- [ ] `/athletes/{id}` versi pelatih: tampil profil + tabel mental/taktik (bukan radar fisik)

---

## 6) RBAC Negative Test — Harus 403 / Redirect

| # | Login sebagai | Coba akses | Expected |
|---|---------------|------------|----------|
| 6.1 | fisio | `GET /mental` | **403 Forbidden** |
| 6.2 | fisio | `GET /taktik` | 403 |
| 6.3 | fisio | `GET /reports` | 403 |
| 6.4 | pelatih | `GET /tests` | 403 |
| 6.5 | pelatih | `GET /forms/endurance` | 403 |
| 6.6 | pelatih | `GET /report` | 403 |
| 6.7 | pelatih | `POST /athletes` | 403 |
| 6.8 | pelatih | `PUT /athletes/{id}` / `DELETE` | 403 |
| 6.9 | guest | `GET /dashboard`, `/athletes`, `/tests` | Redirect ke `/login` |

- [ ] Semua di atas sesuai (tidak bocor)

---

## 7) CRUD Data Atlet (Bug yang baru diperbaiki)

> Bug sebelumnya: `syntax error, unexpected token "endforeach"` di `athletes/index.blade.php:94`

- [ ] Login admin/fisio → `/athletes` **tidak error**, tabel tampil
- [ ] Klik **Tambah Atlet** → modal terbuka → isi semua field → Simpan → toast sukses → data muncul di tabel
- [ ] Upload foto (JPG/PNG, <2MB) → foto tersimpan di `public/uploads/athletes/`
- [ ] Klik **Lihat** → `/athletes/{id}` tampil normal
- [ ] Klik **Edit** (di list) → modal terisi data lama → ubah → Update → sukses
- [ ] Edit foto → foto lama terhapus, foto baru tampil
- [ ] Klik **Hapus** → confirm → data hilang → toast sukses
- [ ] Validasi: nama kosong → error validation muncul
- [ ] Pagination (jika >15 data) jalan
- [ ] Role pelatih: tombol Edit/Hapus **tidak muncul** di list

---

## 8) Export Laporan

- [ ] Fisio: `/report/export/pdf` → download PDF, isi benar
- [ ] Fisio: `/report/export/excel` → download Excel/CSV
- [ ] Pelatih: `/reports/export/pdf` → download PDF
- [ ] Pelatih: `/reports/export/excel` → download Excel
- [ ] Jika butuh `barryvdh/laravel-dompdf` sudah terinstall (`composer.json` ada)

---

## 9) UI / Layout

- [ ] Sidebar toggle (mobile) jalan
- [ ] Flash message `success`/`error` muncul setelah aksi
- [ ] Toast di kanan bawah muncul
- [ ] Tidak ada error di Console browser (F12)
- [ ] Responsive: coba di HP (400px) — tidak ada horizontal scroll
- [ ] Semua view pakai `layouts/app.blade.php` tanpa error

---

## 10) Automated Test (opsional)

```bash
php artisan test
# atau
php artisan test --filter ExampleTest
```

- [ ] `Tests\Unit\ExampleTest` PASS
- [ ] `Tests\Feature\ExampleTest` — sesuaikan ekspektasi:
  ```php
  // Sebelum (gagal karena redirect):
  $response->assertStatus(200);
  // Sesuaikan jadi:
  $response->assertRedirect('/login'); // untuk guest
  // atau
  $this->actingAs($user)->get('/')->assertRedirect(route('dashboard.index'));
  ```

---

## 11) Jika Masih Error

```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan optimize:clear
```

Cek log:
```bash
tail -n 100 storage/logs/laravel.log
# atau buka file storage/logs/laravel.log
```

Cek syntax blade:
```bash
php -l resources/views/athletes/index.blade.php
php -l resources/views/athletes/show.blade.php
php -l routes/web.php
```

---

## Ringkasan Hasil

- Total item: ~70
- Jika semua [x], aplikasi siap demo / deploy.
- Catat bug yang ditemukan di bawah:

**Bug ditemukan:**

| Tanggal | Deskripsi | Status |
|---------|-----------|--------|
| 2026-10-12 | `athletes/index.blade.php:94` endforeach mismatch | ✅ Fixed |
| | | |

**Catatan tester:**

> Tulis catatan di sini...

---

*Last updated: 2026-10-12 — FootScreen Pro*
