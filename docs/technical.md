# 🔧 BulkMailer - Technical Documentation

Dokumentasi teknis untuk developer: arsitektur, setup, konfigurasi, dan pengembangan.

---

## 1. Tech Stack

| Layer                  | Teknologi                             |
| ---------------------- | ------------------------------------- |
| **Framework**          | Laravel 12                            |
| **Frontend**           | Livewire 4, Alpine.js, Tailwind CSS 4 |
| **Build Tool**         | Vite 7 + `@tailwindcss/vite`          |
| **Rich Text Editor**   | Quill.js (CDN)                        |
| **Spreadsheet Import** | `maatwebsite/excel`                   |
| **Queue**              | Laravel Queue (database driver)       |
| **Database**           | MySQL / SQLite                        |
| **SMTP**               | Gmail SMTP (dengan App Password)      |

---

## 2. Installation & Setup

### 2.1 Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+ & npm
- MySQL / SQLite
- Gmail account dengan 2FA & App Password

### 2.2 Installation Steps

```bash
# 1. Clone repository
git clone <repo-url> bulk-email
cd bulk-email

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Copy environment file
cp .env.example .env
php artisan key:generate

# 5. Configure database di .env
# DB_CONNECTION=mysql
# DB_DATABASE=bulk_email
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Configure Gmail SMTP di .env
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.gmail.com
# MAIL_PORT=587
# MAIL_USERNAME=your-email@gmail.com
# MAIL_PASSWORD=your-16-char-app-password
# MAIL_ENCRYPTION=tls
# MAIL_FROM_ADDRESS=your-email@gmail.com
# MAIL_FROM_NAME="BulkMailer"

# 7. Configure rate limits di .env
# DAILY_EMAIL_LIMIT=400
# HOURLY_EMAIL_LIMIT=50
# DELAY_BETWEEN_EMAILS=15
# MAX_RETRY_ATTEMPTS=3

# 8. Run migrations & seed
php artisan migrate
php artisan db:seed

# 9. Build frontend assets
npm run build        # production
# atau
npm run dev          # development (hot reload)

# 10. Start queue worker (WAJIB untuk mengirim email)
php artisan queue:work --tries=3 --delay=15

# 11. Start scheduler (untuk quota reset & log cleanup)
php artisan schedule:work
```

### 2.3 Laragon Setup

Jika menggunakan Laragon:

1. Project folder: `C:\laragon\www\bulk-email`
2. Otomatis tersedia di: `http://bulk-email.test`
3. Jalankan `npm run dev` di terminal terpisah untuk Vite.
4. Jalankan `php artisan queue:work` di terminal terpisah.

---

## 3. Architecture Overview

### 3.1 Directory Structure (Custom Files)

```
app/
├── Http/Middleware/
│   └── RoleMiddleware.php          # Role-based access control
├── Jobs/
│   ├── ProcessCampaignJob.php      # Batch processing orchestrator
│   ├── SendBulkEmailJob.php        # Individual email sender
│   ├── CleanupFailedEmailsJob.php  # Old log cleanup
│   └── ResetDailyQuotaJob.php      # Daily quota reset
├── Livewire/
│   ├── Auth/
│   │   ├── Login.php
│   │   └── Register.php
│   ├── Campaigns/
│   │   ├── CampaignList.php
│   │   ├── CampaignForm.php
│   │   └── CampaignDetail.php
│   ├── Templates/
│   │   ├── TemplateList.php
│   │   └── TemplateForm.php
│   ├── Dashboard.php
│   ├── EmailLogs.php
│   └── Settings.php
├── Models/
│   ├── Attachment.php
│   ├── Campaign.php
│   ├── DailyQuota.php
│   ├── EmailLog.php
│   ├── EmailTemplate.php
│   ├── Recipient.php
│   └── User.php
└── Services/
    ├── CampaignService.php         # Campaign lifecycle
    ├── EmailService.php            # Email sending logic
    ├── MergeTagParser.php          # {{tag}} replacement
    ├── QuotaService.php            # Rate limiting
    └── RecipientService.php        # CSV/Excel import & validation

config/
└── bulkemail.php                   # Centralized configuration

resources/views/
├── components/layouts/
│   ├── app.blade.php               # Main authenticated layout
│   └── guest.blade.php             # Auth pages layout
└── livewire/                       # Livewire component views
    ├── auth/
    ├── campaigns/
    ├── templates/
    ├── dashboard.blade.php
    ├── email-logs.blade.php
    └── settings.blade.php
```

