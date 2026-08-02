<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'md_absensi';

    protected $fillable = [
        'user_id',
        'task_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'status',
        'status_id',
        'status_masuk_id',
        'status_pulang_id',
        'foto',
        'foto_kamera',
        'foto_masuk',
        'foto_pulang',
        'lokasi_latitude',
        'lokasi_longitude',
        'lokasi_akurasi',
        'lokasi_diambil_pada',
        'lokasi_masuk_latitude',
        'lokasi_masuk_longitude',
        'lokasi_masuk_akurasi',
        'lokasi_masuk_diambil_pada',
        'lokasi_pulang_latitude',
        'lokasi_pulang_longitude',
        'lokasi_pulang_akurasi',
        'lokasi_pulang_diambil_pada',
        'laporan',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'lokasi_latitude' => 'decimal:7',
        'lokasi_longitude' => 'decimal:7',
        'lokasi_akurasi' => 'decimal:2',
        'lokasi_diambil_pada' => 'datetime',
        'lokasi_masuk_latitude' => 'decimal:7',
        'lokasi_masuk_longitude' => 'decimal:7',
        'lokasi_masuk_akurasi' => 'decimal:2',
        'lokasi_masuk_diambil_pada' => 'datetime',
        'lokasi_pulang_latitude' => 'decimal:7',
        'lokasi_pulang_longitude' => 'decimal:7',
        'lokasi_pulang_akurasi' => 'decimal:2',
        'lokasi_pulang_diambil_pada' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statusMaster()
    {
        return $this->belongsTo(MasterData::class, 'status_id');
    }

    public function statusMasukMaster()
    {
        return $this->belongsTo(MasterData::class, 'status_masuk_id');
    }

    public function statusPulangMaster()
    {
        return $this->belongsTo(MasterData::class, 'status_pulang_id');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function getStatusAttribute($value): ?string
    {
        return $this->statusMaster?->kode ?? $value;
    }

    public function getStatusLabelAttribute(): ?string
    {
        return $this->statusMaster?->nama ?? $this->status;
    }

    public function setStatusAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['status_id'] = MasterData::idFor(MasterData::ABSENSI_STATUS, $value);
        }
    }
}
