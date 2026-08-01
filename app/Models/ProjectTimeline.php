<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTimeline extends Model
{
    use HasFactory;

    public const STATUS_BELUM_DIMULAI = 'belum_dimulai';
    public const STATUS_BERJALAN = 'berjalan';
    public const STATUS_SELESAI = 'selesai';

    protected $table = 'md_project_timelines';

    protected $fillable = [
        'project_id',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'urutan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public static function statusOptions(): array
    {
        return [
            self::STATUS_BELUM_DIMULAI => 'Belum Dimulai',
            self::STATUS_BERJALAN => 'Berjalan',
            self::STATUS_SELESAI => 'Selesai',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function modules()
    {
        return $this->hasMany(ProjectModule::class, 'timeline_id')->orderBy('urutan');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }
}
