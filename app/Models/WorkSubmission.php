<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkSubmission extends Model
{
    use HasFactory;

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_REVISION = 'revision';

    public const STATUS_APPROVED = 'approved';

    protected $table = 'md_work_submissions';

    protected $fillable = [
        'task_participant_id',
        'task_id',
        'user_id',
        'tanggal',
        'isi_laporan',
        'lampiran',
        'status',
        'reviewed_at',
        'reviewed_by',
        'review_note',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function participant()
    {
        return $this->belongsTo(ProjectTaskParticipant::class, 'task_participant_id');
    }

    public function task()
    {
        return $this->belongsTo(ProjectTask::class, 'task_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function replies()
    {
        return $this->hasMany(ProjectNoteReply::class, 'submission_id');
    }
}