### 3.2 Data Flow Diagram

```
User (Browser)
  │
  ▼
Livewire Component (CampaignForm)
  │
  ├── RecipientService.importFromFile()   ← CSV/Excel
  ├── CampaignService.startCampaign()
  │     │
  │     ▼
  │   ProcessCampaignJob (Queue)
  │     │
  │     ├── Fetch batch of pending recipients
  │     ├── Dispatch SendBulkEmailJob per recipient
  │     │     │
  │     │     ├── QuotaService.canSendEmail()
  │     │     ├── MergeTagParser.parse()
  │     │     ├── Mail::html() → Gmail SMTP
  │     │     ├── QuotaService.incrementCounter()
  │     │     └── EmailLog::create()
  │     │
  │     └── Re-dispatch self (next batch)
  │
  └── Dashboard / CampaignDetail (poll for updates)
```

---

## 4. Database Schema

### 4.1 Entity Relationship

```
users (1)──────(N) campaigns
users (1)──────(N) email_templates

campaigns (1)──(N) recipients
campaigns (1)──(N) email_logs
campaigns (1)──(N) attachments

recipients (1)─(N) email_logs

daily_quotas (standalone, 1 row per day)
```

### 4.2 Tables

#### `users`

| Column   | Type   | Note                                    |
| -------- | ------ | --------------------------------------- |
| id       | bigint | PK                                      |
| name     | string |                                         |
| email    | string | unique                                  |
| password | string |                                         |
| role     | string | `admin` \| `sender` (default: `sender`) |

#### `campaigns`

| Column       | Type       | Note                                                                |
| ------------ | ---------- | ------------------------------------------------------------------- |
| id           | bigint     | PK                                                                  |
| user_id      | FK → users |                                                                     |
| name         | string     |                                                                     |
| subject      | string     | Supports merge tags                                                 |
| body         | longtext   | HTML content                                                        |
| status       | string     | `draft`, `scheduled`, `processing`, `paused`, `completed`, `failed` |
| scheduled_at | timestamp  | nullable                                                            |
| started_at   | timestamp  | nullable                                                            |
| completed_at | timestamp  | nullable                                                            |
| settings     | json       | `{delay, batch_size}`                                               |
| deleted_at   | timestamp  | Soft deletes                                                        |

#### `recipients`

| Column        | Type           | Note                                  |
| ------------- | -------------- | ------------------------------------- |
| id            | bigint         | PK                                    |
| campaign_id   | FK → campaigns |                                       |
| email         | string         |                                       |
| name          | string         | nullable                              |
| custom_fields | json           | Additional merge tag data             |
| status        | string         | `pending`, `queued`, `sent`, `failed` |
| sent_at       | timestamp      | nullable                              |
| error_message | text           | nullable                              |
| retry_count   | int            | default 0                             |

#### `email_templates`

| Column  | Type       | Note |
| ------- | ---------- | ---- |
| id      | bigint     | PK   |
| user_id | FK → users |      |
| name    | string     |      |
| subject | string     |      |
| body    | longtext   | HTML |

#### `email_logs`

| Column        | Type            | Note             |
| ------------- | --------------- | ---------------- |
| id            | bigint          | PK               |
| campaign_id   | FK → campaigns  |                  |
| recipient_id  | FK → recipients |                  |
| status        | string          | `sent`, `failed` |
| sent_at       | timestamp       | nullable         |
| error_message | text            | nullable         |
| metadata      | json            | nullable         |

