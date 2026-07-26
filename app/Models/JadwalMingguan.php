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
        'senin_status_id',
        'selasa',
        'selasa_status_id',
        'rabu',
        'rabu_status_id',
        'kamis',
        'kamis_status_id',
        'jumat',
        'jumat_status_id',
    ];

    public static function defaultSchedule(): array
    {
        return [
            'senin' => 'wfo',
            'selasa' => 'wfo',
            'rabu' => 'wfo',
            'kamis' => 'wfo',
            'jumat' => 'wfh',
        ];
    }

    public static function grupA(): array
    {
        return [
            'senin' => 'wfo',
            'selasa' => 'wfh',
            'rabu' => 'wfo',
            'kamis' => 'wfh',
            'jumat' => 'wfh',
        ];
    }

    public static function grupB(): array
    {
        return [
            'senin' => 'wfh',
            'selasa' => 'wfo',
            'rabu' => 'wfh',
            'kamis' => 'wfo',
            'jumat' => 'wfh',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getSeninAttribute($value): ?string
    {
        return $this->dayStatusCode('senin', $value);
    }

    public function getSelasaAttribute($value): ?string
    {
        return $this->dayStatusCode('selasa', $value);
    }

    public function getRabuAttribute($value): ?string
    {
        return $this->dayStatusCode('rabu', $value);
    }

    public function getKamisAttribute($value): ?string
    {
        return $this->dayStatusCode('kamis', $value);
    }

    public function getJumatAttribute($value): ?string
    {
        return $this->dayStatusCode('jumat', $value);
    }

    public function setSeninAttribute(?string $value): void
    {
        $this->setDayStatusId('senin', $value);
    }

    public function setSelasaAttribute(?string $value): void
    {
        $this->setDayStatusId('selasa', $value);
    }

    public function setRabuAttribute(?string $value): void
    {
        $this->setDayStatusId('rabu', $value);
    }

    public function setKamisAttribute(?string $value): void
    {
        $this->setDayStatusId('kamis', $value);
    }

    public function setJumatAttribute(?string $value): void
    {
        $this->setDayStatusId('jumat', $value);
    }

    private function dayStatusCode(string $day, ?string $fallback): ?string
    {
        return MasterData::kodeForId($this->attributes[$day . '_status_id'] ?? null) ?? $fallback;
    }

    private function setDayStatusId(string $day, ?string $value): void
    {
        if ($value) {
            $this->attributes[$day . '_status_id'] = MasterData::idFor(MasterData::JADWAL_STATUS, $value);
        }
    }
}
