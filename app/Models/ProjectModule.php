<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectModule extends Model
{
    use HasFactory;

    public const STATUS_BELUM_DIMULAI = 'belum_dimulai';

    public const STATUS_BERJALAN = 'berjalan';

    public const STATUS_SELESAI = 'selesai';

    protected $table = 'md_project_modules';

    protected $fillable = [
        'project_id',
        'timeline_id',
        'nama',
        'deskripsi',
        'tanggal_mulai',
        'tanggal_selesai',
        'progress',
        'status',
        'bobot',
        'urutan',
    ];

    protected $casts = [
        'progress' => 'float',
        'bobot' => 'float',
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

    public function timeline()
    {
        return $this->belongsTo(ProjectTimeline::class, 'timeline_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'module_members', 'module_id', 'user_id')
            ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'module_id');
    }

    public function task()
    {
        return $this->hasOne(ProjectTask::class, 'module_id');
    }

    public function recalculateProgress(): float
    {
        $totalTasks = $this->tasks()->count();
        if ($totalTasks === 0) {
            $progress = (float) $this->progress;
        } else {
            $completedTasks = $this->tasks()->where('status', 'selesai')->count();
            $progress = round(($completedTasks / $totalTasks) * 100, 1);
        }

        $status = self::STATUS_BELUM_DIMULAI;
        if ($progress >= 100) {
            $status = self::STATUS_SELESAI;
        } elseif ($progress > 0) {
            $status = self::STATUS_BERJALAN;
        }

        $this->update([
            'progress' => $progress,
            'status' => $status,
        ]);

        return $progress;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statusOptions()[$this->status] ?? ucfirst(str_replace('_', ' ', (string) $this->status));
    }

    public function getIsChosenAttribute(): bool
    {
        if ($this->relationLoaded('tasks')) {
            return $this->tasks->contains(function (ProjectTask $task): bool {
                return $task->isModuleAssignment() && $task->user_id;
            });
        }

        return $this->tasks()
            ->whereNotNull('user_id')
            ->where('judul', 'like', ProjectTask::MODULE_ASSIGNMENT_PREFIX.'%')
            ->exists();
    }
}