#### `daily_quotas`

| Column     | Type   | Note              |
| ---------- | ------ | ----------------- |
| id         | bigint | PK                |
| date       | date   | unique            |
| sent_count | int    | default 0         |
| limit      | int    | daily limit value |

#### `attachments`

| Column        | Type           | Note             |
| ------------- | -------------- | ---------------- |
| id            | bigint         | PK               |
| campaign_id   | FK → campaigns |                  |
| filename      | string         | Stored filename  |
| original_name | string         | User-facing name |
| path          | string         | Storage path     |
| mime_type     | string         |                  |
| size          | bigint         | Bytes            |

---

## 5. Service Layer

### 5.1 EmailService

**File:** `app/Services/EmailService.php`

Tanggung jawab utama:

- Mengirim email ke recipient dengan merge tag replacement
- Mengelola attachment
- Cek kuota sebelum kirim via `QuotaService`
- Log setiap pengiriman (sukses/gagal) ke `email_logs`
- Kirim test email (tidak masuk kuota)
- Test koneksi SMTP

**Key Methods:**

| Method                             | Fungsi                                 |
| ---------------------------------- | -------------------------------------- |
| `sendEmail(Recipient, Campaign)`   | Kirim email + merge tags + attachments |
| `sendTestEmail(to, subject, body)` | Kirim email percobaan                  |
| `testSmtpConnection()`             | Tes koneksi ke SMTP server             |

### 5.2 QuotaService

**File:** `app/Services/QuotaService.php`

Mengatur rate limiting berbasis Gmail SMTP limits:

- Daily limit: 400 email/hari
- Hourly limit: 50 email/jam
- Caching untuk performa

**Key Methods:**

| Method               | Fungsi                                                          |
| -------------------- | --------------------------------------------------------------- |
| `canSendEmail()`     | Cek daily + hourly limit                                        |
| `incrementCounter()` | Tambah counter setelah kirim                                    |
| `getDailyStats()`    | Return array statistik (sent_today, remaining, percentage, dll) |
| `isNearLimit()`      | Warning jika mendekati batas (80%+)                             |

### 5.3 CampaignService

**File:** `app/Services/CampaignService.php`

Lifecycle management campaign:

| Method                        | Fungsi                                               |
| ----------------------------- | ---------------------------------------------------- |
| `startCampaign(Campaign)`     | Set status processing, dispatch `ProcessCampaignJob` |
| `pauseCampaign(Campaign)`     | Pause campaign                                       |
| `resumeCampaign(Campaign)`    | Resume, re-dispatch job                              |
| `stopCampaign(Campaign)`      | Emergency stop                                       |
| `duplicateCampaign(Campaign)` | Clone campaign tanpa recipients                      |
| `getStatistics(Campaign)`     | Return stats array                                   |

### 5.4 RecipientService

**File:** `app/Services/RecipientService.php`

| Method                            | Fungsi                                       |
| --------------------------------- | -------------------------------------------- |
| `importFromFile(file, Campaign)`  | Parse CSV/Excel, validate, insert recipients |
| `getAvailableMergeTags(Campaign)` | Return merge tags dari custom_fields         |

Validasi saat import:

- Format email (filter_var)
- Deteksi email duplikat dalam campaign
- Block domain disposable (mailinator, guerrillamail, dll)

### 5.5 MergeTagParser

**File:** `app/Services/MergeTagParser.php`

Utility sederhana untuk replace `{{tag}}` patterns:

```php
$parser->parse("Halo {{nama}}", $recipient);
// Output: "Halo John Doe"
```

---

## 6. Queue System

### 6.1 Queue Driver

Menggunakan **database driver**. Tabel `jobs` dibuat oleh migration bawaan Laravel.

### 6.2 Job Flow

