<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTask extends Model
{
    use HasFactory;

    public const MODULE_ASSIGNMENT_PREFIX = 'Pengerjaan Modul: ';

    protected $table = 'md_project_tasks';

    protected $fillable = [
        'project_id',
        'module_id',
        'user_id',
        'judul',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'join_window_minutes',
        'join_dibuka_pada',
        'join_ditutup_pada',
        'status',
        'catatan_revisi',
        'file_lampiran',
        'laporan_kerja',
        'tanggal_selesai_kerja',
        'urutan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'join_dibuka_pada' => 'datetime',
        'join_ditutup_pada' => 'datetime',
        'tanggal_selesai_kerja' => 'datetime',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function module()
    {
        return $this->belongsTo(ProjectModule::class, 'module_id');
    }

    public function participants()
    {
        return $this->hasMany(ProjectTaskParticipant::class, 'task_id');
    }

    public function submissions()
    {
        return $this->hasMany(WorkSubmission::class, 'task_id');
    }

    public function replies()
    {
        return $this->hasMany(ProjectNoteReply::class, 'task_id');
    }

    public function getIsJoinOpenAttribute(): bool
    {
        if ($this->status !== 'open') {
            return false;
        }

        if (! $this->join_dibuka_pada) {
            return true;
        }

        return now(config('app.timezone'))->lessThanOrEqualTo($this->joinDeadline());
    }

    public function joinDeadline(): CarbonInterface
    {
        return $this->join_ditutup_pada
            ?: $this->join_dibuka_pada->copy()->addMinutes($this->join_window_minutes ?: 5);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getProgressPercentageAttribute(): float
    {
        return $this->status === 'selesai' ? 100.0 : 0.0;
    }

    public function recalculateModuleProgress(): void
    {
        if ($this->module) {
            $this->module->recalculateProgress();
        }
    }

    public function isModuleAssignment(): bool
    {
        return str_starts_with((string) $this->judul, self::MODULE_ASSIGNMENT_PREFIX);
    }
}
