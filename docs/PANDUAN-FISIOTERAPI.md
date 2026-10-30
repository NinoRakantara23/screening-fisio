# Panduan & Ringkasan Bagian Fisioterapi
## FootScreen Pro — Sistem Screening Fisioterapi Sepak Bola

| Item | Keterangan |
|---|---|
| Role | `fisio` (Fisioterapis) + `admin` |
| Repositori | `https://github.com/NinoRakantara23/screening-fisio` |
| Branch pekerjaan | `fix/audit-tahap-1` |
| PR | #1 — `Fix/audit tahap 1` (merged 12 Okt 2026) |
| Basis kode dokumen ini | `ed2c692` (branch) dan `3d2390c` (`origin/main`) |
| Status | Selesai, di-push, dan sudah di-merge ke `main` |

---

## 1. Ringkasan Eksekutif

Bagian Fisioterapi mencakup **manajemen atlet, 10 tes fisik, dashboard fisik, dan laporan fisioterapi**. Pekerjaan yang dilakukan berfokus pada:

1. **Keamanan akses** — role diseragamkan menjadi `fisio`, registrasi publik ditutup, route lupa password dihapus, login di-throttle.
2. **Integritas data** — identitas dan tanda vital benar-benar tersimpan di 10 form tes.
3. **Kualitas laporan** — rating `Sempurna/Fair/Weak/Kurang Sekali` dikenali, radar atlet terisi 10/10, laporan fisio responsif di 375 px.
4. **Keamanan input** — upload foto memakai UUID, validasi atlet terlihat, XSS pada label foto ditutup.
5. **Bukti pengujian** — suite E2E Playwright mencakup seluruh alur fisio.

Hasil suite E2E: **54 lulus / 8 gagal** saat audit → **62 lulus / 0 gagal** setelah perbaikan.

---

## 2. Cakupan Role Fisioterapi

| Modul | Route | Akses fisio |
|---|---|---|
| Dashboard | `/dashboard` | ✅ (statistik fisik) |
| Data Atlet | `/athletes` | ✅ lihat + tambah/edit/hapus |
| Semua Tes | `/tests` | ✅ |
| 10 Form Tes | `/forms/{endurance,speed,agility,strength,power,accuracy,reaction,coordination,flexibility,balance}` | ✅ |
| Laporan Fisioterapi | `/report` | ✅ |
| Export PDF | `/report/export/pdf` | ✅ |
| Export Excel | `/report/export/excel` | ✅ |
| Detail laporan atlet | `/report/athlete/{id}` | ✅ |
| Mental Assessment | `/mental` | ❌ 403 |
| Taktik Assessment | `/taktik` | ❌ 403 |
| Laporan Pelatih | `/reports` | ❌ 403 |
| Area Pemain | `/pemain` | ❌ 403 |

> `admin` dapat mengakses seluruh route karena `CheckRole` memberi bypass khusus admin.

---

## 3. Perubahan yang Dikerjakan (Branch `fix/audit-tahap-1`)

### 3.1 Fondasi & keamanan — `5fbb5c0`

| Sebelum | Sesudah |
|---|---|
| Role bernama `fisioterapis` | Role diseragamkan menjadi `fisio` |
| Semua role bisa tambah/edit/hapus atlet | Hanya `fisio` (+ admin); pelatih read-only |
| Login tanpa batas | Throttle 5 percobaan/menit |
| Tombol aksi tanpa allow-list | Guard tombol berbasis role |
| Upload foto ikut ter-track git | Upload foto dilepas dari tracking |

### 3.2 Manajemen atlet

| Commit | Perubahan | Dampak ke fisio |
|---|---|---|
| **T3** `d169088` | Nama file foto memakai UUID; unlink hanya di `uploads/athletes`; label foto memakai `textContent` | Upload nama file aneh tidak lagi 500 dan aman dari XSS |
| **T5** `24856f4` | `birth_date` wajib `before_or_equal:today`; pesan error Bahasa Indonesia tampil di `#errorAlert`; modal terbuka kembali; `old()` mengisi ulang field | Fisio langsung melihat alasan input ditolak |

### 3.3 Tes fisik & data identitas

| Commit | Perubahan | Dampak ke fisio |
|---|---|---|
| **T7** `a9b9019` | `$identityRules` dipakai di 10/10 form: `gender` wajib `putra/putri`, `height`/`weight` numerik, `vital_*` string | Tinggi, berat, jenis kelamin, TD/suhu/nadi/respirasi tersimpan (sebelumnya hilang tanpa pesan) |

