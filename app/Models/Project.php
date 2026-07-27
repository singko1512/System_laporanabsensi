<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $table = 'md_projects';

    protected $fillable = [
        'user_id',
        'nama',
        'kebutuhan',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'status_id',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function statusMaster()
    {
        return $this->belongsTo(MasterData::class, 'status_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'md_project_user', 'project_id', 'user_id')
            ->withTimestamps();
    }

    public function dayAssignments()
    {
        return $this->hasMany(ProjectDayAssignment::class, 'project_id');
    }

    public function notes()
    {
        return $this->hasMany(ProjectNote::class, 'project_id');
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
            $this->attributes['status_id'] = MasterData::idFor(MasterData::PROJECT_STATUS, $value);
        }
    }
}
