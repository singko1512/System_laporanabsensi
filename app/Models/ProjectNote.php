<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectNote extends Model
{
    use HasFactory;

    protected $table = 'md_project_notes';

    protected $fillable = [
        'project_id',
        'user_id',
        'tanggal',
        'kategori',
        'kategori_id',
        'judul',
        'catatan',
        'selesai_pada',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'selesai_pada' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kategoriMaster()
    {
        return $this->belongsTo(MasterData::class, 'kategori_id');
    }

    public function getIsSelesaiAttribute(): bool
    {
        return $this->selesai_pada !== null;
    }

    public function getKategoriAttribute($value): ?string
    {
        return $this->kategoriMaster?->kode ?? $value;
    }

    public function getKategoriLabelAttribute(): ?string
    {
        return $this->kategoriMaster?->nama ?? $this->kategori;
    }

    public function setKategoriAttribute(?string $value): void
    {
        if ($value) {
            $this->attributes['kategori_id'] = MasterData::idFor(MasterData::NOTE_KATEGORI, $value);
        }
    }
}
