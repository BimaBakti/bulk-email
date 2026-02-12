# 📖 BulkMailer - User Guide

Panduan lengkap penggunaan aplikasi BulkMailer untuk mengirim email massal.

---

## 1. Memulai

### 1.1 Login & Register

- Buka aplikasi di browser, Anda akan diarahkan ke halaman **Login**.
- Masukkan email dan password, lalu klik **Sign In**.
- Jika belum punya akun, klik **Create account** untuk mendaftar.

**Akun Default (dari Seeder):**

| Email                   | Password   | Role   |
| ----------------------- | ---------- | ------ |
| `admin@bulkmailer.com`  | `password` | Admin  |
| `sender@bulkmailer.com` | `password` | Sender |

### 1.2 Dashboard

Setelah login, Anda akan melihat **Dashboard** dengan informasi:

- **Email Terkirim Hari Ini** — jumlah email yang sudah dikirim hari ini
- **Sisa Quota** — sisa kuota harian (default: 400/hari)
- **Rate Per Jam** — jumlah email terkirim dalam jam terakhir vs limit per jam
- **Total Campaigns** — total campaign yang pernah dibuat
- **Daily Quota Progress Bar** — visualisasi persentase penggunaan kuota
- **Active Campaigns** — campaign yang sedang berjalan dengan progress bar
- **Recent Campaigns** — tabel 10 campaign terakhir

> Dashboard di-refresh otomatis setiap 5 detik untuk memonitor campaign aktif.

---

## 2. Campaigns

### 2.1 Membuat Campaign Baru

1. Klik **New Campaign** di halaman Campaigns atau Dashboard.
2. Isi form:

| Field             | Keterangan                                                                               |
| ----------------- | ---------------------------------------------------------------------------------------- |
| **Campaign Name** | Nama untuk identifikasi campaign                                                         |
| **Subject Line**  | Subject email. Bisa pakai merge tags, contoh: `Halo {{nama}}`                            |
| **Email Body**    | Isi email menggunakan rich text editor (Quill). Klik tombol merge tag untuk menyisipkan. |

3. **Upload Recipients** — upload file CSV/Excel yang berisi daftar penerima.
4. (Opsional) **Upload Attachments** — lampirkan file jika diperlukan.
5. Pilih **Schedule**:
    - **Send Now** — langsung kirim
    - **Schedule** — pilih tanggal dan jam pengiriman
6. Klik **Start Campaign** atau **Schedule Campaign**.

### 2.2 Format File Recipient (CSV/Excel)

File CSV/Excel **wajib** memiliki kolom:

| Kolom              | Wajib | Keterangan                          |
| ------------------ | ----- | ----------------------------------- |
| `email`            | ✅    | Alamat email penerima               |
| `nama` atau `name` | ✅    | Nama penerima                       |
| _kolom lainnya_    | ❌    | Otomatis menjadi merge tag tambahan |

**Contoh CSV:**

```csv
email,nama,perusahaan,jabatan
john@example.com,John Doe,PT ABC,Manager
jane@example.com,Jane Smith,CV XYZ,Director
```

Dari contoh di atas, merge tags yang tersedia:

- `{{nama}}` → nama penerima
- `{{email}}` → email penerima
- `{{perusahaan}}` → nama perusahaan
- `{{jabatan}}` → jabatan

> **Catatan:** Email duplikat dan email dengan format tidak valid akan otomatis dilewati saat import. Domain email disposable (mailinator, guerrillamail, dll) juga ditolak.

### 2.3 Merge Tags (Personalisasi)

Merge tags memungkinkan personalisasi isi email per penerima. Gunakan format `{{nama_kolom}}` di Subject atau Body email.

**Contoh Subject:** `Halo {{nama}}, penawaran khusus untuk {{perusahaan}}`

**Contoh Body:**

```
Kepada Yth. {{nama}},

Kami dari {{perusahaan}} ingin menawarkan...
```

Setiap penerima akan menerima email dengan tag yang sudah diganti data sesuai kolom CSV.

### 2.4 Load Template

- Di sidebar form campaign, pilih template dari dropdown **Load Template**.
- Klik **Load** untuk mengisi Subject dan Body email dari template.
- Anda bisa mengedit setelah template di-load.

### 2.5 Preview & Test Email

- **Preview** — klik tombol Preview di sidebar untuk melihat tampilan email (sample data).
- **Send Test Email** — kirim email percobaan ke alamat tertentu tanpa mengurangi kuota harian.

### 2.6 Mengelola Campaign

Pada halaman **Campaign List**, tersedia aksi berdasarkan status campaign:

| Status               | Aksi Tersedia           |
| -------------------- | ----------------------- |
| **Draft**            | Edit, Duplicate, Delete |
| **Processing**       | Pause, Emergency Stop   |
| **Paused**           | Resume                  |
| **Completed/Failed** | Duplicate, Delete       |