### 3.4 Rating & radar atlet

| Commit | Perubahan | Dampak ke fisio |
|---|---|---|
| **T6** `b998293` | Rating `Sempurna/Fair/Weak/Kurang Sekali` dikenali dengan skala 5/4/3/2/1; `display_ratings` tampil di tabel hasil | Radar atlet 10/10; label rating muncul di tabel hasil |
| **T11** `32c1e41` | Nilai `Fair` disesuaikan menjadi 2 mengikuti urutan opsi form Fleksibilitas | Sumbu Fleksibilitas di radar tidak salah nilai |

### 3.5 Dashboard

| Commit | Perubahan | Dampak ke fisio |
|---|---|---|
| **T8** `987daf3` | `$includePhysical = in_array(role, ['admin','fisio'], true)` | Fisio tetap melihat kartu YoYo/Speed/Agility/Push-up; pelatih tidak |

### 3.6 Laporan fisioterapi

| Commit | Perubahan | Dampak ke fisio |
|---|---|---|
| **T9** `3dbbeb9` | Baris kontrol, filter, dan tombol export memakai `flex-wrap` + `gap-2` | `/report` tidak overflow di 375 px; tombol export membungkus |

> **Catatan:** T1 (`f980cd1`) adalah perbaikan laporan **pelatih**, bukan laporan fisio. Laporan fisio `/report` tidak mengalami 500 seperti laporan pelatih.

### 3.7 Autentikasi

| Commit | Perubahan | Dampak ke fisio |
|---|---|---|
| **T2** `fc7a8cd` | Route & link registrasi dihapus | Tidak ada pendaftaran mandiri; akun fisio dibuat admin |
| **T4** `888806e` | Route `/forgot-password` dihapus; link diganti teks statis | Tidak ada 500; fisio diarahkan menghubungi administrator |
| **T10** `0f64cf4` | `RegisterController` & view register dihapus | Registrasi ditutup total |

### 3.8 Bukti pengujian — `d448d25` (T12)

Suite E2E yang mencakup alur fisio:

- `04-athlete.spec.js` — CRUD atlet, validasi, upload foto
- `05-physical.spec.js` — 10 form tes fisik
- `03-authz.spec.js` — matriks otorisasi 3 role
- `07-dashboard-frontend.spec.js` — dashboard fisio vs pelatih
- `08-security.spec.js` — CSRF, cookie, XSS

---

## 4. Perubahan di `main` Setelah PR #1 (oleh Ambulau)

Branch `fix/audit-tahap-1` **belum memuat** dua commit ini. Jika demo dijalankan dari folder lokal yang masih di `ed2c692`, perubahan berikut belum terlihat.

### 4.1 Commit `4eb28fd` — Perbaikan autentikasi dan testing Laravel

| Perubahan | Dampak ke fisio |
|---|---|
| `RegisterController.php` ditambahkan kembali | Route `/register` tetap tidak ada di `routes/web.php` (masih 404). Jika route dipasang lagi, default role `fisio` berisiko terbuka |
| `athletes/show.blade.php` dirombak besar | Tampilan detail atlet yang dipakai fisio berubah; radar & hasil tes perlu diuji ulang |
| `LoginController` diubah | Redirect login menyesuaikan role |
| `TESTING_CHECKLIST.md` ditambahkan | Checklist pengujian manual |

### 4.2 Commit `3d2390c` — update semua fitur dan modul player

| Perubahan | Dampak ke fisio |
|---|---|
| Role baru `pemain` + area `/pemain` (read-only data sendiri) | Hasil tes fisioterapi bisa dilihat pemain lewat menu `Hasil Fisioterapi` |
| Migrasi `users.athlete_id` & `users.phone` | Akun pemain terhubung ke atlet; login bisa memakai email atau nomor HP |
| Login redirect per role | Fisio diarahkan ke `/tests`, bukan `/dashboard` |
| `/` selalu logout lalu ke login | Membuka root tidak langsung ke dashboard |
| `/dashboard` & `/athletes` memakai `role:fisio,pelatih` | Fisio tetap boleh; admin tetap boleh karena bypass admin |
| `athletes/show` dirombak lagi | Radar hanya dirender untuk `admin`/`fisio`; indikator menjadi `X/10 dinilai` |
| Nav layout ditambah menu pemain | Menu fisio tetap sama |

---

## 5. Alur Fisio: Dulu vs Sekarang

