# UAS Pengembangan Web – Debug REST API CI4

## Tugas:
- Perbaiki minimal 5 bug dari aplikasi
- Catat bug dan solusinya dalam tabel laporan

### Laporan Bug
| No | File                               | Baris | Bug                                          | Solusi                                                                |
|----|------------------------------------|-------|----------------------------------------------|-----------------------------------------------------------------------|
| 1  | app/AuthController.php             | 28    | Tidak ada validasi input register            | Tambahkan pengecekan input dan validasi format email                  |
| 2  | app/AuthController.php             | 35    | Password tidak di-hash                       | Gunakan `password_hash()` sebelum disimpan                            |
| 3  | app/AuthController.php             | 40    | Password dikembalikan di response            | Hilangkan field `password` dari response                              |
| 4  | app/AuthController.php             | 53    | Tidak ada validasi input login               | Tambahkan pengecekan email dan password tidak kosong                  |
| 5  | app/AuthController.php             | 61    | Password dibandingkan plaintext              | Gunakan `password_verify()` untuk verifikasi password                 |
| 6  | app/AuthController.php             | 72    | Endpoint `refresh()` belum diimplementasikan | Tambahkan proses decode dan generate ulang JWT token                  |
| 7  | .env                               | 10    | `JWT_SECRET_KEY` kosong                      | Tambahkan `JWT_SECRET_KEY=rahasia_super_aman_123`                     |
| 8  | app/Config/Filters.php             | 16    | Filter `jwt` belum didaftarkan               | Tambahkan `'jwt' => \App\Filters\JWTFilter::class` ke array `aliases` |
| 9  | app/Config/Routes.php              | 25    | Route `/api/auth/refresh` tanpa filter       | Tambahkan filter: `['filter' => 'jwt']` pada route `refresh`          |
| 10 | app/Controllers/UserController.php | 16    | Tidak menggunakan pagination                 | Gunakan `$model->paginate()` dan tambahkan data pager                 |
| 11 | app/Controllers/UserController.php | 23    | Tidak ada validasi ID user                   | Tambahkan `is_numeric($id)` untuk validasi ID                         |
| 12 | app/Controllers/UserController.php | 26    | Password dikembalikan di response            | Hapus field `password` sebelum mengirim response                      |
| 13 | app/Controllers/UserController.php | 34    | Tidak ada otorisasi update user              | Tambahkan pengecekan JWT dan cocokkan `user_id` dari token            |
| 14 | app/Controllers/UserController.php | 36    | Tidak ada validasi input saat update         | Validasi format email dan hash password jika diubah                   |
| 15 | app/Controllers/UserController.php | 45    | Tidak ada otorisasi delete user              | Tambahkan validasi JWT dan cocokkan ID user yang login                |



## Uji dengan Postman:
- POST /login
- POST /register
- GET /users (token diperlukan)