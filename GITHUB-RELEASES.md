# Panduan Pembaruan Theme via GitHub Releases

Theme Haka kini terintegrasi langsung dengan **GitHub Releases**. Anda tidak perlu lagi mengelola server JSON manual.

---

## 1. Konfigurasi Repositori

Ada 2 cara menentukan repositori GitHub Anda:

### Opsi A: Lewat WordPress Customizer (Direkomendasikan)
1. Buka dashboard WordPress: **Tampilan → Sesuaikan (Customizer)**.
2. Masuk ke panel **Pembaruan Theme (GitHub)**.
3. Masukkan nama repositori pada kolom **Repositori GitHub** dengan format:
   ```text
   username/nama-repositori
   ```
   *(Contoh: `hakastudio/hakanews`)*
4. Jika repositori bersifat privat, Anda dapat mengisi **GitHub Token** (Personal Access Token). Jika repositori publik, token ini dikosongkan saja.
5. Klik **Terbitkan**.

### Opsi B: Lewat `wp-config.php`
Anda juga bisa mendefinisikannya secara terpusat di `wp-config.php`:
```php
define( 'HAKA_GITHUB_REPO', 'hakastudio/hakanews' );
// Opsional untuk repo privat:
// define( 'HAKA_GITHUB_TOKEN', 'ghp_xxxxxxxxxxxxxxxxxxxx' );
```

---

## 2. Cara Merilis Versi Baru di GitHub

Setiap kali Anda ingin merilis pembaruan:

1. Di repositori GitHub Anda, buka menu **Releases → Draft a new release**.
2. **Tag version**: Gunakan format versi semantik (contoh: `v1.0.7` atau `1.0.7`).
   > *Catatan: Pastikan nomor versi ini lebih tinggi dari versi yang tertera di `style.css` pada instalasi WordPress.*
3. **Release title**: Beri judul rilis (contoh: `Versi 1.0.7 - Peningkatan Performa`).
4. **Description**: Tulis changelog dengan format Markdown standar, misalnya:
   ```markdown
   ## Perubahan & Peningkatan
   - Menambahkan opsi penyesuaian warna header
   - Memperbaiki bug font Google di mode offline
   - Optimasi kecepatan muat halaman
   ```
   *Daftar poin di atas otomatis ditampilkan pada kotak Dashboard WordPress dan popup detail update.*
5. **Paket Berkas Zip (Opsional tapi Direkomendasikan)**:
   - **Cara 1 (Direkomendasikan)**: Unggah file zip theme (misal: `hakanews.zip`) ke bagian *Attach binaries by dropping them here*. Theme otomatis mendeteksi file zip ini sebagai paket instalasi.
   - **Cara 2 (Otomatis)**: Jika Anda tidak mengunggah file zip khusus, updater otomatis menggunakan arsip zipball bawaan GitHub. Filter `upgrader_source_selection` di theme ini akan otomatis merapikan nama folder hasil ekstraksi menjadi nama slug theme yang benar.
6. Klik **Publish release**.

---

## 3. Pengecekan & Pembaruan di WordPress

- **Otomatis**: WordPress akan memeriksa rilis baru sesuai interval waktu (bawaan: 12 jam).
- **Manual**: Masuk ke **Tampilan → Import Demo Haka**, lalu klik tombol **Periksa pembaruan sekarang**.
- **Instalasi**: Saat versi baru terdeteksi, notifikasi pembaruan akan muncul di:
  - **Dashboard → Pembaruan**
  - **Tampilan → Tema**
  - Kotak informasi theme di **Dashboard WordPress**.
