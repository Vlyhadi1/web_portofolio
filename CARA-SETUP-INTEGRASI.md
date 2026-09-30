# Setup Integrasi Vlyhady

## Admin

1. Jalankan toko dan buka `http://127.0.0.1:8000/admin/`.
2. Buat akun admin pertama dari komputer lokal. Kata sandi minimal 12 karakter.
3. Di panel **Produk**, ganti produk contoh dengan produk asli. Isi berat paket dalam gram dan panjang, lebar, tinggi paket dalam cm. Tandai **Produk contoh** hanya untuk data uji.
4. Buka tab **Kasir** untuk penjualan langsung. Cari produk, atur jumlah, masukkan uang tunai yang diterima untuk menghitung kembalian, lalu simpan. QRIS/kartu di kasir perlu dikonfirmasi manual; alat terminal pembayaran belum terhubung.

## Kunci Layanan

Salin `api/config.local.example.php` menjadi `api/config.local.php`. Isi file lokal tersebut dengan:

- `midtrans_server_key`: Server Key dari akun Midtrans Sandbox.
- `midtrans_environment`: tetap `sandbox` untuk pengujian; ubah ke `production` setelah akun live siap.
- `biteship_api_key`: API key dari akun Biteship.
- `biteship_origin_postal_code`: kode pos titik kirim, saat ini `46196`.

Jangan kirim Server Key atau API key melalui chat. `api/config.local.php` sudah masuk `.gitignore` dan tidak dikirim ke browser. Alternatifnya, atur environment variable `VLYHADY_MIDTRANS_SERVER_KEY`, `VLYHADY_MIDTRANS_ENVIRONMENT`, `VLYHADY_BITESHIP_API_KEY`, `VLYHADY_BITESHIP_ORIGIN_POSTAL_CODE`, dan `VLYHADY_BITESHIP_COURIERS` di lingkungan PHP.

Checkout produk asli baru membuat pesanan setelah tarif Biteship tersedia. Nilai tarif diverifikasi ulang oleh server, lalu total dihitung dari MySQL dan dikirim ke Midtrans Snap. Pesanan hanya ditandai lunas melalui notifikasi Midtrans dengan signature yang valid.

## Sebelum Produksi

- Gunakan hosting dengan HTTPS dan akun MySQL khusus; jangan gunakan akun `root`.
- Atur URL notifikasi Midtrans menjadi `https://domain-anda/api/midtrans-notification.php` di dashboard Midtrans. Midtrans tidak dapat mengirim webhook ke `localhost`.
- Jalankan `composer install` saat menyiapkan server baru.
- Uji pembayaran di Sandbox dan tarif kurir dengan API key aktif sebelum beralih ke `production`.
- Produk awal tetap bertanda contoh sampai diganti melalui panel admin.