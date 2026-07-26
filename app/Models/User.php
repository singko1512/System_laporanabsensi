<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'md_user';

    protected $fillable = [
        'nama',
        'nip_atau_id',
        'email',
        'pembimbing_magang',
        'bidang_magang',
        'tanggal_mulai_magang',
        'tanggal_selesai_magang',
    ];

    protected $casts = [
        'tanggal_mulai_magang' => 'date',
        'tanggal_selesai_magang' => 'date',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    public function jadwalMingguan()
    {
        return $this->hasOne(JadwalMingguan::class, 'user_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'user_id');
    }

    public function timelineProjects()
    {
        return $this->belongsToMany(Project::class, 'md_project_user', 'user_id', 'project_id')
            ->withTimestamps();
    }

    public function projectDayAssignments()
    {
        return $this->hasMany(ProjectDayAssignment::class, 'user_id');
    }
}