### 2.7 Campaign Detail

Halaman detail campaign menampilkan:

- **Stats** — Total, Sent, Failed, Pending, Success Rate
- **Progress Bar** — persentase pengiriman
- **Recipient Table** — daftar penerima dengan status, waktu kirim, dan error message
- **Filter** — filter recipient berdasarkan status
- **Retry Failed** — kirim ulang email yang gagal
- **Export CSV** — download laporan campaign

> Halaman detail auto-refresh setiap 3 detik saat campaign aktif.

---

## 3. Email Templates

### 3.1 Membuat Template

1. Buka menu **Templates** → klik **New Template**.
2. Isi nama template, subject, dan body email.
3. Gunakan merge tags (`{{nama}}`, `{{email}}`) untuk personalisasi.
4. Klik **Save Template**.

### 3.2 Menggunakan Template

Template bisa digunakan saat membuat campaign baru:

1. Di form campaign, pilih template dari dropdown **Load Template**.
2. Klik **Load** — subject dan body dari template akan diisi otomatis.
3. Edit sesuai kebutuhan.

---

## 4. Email Logs

Halaman **Email Logs** menampilkan riwayat seluruh email yang dikirim/gagal:

- **Search** — cari berdasarkan email penerima atau nama campaign
- **Filter** — filter berdasarkan status (Sent/Failed)
- **Export CSV** — download seluruh log sebagai file CSV

---

## 5. Settings

### 5.1 SMTP Configuration

Menampilkan konfigurasi Gmail SMTP yang aktif (host, port, encryption, username). Konfigurasi diubah melalui file `.env`.

### 5.2 Test SMTP Connection

Klik **Test SMTP Connection** untuk verifikasi apakah koneksi ke server Gmail berfungsi.

### 5.3 Rate Limiting

Menampilkan konfigurasi rate limiting:

| Parameter    | Default | Keterangan                        |
| ------------ | ------- | --------------------------------- |
| Daily Limit  | 400     | Max email per hari                |
| Hourly Limit | 50      | Max email per jam                 |
| Delay        | 15s     | Jeda antar pengiriman email       |
| Max Retry    | 3x      | Percobaan ulang untuk email gagal |

### 5.4 Send Test Email

Kirim email test untuk memastikan SMTP sudah benar. Tidak dihitung dalam kuota harian.

### 5.5 Setup Gmail App Password

Panduan step-by-step untuk mendapatkan App Password dari Google:

1. Buka [Google Account Security](https://myaccount.google.com/security)
2. Aktifkan **2-Step Verification**
3. Kembali ke Security → **App passwords**
4. Pilih app: **Mail**, device: **Other** → ketik "BulkMailer"
5. Klik **Generate** → copy password 16 karakter
6. Paste ke `.env`:
    ```
    MAIL_USERNAME=email-anda@gmail.com
    MAIL_PASSWORD=password-16-karakter
    ```
7. Jalankan `php artisan config:clear`
8. Test koneksi di halaman Settings

---

## 6. Alur Kerja (Workflow)

```
┌─────────────┐     ┌──────────────┐     ┌──────────────┐
│  Buat        │────▶│  Upload      │────▶│  Preview &   │
│  Campaign    │     │  Recipients  │     │  Test Email  │
└─────────────┘     └──────────────┘     └──────┬───────┘
                                                 │
                    ┌──────────────┐              │
                    │  Monitor     │◀─────────────┤
                    │  Progress    │     ┌────────▼───────┐
                    └──────┬───────┘     │  Start / Sche- │
                           │             │  dule Campaign │
                    ┌──────▼───────┐     └────────────────┘
                    │  Export      │
                    │  Report      │
                    └──────────────┘
```

---

## 7. FAQ

**Q: Berapa batas email per hari?**
A: Default 400/hari (sesuai rekomendasi Gmail). Bisa diubah di `.env` (`DAILY_EMAIL_LIMIT`).

**Q: Apa yang terjadi jika quota habis?**
A: Campaign otomatis di-pause. Email yang belum terkirim berstatus "pending" dan akan dilanjutkan keesokan harinya saat quota reset.

**Q: Bagaimana cara retry email gagal?**
A: Buka Campaign Detail → klik **Retry Failed**. Email gagal akan di-queue ulang.

**Q: Format file apa yang didukung untuk import?**
A: CSV (.csv), Excel (.xlsx, .xls).

**Q: Apakah bisa mengirim attachment?**
A: Ya, upload file di section Attachments saat membuat campaign. Max 25MB per file.

**Q: Kapan quota harian di-reset?**
A: Otomatis setiap hari jam 00:00 via scheduled task.
