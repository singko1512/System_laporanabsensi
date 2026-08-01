<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'md_user';

    protected $fillable = [
        'nama',
        'username',
        'nip_atau_id',
        'email',
        'password',
        'bidang_id',
        'pembimbing_magang_id',
        'pembimbing_magang',
        'bidang_magang',
        'tanggal_mulai_magang',
        'tanggal_selesai_magang',
        'role',
        'status_akun',
        'sertifikat_file_path',
        'sertifikat_file_name',
        'sertifikat_file_mime',
        'sertifikat_diunggah_pada',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'tanggal_mulai_magang' => 'date',
        'tanggal_selesai_magang' => 'date',
        'sertifikat_diunggah_pada' => 'datetime',
        'password' => 'hashed',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    public function jadwalMingguan()
    {
        return $this->hasOne(JadwalMingguan::class, 'user_id');
    }

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }

    public function pembimbingMagang()
    {
        return $this->belongsTo(PembimbingMagang::class, 'pembimbing_magang_id');
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

    public function projectModules()
    {
        return $this->belongsToMany(ProjectModule::class, 'module_members', 'user_id', 'module_id')
            ->withTimestamps();
    }

    public function taskParticipants()
    {
        return $this->hasMany(ProjectTaskParticipant::class, 'user_id');
    }

    public function workSubmissions()
    {
        return $this->hasMany(WorkSubmission::class, 'user_id');
    }
}