```
CampaignService::startCampaign()
  └── dispatch ProcessCampaignJob

ProcessCampaignJob::handle()
  ├── Check campaign status (skip if paused/failed)
  ├── Fetch batch of pending recipients (default: 50)
  ├── For each recipient:
  │     └── dispatch SendBulkEmailJob with delay
  ├── If more pending recipients exist:
  │     └── Re-dispatch self (ProcessCampaignJob)
  └── Else: mark campaign as completed

SendBulkEmailJob::handle()
  ├── QuotaService->canSendEmail()
  │     ├── false → set recipient back to pending, return
  │     └── true → continue
  ├── EmailService->sendEmail(recipient, campaign)
  └── On failure → increment retry_count, re-queue or mark failed
```

### 6.3 Running the Queue Worker

```bash
# Development
php artisan queue:work --tries=3 --delay=15

# Production (recommended with supervisor)
php artisan queue:work --tries=3 --delay=15 --sleep=3 --max-jobs=500
```

> **PENTING:** Queue worker HARUS berjalan agar email terkirim. Tanpa queue worker, email hanya masuk antrian tapi tidak diproses.

### 6.4 Scheduled Tasks

Didefinisikan di `routes/console.php`:

| Schedule            | Job                      | Fungsi                          |
| ------------------- | ------------------------ | ------------------------------- |
| Daily 00:00         | `ResetDailyQuotaJob`     | Buat/reset quota record harian  |
| Weekly Sunday 02:00 | `CleanupFailedEmailsJob` | Hapus log email gagal > 30 hari |

Jalankan scheduler:

