<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyQuota extends Model
{
    protected $fillable = [
        'date',
        'sent_count',
        'limit',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public static function today(): self
    {
        return self::firstOrCreate(
            ['date' => now()->toDateString()],
            ['sent_count' => 0, 'limit' => config('bulkemail.daily_limit', 400)]
        );
    }

    public function remaining(): int
    {
        return max(0, $this->limit - $this->sent_count);
    }

    public function isExhausted(): bool
    {
        return $this->sent_count >= $this->limit;
    }

    public function percentage(): float
    {
        if ($this->limit === 0) return 100;
        return round(($this->sent_count / $this->limit) * 100, 1);
    }
}
