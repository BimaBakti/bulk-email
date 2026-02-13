<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@mail.com',
            'password' => bcrypt('123123123'),
            'role' => 'admin',
        ]);

        // Create sender user
        User::create([
            'name' => 'Sender',
            'email' => 'sender@example.com',
            'password' => bcrypt('password'),
            'role' => 'sender',
        ]);

        // Create sample templates
        EmailTemplate::create([
            'user_id' => $admin->id,
            'name' => 'Welcome Email',
            'subject' => 'Selamat Datang, {{nama}}!',
            'body' => '<h2>Halo {{nama}},</h2><p>Terima kasih telah bergabung dengan kami. Kami sangat senang Anda ada di sini!</p><p>Salam hangat,<br>Tim Kami</p>',
        ]);

        EmailTemplate::create([
            'user_id' => $admin->id,
            'name' => 'Newsletter Template',
            'subject' => 'Update Terbaru untuk {{nama}}',
            'body' => '<h2>Hai {{nama}},</h2><p>Berikut adalah update terbaru dari kami minggu ini.</p><ul><li>Fitur baru telah dirilis</li><li>Panduan penggunaan tersedia</li></ul><p>Terima kasih,<br>Tim Newsletter</p>',
        ]);

        EmailTemplate::create([
            'user_id' => $admin->id,
            'name' => 'Promotional Email',
            'subject' => 'Penawaran Spesial untuk {{nama}}!',
            'body' => '<h2>Hai {{nama}},</h2><p>Kami punya penawaran spesial yang tidak ingin Anda lewatkan!</p><p>Gunakan kode promo <strong>SPECIAL50</strong> untuk mendapatkan diskon 50%.</p><p>Penawaran berlaku hingga akhir bulan ini.</p><p>Salam,<br>Tim Marketing</p>',
        ]);
    }
}