```bash
php artisan schedule:work     # development
# atau setup cron di production:
# * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Configuration

### 7.1 `config/bulkemail.php`

File konfigurasi terpusat, membaca dari `.env`:

```php
return [
    'daily_limit'          => env('DAILY_EMAIL_LIMIT', 400),
    'hourly_limit'         => env('HOURLY_EMAIL_LIMIT', 50),
    'delay_between_emails' => env('DELAY_BETWEEN_EMAILS', 15),    // detik
    'max_retry_attempts'   => env('MAX_RETRY_ATTEMPTS', 3),
    'warning_threshold'    => 80,   // percentage
    'batch_size'           => 50,
    'disposable_domains'   => [...], // list domain email disposable
];
```

### 7.2 Environment Variables

| Variable               | Default        | Keterangan               |
| ---------------------- | -------------- | ------------------------ |
| `DAILY_EMAIL_LIMIT`    | 400            | Max email per hari       |
| `HOURLY_EMAIL_LIMIT`   | 50             | Max email per jam        |
| `DELAY_BETWEEN_EMAILS` | 15             | Jeda (detik) antar email |
| `MAX_RETRY_ATTEMPTS`   | 3              | Max percobaan ulang      |
| `MAIL_HOST`            | smtp.gmail.com | SMTP host                |
| `MAIL_PORT`            | 587            | SMTP port                |
| `MAIL_USERNAME`        | -              | Gmail address            |
| `MAIL_PASSWORD`        | -              | Gmail App Password       |
| `MAIL_ENCRYPTION`      | tls            | Encryption method        |
| `QUEUE_CONNECTION`     | database       | Queue driver             |

---

## 8. Authentication & Authorization

### 8.1 Authentication

Custom Livewire-based auth (tanpa Breeze/Jetstream):

- `App\Livewire\Auth\Login` — login with email/password
- `App\Livewire\Auth\Register` — register new account

Menggunakan built-in `Auth::attempt()` dan `Auth::login()`.

### 8.2 Role-Based Access

**Middleware:** `App\Http\Middleware\RoleMiddleware`

| Role     | Hak Akses                                              |
| -------- | ------------------------------------------------------ |
| `admin`  | Full access ke semua fitur                             |
| `sender` | Akses campaign, template, logs, settings milik sendiri |

Middleware belum di-apply ke route secara default. Untuk mengaktifkan:

```php
// routes/web.php
Route::middleware(['auth', 'role:admin'])->group(function () {
    // admin-only routes
});
```

---

## 9. Routing

**File:** `routes/web.php`

| Method | URI                               | Component                  | Name               |
| ------ | --------------------------------- | -------------------------- | ------------------ |
| GET    | `/login`                          | `Auth\Login`               | `login`            |
| GET    | `/register`                       | `Auth\Register`            | `register`         |
| POST   | `/logout`                         | Closure                    | `logout`           |
| GET    | `/`                               | `Dashboard`                | `dashboard`        |
| GET    | `/campaigns`                      | `Campaigns\CampaignList`   | `campaigns.index`  |
| GET    | `/campaigns/create`               | `Campaigns\CampaignForm`   | `campaigns.create` |
| GET    | `/campaigns/{campaign}/edit`      | `Campaigns\CampaignForm`   | `campaigns.edit`   |
| GET    | `/campaigns/{campaign}`           | `Campaigns\CampaignDetail` | `campaigns.show`   |
| GET    | `/templates`                      | `Templates\TemplateList`   | `templates.index`  |
| GET    | `/templates/create`               | `Templates\TemplateForm`   | `templates.create` |
| GET    | `/templates/{emailTemplate}/edit` | `Templates\TemplateForm`   | `templates.edit`   |
| GET    | `/logs`                           | `EmailLogs`                | `logs.index`       |
| GET    | `/settings`                       | `Settings`                 | `settings.index`   |

---

## 10. Gmail SMTP Best Practices

### 10.1 Rate Limits

Gmail menerapkan batasan pengiriman:

- **Free Gmail:** ~500 email/hari
- **Google Workspace:** ~2.000 email/hari
- **Rekomendasi aman:** 400/hari, 50/jam, delay 15 detik

### 10.2 Menghindari Spam/Block

1. **Delay antar email** — minimal 10-15 detik
2. **Personalisasi** — gunakan merge tags agar setiap email unik
3. **Hindari kata spam** — "GRATIS", "KLIK SEKARANG", caps lock berlebihan
4. **Sertakan unsubscribe** — tambahkan link berhenti berlangganan di body email
5. **Jangan melebihi limit** — Gmail akan temporary block jika melebihi limit
6. **Gunakan SPF/DKIM** — konfigurasi DNS domain jika mengirim dari domain sendiri

### 10.3 Troubleshooting

| Problem               | Solusi                                                 |
| --------------------- | ------------------------------------------------------ |
| Authentication failed | Pastikan App Password benar (bukan password akun)      |
| Connection timeout    | Periksa firewall, pastikan port 587 terbuka            |
| Too many emails       | Kurangi `DAILY_EMAIL_LIMIT` dan `HOURLY_EMAIL_LIMIT`   |
| Email masuk spam      | Gunakan personalisasi, hindari kata spam, cek SPF/DKIM |
| Queue tidak jalan     | Pastikan `php artisan queue:work` berjalan             |

---

## 11. Extending & Customization

### 11.1 Menambah Provider SMTP Lain

Ubah konfigurasi di `.env`:

```env
MAIL_HOST=smtp.office365.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

Sesuaikan rate limits (`DAILY_EMAIL_LIMIT`, `HOURLY_EMAIL_LIMIT`) dengan batasan provider yang digunakan.

### 11.2 Menambah Merge Tag Custom

Merge tags otomatis tersedia dari kolom CSV. Jika ingin menambah merge tag statis:

```php
// app/Services/MergeTagParser.php
public function parse(string $content, Recipient $recipient): string
{
    // Tambah tag custom
    $data['tanggal'] = now()->format('d M Y');
    $data['tahun'] = now()->year;
    // ... rest of parsing logic
}
```

### 11.3 Custom Email Validation

Untuk menambah domain yang diblokir:

```php
// config/bulkemail.php → disposable_domains
'disposable_domains' => [
    'mailinator.com',
    'domain-baru.com',  // tambah di sini
    // ...
],
```
