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

    public function modules()
    {
        return $this->hasMany(ProjectModule::class, 'project_id');
    }

    public function timelines()
    {
        return $this->hasMany(ProjectTimeline::class, 'project_id')->orderBy('urutan');
    }

    public function tasks()
    {
        return $this->hasMany(ProjectTask::class, 'project_id');
    }

    /**
     * Alias: actual_progress → progress_percentage
     * Digunakan di view absensi/index.blade.php
     */
    public function getActualProgressAttribute(): float
    {
        return $this->getProgressPercentageAttribute();
    }

    /**
     * Alias: planned_progress → planned_progress_percentage
     * Digunakan di view absensi/index.blade.php
     */
    public function getPlannedProgressAttribute(): float
    {
        return $this->getPlannedProgressPercentageAttribute();
    }

    public function getProgressPercentageAttribute(): float
    {
        $modules = $this->modules;

        if ($modules->isEmpty()) {
            return 0.0;
        }

        $totalWeight = $modules->sum('bobot');
        if ($totalWeight > 0) {
            $sum = 0.0;
            foreach ($modules as $module) {
                $sum += ((float) $module->progress) * ((float) $module->bobot) / 100;
            }
            return round($sum, 1);
        }

        return round($modules->sum('progress') / $modules->count(), 1);
    }

    public function getPlannedProgressPercentageAttribute(): float
    {
        $modules = $this->modules;

        if ($modules->isEmpty()) {
            return 0.0;
        }

        $today = now()->startOfDay();
        $totalWeight = $modules->sum('bobot');
        $sum = 0.0;

        foreach ($modules as $module) {
            $start = $module->tanggal_mulai ? $module->tanggal_mulai->startOfDay() : null;
            $end = $module->tanggal_selesai ? $module->tanggal_selesai->startOfDay() : null;

            if (! $start || ! $end) {
                $plannedModuleProgress = 0.0;
            } elseif ($today->lt($start)) {
                $plannedModuleProgress = 0.0;
            } elseif ($today->gt($end)) {
                $plannedModuleProgress = 100.0;
            } else {
                $totalDays = max(1, $start->diffInDays($end));
                $elapsedDays = $start->diffInDays($today);
                $plannedModuleProgress = ($elapsedDays / $totalDays) * 100;
            }

            $weight = $totalWeight > 0 ? (float) $module->bobot : (100 / $modules->count());
            $sum += $plannedModuleProgress * $weight / 100;
        }

        return round(min(100.0, max(0.0, $sum)), 1);
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
