<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterData extends Model
{
    use HasFactory;

    public const ABSENSI_STATUS = 'absensi_status';

    public const JADWAL_STATUS = 'jadwal_status';

    public const PROJECT_STATUS = 'project_status';

    public const NOTE_KATEGORI = 'note_kategori';

    protected $table = 'md_master_data';

    protected $fillable = [
        'jenis',
        'kode',
        'nama',
        'warna',
        'urutan',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    private static array $idCache = [];

    private static array $itemCache = [];

    public static function defaults(): array
    {
        return [
            [self::ABSENSI_STATUS, 'hadir', 'Hadir', '#10b981', 1],
            [self::ABSENSI_STATUS, 'wfh', 'WFH', '#6366f1', 2],
            [self::ABSENSI_STATUS, 'sakit', 'Sakit', '#ef4444', 3],
            [self::ABSENSI_STATUS, 'izin', 'Izin', '#f59e0b', 4],
            [self::JADWAL_STATUS, 'wfo', 'WFO', '#10b981', 1],
            [self::JADWAL_STATUS, 'wfh', 'WFH', '#6366f1', 2],
            [self::PROJECT_STATUS, 'aktif', 'Aktif', '#10b981', 1],
            [self::PROJECT_STATUS, 'selesai', 'Selesai', '#64748b', 2],
            [self::NOTE_KATEGORI, 'rendah', 'Rendah', '#10b981', 1],
            [self::NOTE_KATEGORI, 'sedang', 'Sedang', '#f59e0b', 2],
            [self::NOTE_KATEGORI, 'tinggi', 'Tinggi', '#ef4444', 3],
        ];
    }

    public static function seedDefaults(): void
    {
        foreach (self::defaults() as [$jenis, $kode, $nama, $warna, $urutan]) {
            self::updateOrCreate(
                ['jenis' => $jenis, 'kode' => $kode],
                ['nama' => $nama, 'warna' => $warna, 'urutan' => $urutan, 'is_active' => true]
            );
        }

        self::$idCache = [];
        self::$itemCache = [];
    }

    public static function options(string $jenis)
    {
        return self::where('jenis', $jenis)
            ->where('is_active', true)
            ->orderBy('urutan')
            ->orderBy('nama')
            ->get();
    }

    public static function codes(string $jenis): array
    {
        return self::options($jenis)->pluck('kode')->all();
    }

    public static function idFor(string $jenis, string $kode): ?int
    {
        $key = $jenis.':'.$kode;

        if (! array_key_exists($key, self::$idCache)) {
            self::$idCache[$key] = self::where('jenis', $jenis)->where('kode', $kode)->value('id');
        }

        return self::$idCache[$key] ? (int) self::$idCache[$key] : null;
    }

    public static function itemForId(?int $id): ?self
    {
        if (! $id) {
            return null;
        }

        if (! array_key_exists($id, self::$itemCache)) {
            self::$itemCache[$id] = self::find($id);
        }

        return self::$itemCache[$id];
    }

    public static function kodeForId(?int $id): ?string
    {
        return self::itemForId($id)?->kode;
    }

    public static function namaForId(?int $id): ?string
    {
        return self::itemForId($id)?->nama;
    }
}
