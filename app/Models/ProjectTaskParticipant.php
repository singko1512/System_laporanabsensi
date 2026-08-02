<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTaskParticipant extends Model
{
    use HasFactory;

    public const STATUS_JOINED = 'joined';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    protected $table = 'md_project_task_participants';

    protected $fillable = [
        'task_id',
        'user_id',
        'joined_at',
        'contribution_percentage',
        'status',
        'submitted_at',
        'approved_at',
        'approved_by',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'contribution_percentage' => 'decimal:2',
    ];

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function submissions()
    {
        return $this->hasMany(WorkSubmission::class, 'task_participant_id');
    }
}