| Tahap | Di branch `ed2c692` | Di `main` sekarang `3d2390c` |
|---|---|---|
| Login | Redirect ke `/dashboard` | Redirect ke `/tests` |
| Dashboard | Statistik fisik tampil | Statistik fisik tampil (tetap) |
| Data Atlet | CRUD oleh fisio/admin | CRUD oleh fisio/admin (tetap) |
| Tes fisik | 10 form, identitas/vital tersimpan | Sama (tidak diubah setelah merge) |
| Detail atlet | Radar + hasil tes | Radar + hasil tes, layout dirombak, label `X/10 dinilai` |
| Laporan fisio | `/report` + PDF/Excel, responsif | Sama (tidak diubah setelah merge) |
| Mental/Taktik | 403 | 403 |
| Area pemain | Tidak ada | Ada, read-only untuk role `pemain` |

Logika inti fisio tidak berubah setelah merge. Yang berubah adalah shell aplikasi: login redirect, area pemain, dan tampilan detail atlet.

---

## 6. Naskah Demo Visual (Khusus Fisioterapi)

### Persiapan

1. Jalankan aplikasi:

   ```powershell
   cd C:\xampp\htdocs\screening-fisio
   php artisan serve --host=127.0.0.1 --port=8000
   ```

2. Buka `http://127.0.0.1:8000`.
3. Siapkan tab GitHub PR #1: `https://github.com/NinoRakantara23/screening-fisio/pull/1`.
4. Siapkan folder screenshot audit: `e2e-audit/screenshots`.
5. Akun demo:

   | Role | Email | Password |
   |---|---|---|
   | Admin | `admin@footscreen.com` | `password123` |
   | Fisio | `fisio@footscreen.com` | `password123` |
   | Pelatih | `pelatih@footscreen.com` | `password123` |

> Login dibatasi 5 kali/menit. Jangan salah password berulang kali saat demo.

### Adegan 1 — Login fisio & redirect

**Langkah:**

1. Buka `/login` dalam mode incognito.
2. Pilih role `Fisioterapis` pada dropdown quick login.
3. Klik **Masuk**.

**Yang ditunjukkan:**

- Di branch `ed2c692`: masuk ke `/dashboard`.
- Di `main` terbaru: masuk ke `/tests` (Semua Tes).
- Tidak ada link “Daftar”.
- Ada teks “Lupa password? Hubungi administrator.”

**Kalimat penjelasan:**

> “Registrasi publik sudah ditutup dan route lupa password dihapus. Akun fisio dibuat oleh administrator.”

**Bukti sebelum:** screenshot `A7-register-form.png`, commit `fc7a8cd`, `888806e`, `0f64cf4`.

### Adegan 2 — Dashboard fisio vs pelatih

**Langkah:**

1. Login sebagai `Fisio` → buka `/dashboard`.
2. Tunjukkan kartu `Rata-rata YoYo`, `Speed 50M terbaik`, `Agility rata-rata`, `Push-up terbanyak`.
3. Logout, login sebagai `Pelatih` → buka `/dashboard`.
4. Tunjukkan kartu fisik tersebut **tidak ada**; hanya `Rata-rata mental` dan `Rata-rata taktik`.

**Kalimat penjelasan:**

> “Statistik fisik dibatasi tegas hanya untuk admin dan fisio, tidak bocor ke pelatih.”

**Bukti sebelum:** `F2-dashboard-pelatih.png` vs `F1-dashboard-admin.png`, commit `987daf3`.

### Adegan 3 — Validasi & upload foto atlet

**Langkah:**

1. Login `Fisio` → `/athletes` → **+ Tambah Atlet**.
2. Klik **Tambah Atlet** tanpa mengisi apa pun.
3. Tunjukkan alert merah “Nama lengkap wajib diisi.” dan modal tetap terbuka.
4. Isi nama, lalu isi tanggal lahir `31/12/2099` → submit.
5. Tunjukkan error “Tanggal lahir tidak boleh melewati hari ini.”
6. Upload foto yang sudah di-rename menjadi `a"b'c<d>.png` → submit.
7. Tunjukkan data tersimpan, foto tampil, dan nama file di `public/uploads/athletes` berupa UUID.
8. Hapus atlet demo setelah presentasi.

**Kalimat penjelasan:**

> “Sebelumnya validasi gagal tanpa pesan dan nama file khusus memicu 500. Sekarang pesan tampil dan file disimpan dengan UUID.”

**Bukti sebelum:** `C3-validasi.png` … `C7-validasi.png`, `C10-modal-edit-xss.png`, commit `24856f4`, `d169088`.

