<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalMingguan extends Model
{
    use HasFactory;

    protected $table = 'md_jadwal_mingguan';

    protected $fillable = [
        'user_id',
        'senin',
        'selasa',
        'rabu',
        'kamis',
        'jumat',
    ];

    public static function defaultSchedule(): array
    {
        return array_merge(self::weekdayDefaults(), ['jumat' => 'wfh']);
    }

    public static function grupA(): array
    {
        return [
            'senin' => 'wfh',
            'selasa' => 'wfo',
            'rabu' => 'wfh',
            'kamis' => 'wfo',
            'jumat' => 'wfh',
        ];
    }

    public static function grupB(): array
    {
        return [
            'senin' => 'wfo',
            'selasa' => 'wfh',
            'rabu' => 'wfo',
            'kamis' => 'wfh',
            'jumat' => 'wfh',
        ];
    }

    public static function weekdayDefaults(): array
    {
        return [
            'senin' => 'wfo',
            'selasa' => 'wfo',
            'rabu' => 'wfo',
            'kamis' => 'wfo',
        ];
    }

    public static function withPermanentFriday(array $schedule): array
    {
        $schedule['jumat'] = 'wfh';

        return $schedule;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function forDay(string $dayKey): string
    {
        if ($dayKey === 'jumat') {
            return 'wfh';
        }

        return $this->{$dayKey} ?? 'wfo';
    }
}