### Adegan 4 — Tes fisik & identitas

**Langkah:**

1. Buka `/tests` → tunjukkan 10 kartu tes.
2. Buka `ENDURANCE`.
3. Isi atlet, `Jenis Kelamin`, tinggi, berat, TD/suhu/nadi/respirasi, jarak, dan rating.
4. Klik **Simpan** → tunjukkan pesan “Data YoYo Test berhasil disimpan!”
5. Buka detail atlet → tunjukkan hasil tes muncul.
6. Opsional (read-only), verifikasi data tersimpan:

   ```powershell
   php artisan tinker --execute="dump(App\Models\TestResult::latest('id')->first(['test_type','gender','height','weight','vital_td','vital_suhu','vital_nadi','vital_respirasi'])->toArray());"
   ```

**Kalimat penjelasan:**

> “Sebelum T7, tinggi, berat, dan tanda vital diketik tetapi hilang. Sekarang aturan identitas yang sama dipakai di 10 dari 10 form.”

**Bukti sebelum:** `D-endurance.png`, tabel B-08 di `e2e-audit/laporan-teks.md`, commit `a9b9019`.

### Adegan 5 — Radar & rating atlet

**Langkah:**

1. Buka `/athletes` → pilih atlet yang sudah punya banyak hasil tes.
2. Tunjukkan panel **Radar Performa** dan indikator `X/10 dinilai`.
3. Scroll ke tabel **Hasil Tes Fisioterapi** → tunjukkan label seperti `Hexagonal (rating): Baik Sekali`, `Push-up (rating): Baik`, `Rating: Sempurna`.
4. Buka `/report` → tunjukkan kolom rating dan angka `10/10`.

**Kalimat penjelasan:**

> “Sebelum T6, label Sempurna, Fair, dan Weak tidak dikenali sehingga radar hanya 7/10. Sekarang skalanya 5/4/3/2/1 dan rating tampil di tabel.”

**Bukti sebelum:** `D11-detail-hasil-tes.png`, tabel B-07, commit `b998293` dan `32c1e41`.

### Adegan 6 — Laporan fisio & responsif 375 px

**Langkah:**

1. Login `Fisio` → buka `/report`.
2. Tunjukkan kartu ringkasan, filter posisi/bulan, tabel rekap, dan tombol export.
3. Ganti filter posisi → tabel berubah.
4. Klik **Export Semua PDF** dan **Export Excel** → file terunduh.
5. Buka DevTools → mode responsif 375×812.
6. Tunjukkan tidak ada scroll horizontal halaman; tombol export membungkus.

**Kalimat penjelasan:**

> “Sebelum T9, halaman `/report` punya lebar 507 px di layar 375 px. Sekarang baris kontrol dan tombol memakai `flex-wrap`.”

**Bukti sebelum:** `H2-mobile--report.png` vs `H3-desktop--report.png`, commit `3dbbeb9`.

### Adegan 7 — Batas akses fisio

**Langkah:**

1. Tetap login sebagai `Fisio`.
2. Buka `/mental` → tunjukkan **403**.
3. Buka `/taktik` → tunjukkan **403**.
4. Buka `/reports` → tunjukkan **403**.
5. Buka `/pemain` → tunjukkan **403**.

**Kalimat penjelasan:**

> “Fisio hanya memegang modul fisik. Mental, taktik, laporan pelatih, dan area pemain ditolak oleh middleware role.”

### Adegan 8 — Bukti otomatis

**Langkah:**

1. Buka GitHub `e2e-audit/tests/` → tunjukkan 10 spec.
2. Buka `e2e-audit/laporan-teks.md` → tunjukkan tabel B-01…B-12.
3. Buka PR #1 → tunjukkan **Merged, 14 commits**.
4. Opsional jalankan ulang suite:

   ```powershell
   cd C:\xampp\htdocs\screening-fisio\e2e-audit
   npx playwright test
   ```

> Menjalankan suite dapat memperbarui `notes.json` yang di-track. Jika ingin bukti tetap beku, cukup tunjukkan hasil yang sudah ada di GitHub.

---

## 7. Cara Menunjukkan “Sebelum vs Sesudah” Tanpa Mengubah Kode

| Cara | Langkah | Cocok untuk |
|---|---|---|
| GitHub diff | Buka commit → tab **Files changed** (merah = sebelum, hijau = sesudah) | T1–T11 |
| Screenshot audit | Buka `e2e-audit/screenshots` → tampilkan PNG relevan | T2, T3, T5, T6, T8, T9 |
| Tabel laporan | Buka `e2e-audit/laporan-teks.md` | Semua temuan |

Pola presentasi yang efektif:

```text
KIRI  : GitHub commit diff (sebelum → sesudah)
KANAN : Aplikasi live (hasil sesudah)
BAWAH : Screenshot audit (tampilan sebelum)
```

Jangan memakai `git checkout` ke commit lama saat demo karena mengubah isi folder kerja.

---

## 8. Bukti Commit Bagian Fisioterapi

| Area fisio | Commit |
|---|---|
| Role & keamanan | `5fbb5c0` |
| Registrasi & lupa password | `fc7a8cd`, `888806e`, `0f64cf4` |
| Upload foto atlet | `d169088` |
| Validasi atlet | `24856f4` |
| Rating & radar | `b998293`, `32c1e41` |
| Identitas & vital 10 form | `a9b9019` |
| Dashboard fisio | `987daf3` |
| Laporan fisio responsif | `3dbbeb9` |
| Suite E2E fisio | `d448d25` |
| Merge ke main | `638726b` |

Semua sudah di-push dan masuk `main` melalui PR #1.

---

## 9. Catatan & Risiko yang Perlu Dicek

1. **`PlayerController::RATING_PERCENT`** hanya mengenal label Inggris (`excellent`, `good`, `fair`, `poor`, dst.). Label form fisio (`Sempurna`, `Weak`, `Kurang Sekali`) belum ada di daftar itu, sehingga persentase/grade pemain bisa tampil `—`. Ini benturan dengan T6/T11 dan perlu diselaraskan.
2. **`RegisterController` ada lagi di `main`** meskipun route-nya belum dipasang. Pastikan route register tidak ditambahkan tanpa mengubah default role dari `fisio`.
3. **`athletes/show.blade.php` dirombak dua kali** setelah merge. Radar dan `display_results` masih ada, tetapi perlu diuji ulang dengan data 10 tes.
4. **Admin bypass** di `CheckRole` membuat admin bisa membuka semua route fisio. Pastikan ini memang kebijakan yang diinginkan.
5. **Branch lokal tertinggal 2 commit** dari `origin/main`. Untuk melihat perubahan pemain/login terbaru, perlu `git switch main` lalu `git pull`.

---

## 10. Checklist Demo

- [ ] Server `php artisan serve` berjalan di port 8000.
- [ ] 3 akun bisa login (jangan salah password berulang — throttle 5/menit).
- [ ] Tab GitHub PR #1 sudah terbuka.
- [ ] Folder `e2e-audit/screenshots` sudah terbuka.
- [ ] Browser zoom 100%.
- [ ] Data demo yang dibuat akan dihapus setelah presentasi.
- [ ] Tidak ada tab berisi `.env` atau data sensitif.

---

## 11. Perintah Verifikasi (Read-Only)

```powershell
# Commit atas nama Anda
git log --author="ninorakantara@gmail.com" --oneline --decorate

# Bukti sudah masuk main
git merge-base --is-ancestor ed2c692 origin/main
if ($?) { "SUDAH ada di origin/main" }

# Bukti branch sudah di-push
git branch -a -vv

# File audit ikut ter-commit
git ls-files e2e-audit

# Total kontribusi perbaikan T1–T12
git diff --stat 5fbb5c0..ed2c692

# Commit yang bukan milik Anda
git log origin/main --not ed2c692 --oneline
```

---

## 12. Ringkasan Alur Demo (10–12 Menit)

| Waktu | Adegan | Yang ditunjukkan |
|---|---|---|
| 0–1 mnt | Pembuka | Repo public + PR #1 Merged |
| 1–3 mnt | Adegan 1 | Login fisio & redirect |
| 3–4 mnt | Adegan 2 | Dashboard fisio vs pelatih |
| 4–6 mnt | Adegan 3 | Validasi & upload foto |
| 6–7 mnt | Adegan 4 | Tes fisik & identitas |
| 7–9 mnt | Adegan 5 | Radar & rating |
| 9–10 mnt | Adegan 6 | Laporan fisio & 375 px |
| 10–11 mnt | Adegan 7 | Batas akses 403 |
| 11–12 mnt | Adegan 8 | Suite E2E + PR #1 |

---

*Dokumen ini disusun dari riwayat Git dan artefak audit `e2e-audit/`. Tidak ada kode aplikasi yang diubah.*
