<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ActivityLog;
use App\Models\ProjectDayAssignment;
use App\Models\ProjectModule;
use App\Models\ProjectNote;
use App\Models\ProjectNoteReply;
use App\Models\ProjectTask;
use App\Models\ProjectTaskParticipant;
use App\Models\ProjectTimeline;
use App\Models\MasterData;
use App\Models\WorkSubmission;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ProjectTimelineController extends Controller
{
    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:md_user,id',
            'nama' => 'required|string|max:150',
            'kebutuhan' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'nama.required' => 'Nama project wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $userIds = collect($data['user_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        $project = DB::transaction(function () use ($data, $userIds) {
            $project = Project::create([
                'user_id' => $userIds->first() ?: Auth::id(),
                'nama' => $data['nama'],
                'kebutuhan' => $data['kebutuhan'] ?? null,
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'status' => 'aktif',
            ]);

            $project->members()->sync($userIds->all());
            $this->syncPrimaryTimeline($project);

            return $project;
        });

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])
            ->with('success_swal', 'Project timeline berhasil dibuat.')
            ->with('project_created_id', $project->id)
            ->with('project_created_name', $project->nama);
    }

    public function updateProject(Request $request, Project $project)
    {
        $data = $request->validate([
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:md_user,id',
            'nama' => 'required|string|max:150',
            'kebutuhan' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => [
                'required',
                Rule::exists('md_master_data', 'kode')
                    ->where(fn ($query) => $query->where('jenis', MasterData::PROJECT_STATUS)->where('is_active', true)),
            ],
        ]);

        $userIds = collect($data['user_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();

        $project->update([
            'user_id' => $userIds->first() ?: Auth::id(),
            'nama' => $data['nama'],
            'kebutuhan' => $data['kebutuhan'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'status' => $data['status'],
        ]);

        $project->members()->sync($userIds->all());
        $project->dayAssignments()
            ->whereNotIn('user_id', $userIds->all())
            ->delete();
        $project->dayAssignments()
            ->where(function ($query) use ($data) {
                $query->where('tanggal', '<', $data['tanggal_mulai'])
                    ->orWhere('tanggal', '>', $data['tanggal_selesai']);
            })
            ->delete();
        $this->syncPrimaryTimeline($project->refresh());

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Project timeline berhasil diperbarui.');
    }

    public function destroyProject(Project $project)
    {
        DB::transaction(function () use ($project): void {
            $this->deleteProjectChildren($project);
            $project->delete();
        });

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Project timeline berhasil dihapus.');
    }

    public function storeTimeline(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:md_projects,id',
            'nama' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => ['required', Rule::in(array_keys(ProjectTimeline::statusOptions()))],
        ], [
            'nama.required' => 'Nama timeline wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai timeline wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai timeline wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai timeline harus sama atau setelah tanggal mulai.',
        ]);

        ProjectTimeline::create([
            'project_id' => $data['project_id'],
            'nama' => $data['nama'],
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'status' => $data['status'],
            'urutan' => ProjectTimeline::where('project_id', $data['project_id'])->max('urutan') + 1,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Timeline project berhasil dibuat.');
    }

    public function updateTimeline(Request $request, ProjectTimeline $timeline)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'status' => ['required', Rule::in(array_keys(ProjectTimeline::statusOptions()))],
        ]);

        $timeline->update($data);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Timeline project berhasil diperbarui.');
    }

    public function destroyTimeline(ProjectTimeline $timeline)
    {
        $timeline->modules()->delete();
        $timeline->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Timeline project berhasil dihapus.');
    }

    public function storeModule(Request $request)
    {
        // Compatibility check: get project_id from timeline if missing
        if (!$request->has('project_id') && $request->has('timeline_id')) {
            $tl = ProjectTimeline::find($request->timeline_id);
            if ($tl) {
                $request->merge(['project_id' => $tl->project_id]);
            }
        }
        
        // Compatibility check: default bobot to 0 if missing
        if (!$request->has('bobot')) {
            $request->merge(['bobot' => 0]);
        }

        $data = $request->validate([
            'project_id' => 'required|exists:md_projects,id',
            'timeline_id' => 'nullable|exists:md_project_timelines,id',
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'bobot' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:md_user,id',
        ], [
            'nama.required' => 'Nama modul wajib diisi.',
            'bobot.required' => 'Bobot modul wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai modul wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai modul wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        $project = Project::findOrFail($data['project_id']);

        // Validasi rentang tanggal project
        if ($data['tanggal_mulai'] < $project->tanggal_mulai->format('Y-m-d') || $data['tanggal_selesai'] > $project->tanggal_selesai->format('Y-m-d')) {
            return back()->withErrors(['tanggal_mulai' => 'Tanggal modul harus berada di dalam rentang tanggal project (' . $project->tanggal_mulai->format('d-m-Y') . ' s/d ' . $project->tanggal_selesai->format('d-m-Y') . ')'])->withInput();
        }

        // Validasi total bobot tidak boleh melebihi 100%
        if ($data['bobot'] > 0) {
            $currentWeightSum = ProjectModule::where('project_id', $project->id)->sum('bobot');
            if (($currentWeightSum + $data['bobot']) > 100.0) {
                return back()->withErrors(['bobot' => 'Total bobot seluruh modul project ini tidak boleh melebihi 100% (saat ini total: ' . $currentWeightSum . '%).'])->withInput();
            }
        }

        // Default timeline
        $timelineId = $data['timeline_id'] ?? null;
        if (!$timelineId) {
            $timeline = ProjectTimeline::where('project_id', $project->id)->first();
            if (! $timeline) {
                $timeline = ProjectTimeline::create([
                    'project_id' => $project->id,
                    'nama' => 'Timeline Utama',
                    'status' => 'belum_dimulai',
                    'urutan' => 1,
                ]);
            }
            $timelineId = $timeline->id;
        }

        $module = ProjectModule::create([
            'project_id' => $project->id,
            'timeline_id' => $timelineId,
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'progress' => $data['progress'] ?? 0,
            'status' => $data['status'] ?? 'belum_dimulai',
            'bobot' => $data['bobot'],
            'urutan' => ProjectModule::where('project_id', $project->id)->max('urutan') + 1,
        ]);

        if (!empty($data['user_ids'])) {
            $module->members()->sync($data['user_ids']);
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'aktivitas' => 'Admin membuat modul baru: ' . $module->nama . ' (Bobot: ' . $module->bobot . '%, Jadwal: ' . $module->tanggal_mulai->format('d/m/Y') . ' - ' . $module->tanggal_selesai->format('d/m/Y') . ')',
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Modul pekerjaan berhasil dibuat.');
    }

    public function updateModule(Request $request, ProjectModule $module)
    {
        // Compatibility check: default bobot to current value if missing
        if (!$request->has('bobot')) {
            $request->merge(['bobot' => $module->bobot ?? 0]);
        }

        $data = $request->validate([
            'nama' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'bobot' => 'required|numeric|min:0|max:100',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'progress' => 'nullable|numeric|min:0|max:100',
            'status' => 'nullable|string',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:md_user,id',
        ], [
            'nama.required' => 'Nama modul wajib diisi.',
            'bobot.required' => 'Bobot modul wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai modul wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai modul wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        ]);

        $project = $module->project;

        // Validasi rentang tanggal project
        if ($data['tanggal_mulai'] < $project->tanggal_mulai->format('Y-m-d') || $data['tanggal_selesai'] > $project->tanggal_selesai->format('Y-m-d')) {
            return back()->withErrors(['tanggal_mulai' => 'Tanggal modul harus berada di dalam rentang tanggal project (' . $project->tanggal_mulai->format('d-m-Y') . ' s/d ' . $project->tanggal_selesai->format('d-m-Y') . ')'])->withInput();
        }

        // Validasi total bobot tidak boleh melebihi 100%
        $currentWeightSum = ProjectModule::where('project_id', $project->id)->where('id', '!=', $module->id)->sum('bobot');
        if (($currentWeightSum + $data['bobot']) > 100.0) {
            return back()->withErrors(['bobot' => 'Total bobot seluruh modul project ini tidak boleh melebihi 100% (saat ini total modul lain: ' . $currentWeightSum . '%).'])->withInput();
        }

        $oldName = $module->nama;
        $oldBobot = $module->bobot;
        $oldStart = $module->tanggal_mulai ? $module->tanggal_mulai->format('d/m/Y') : '-';
        $oldEnd = $module->tanggal_selesai ? $module->tanggal_selesai->format('d/m/Y') : '-';

        $module->update([
            'nama' => $data['nama'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'bobot' => $data['bobot'],
            'progress' => $data['progress'] ?? $module->progress,
            'status' => $data['status'] ?? $module->status,
        ]);

        if (isset($data['user_ids'])) {
            $module->members()->sync($data['user_ids']);
        }

        $module->recalculateProgress();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'aktivitas' => 'Admin memperbarui modul: ' . $oldName . ' -> ' . $module->nama . ' (Bobot: ' . $oldBobot . '% -> ' . $module->bobot . '%, Jadwal: [' . $oldStart . ' - ' . $oldEnd . '] -> [' . $module->tanggal_mulai->format('d/m/Y') . ' - ' . $module->tanggal_selesai->format('d/m/Y') . '])',
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Modul pekerjaan berhasil diperbarui.');
    }

    public function destroyModule(ProjectModule $module)
    {
        $project = $module->project;
        $moduleName = $module->nama;

        // Hapus task otomatis terkait terlebih dahulu
        ProjectTask::where('module_id', $module->id)->delete();
        $module->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $project->id,
            'aktivitas' => 'Admin menghapus modul: ' . $moduleName,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Modul pekerjaan berhasil dihapus.');
    }

    public function reorderModules(Request $request)
    {
        $data = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*.id' => 'required|exists:md_project_modules,id',
            'modules.*.timeline_id' => 'required|exists:md_project_timelines,id',
            'modules.*.urutan' => 'required|integer|min:1',
        ]);

        $timelines = ProjectTimeline::whereIn('id', collect($data['modules'])->pluck('timeline_id')->unique())
            ->get()
            ->keyBy('id');

        foreach ($data['modules'] as $moduleOrder) {
            $timeline = $timelines->get((int) $moduleOrder['timeline_id']);
            if ($timeline) {
                ProjectModule::where('id', $moduleOrder['id'])->update([
                    'timeline_id' => $timeline->id,
                    'urutan' => $moduleOrder['urutan'],
                ]);
            }
        }

        return response()->json(['message' => 'Urutan modul berhasil disimpan.']);
    }

    public function storeTask(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:md_projects,id',
            'module_id' => 'required|exists:md_project_modules,id',
            'judul' => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'user_id' => 'nullable|exists:md_user,id',
        ], [
            'judul.required' => 'Judul task wajib diisi.',
            'module_id.required' => 'Wajib memilih modul.',
            'tanggal_selesai.after_or_equal' => 'Deadline task harus sama atau setelah tanggal mulai.',
        ]);

        $module = ProjectModule::findOrFail($data['module_id']);
        if ((int) $module->project_id !== (int) $data['project_id']) {
            return redirect()->route('admin.dashboard', ['tab' => 'timeline'])
                ->with('error_swal', 'Modul tidak sesuai dengan project.');
        }

        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        if ($userId) {
            $project = Project::findOrFail($data['project_id']);
            if (! $project->members()->where('md_user.id', $userId)->exists()) {
                $project->members()->attach($userId);
            }
        }

        $task = ProjectTask::create([
            'project_id' => $data['project_id'],
            'module_id' => $data['module_id'],
            'user_id' => $userId,
            'judul' => $data['judul'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'] ?? $module->tanggal_mulai,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? $module->tanggal_selesai,
            'status' => $userId ? 'sedang_dikerjakan' : 'belum_dikerjakan',
            'join_window_minutes' => 999999, // default unlimited
            'urutan' => ProjectTask::where('project_id', $data['project_id'])->max('urutan') + 1,
        ]);

        if ($userId) {
            ProjectTaskParticipant::updateOrCreate(
                ['task_id' => $task->id, 'user_id' => $userId],
                [
                    'status' => 'joined',
                    'joined_at' => now(),
                    'contribution_percentage' => 100.00,
                ]
            );
        }

        $module->recalculateProgress();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $module->project_id,
            'aktivitas' => 'Admin membuat task baru: ' . $task->judul . ' pada modul ' . $module->nama . ($userId ? ' dan menugaskannya ke PIC.' : ''),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Task pekerjaan berhasil dibuat.');
    }

    public function assignTaskPIC(Request $request, ProjectTask $task)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:md_user,id',
        ]);

        if (! $task->project->members()->where('md_user.id', $data['user_id'])->exists()) {
            $task->project->members()->attach($data['user_id']);
        }

        $task->update([
            'user_id' => $data['user_id'],
            'status' => 'sedang_dikerjakan',
        ]);

        ProjectTaskParticipant::updateOrCreate(
            ['task_id' => $task->id, 'user_id' => $data['user_id']],
            [
                'status' => 'joined',
                'joined_at' => now(),
                'contribution_percentage' => 100.00,
            ]
        );

        $task->recalculateModuleProgress();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'aktivitas' => 'Admin menugaskan task: ' . $task->judul . ' ke ' . $task->user->nama,
        ]);

        return back()->with('success_swal', 'Berhasil menugaskan PIC ke task pekerjaan.');
    }

    public function unassignTaskPIC(ProjectTask $task)
    {
        $oldPicName = $task->user ? $task->user->nama : 'PIC';
        
        $task->update([
            'user_id' => null,
            'status' => 'belum_dikerjakan',
        ]);

        ProjectTaskParticipant::where('task_id', $task->id)->delete();

        $task->recalculateModuleProgress();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'aktivitas' => 'Admin menghapus penugasan PIC (' . $oldPicName . ') dari task: ' . $task->judul,
        ]);

        return back()->with('success_swal', 'Berhasil melepas PIC dari task.');
    }

    public function destroyTask(ProjectTask $task)
    {
        $judul = $task->judul;
        $projectId = $task->project_id;
        $module = $task->module;

        DB::transaction(function () use ($task): void {
            DB::table('md_project_note_replies')->where('task_id', $task->id)->delete();
            DB::table('md_work_submissions')->where('task_id', $task->id)->delete();
            DB::table('md_project_task_participants')->where('task_id', $task->id)->delete();
            DB::table('md_absensi')->where('task_id', $task->id)->update(['task_id' => null]);
            $task->delete();
        });

        if ($module) {
            $module->recalculateProgress();
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $projectId,
            'aktivitas' => 'Admin menghapus task: ' . $judul,
        ]);

        return back()->with('success_swal', 'Task berhasil dihapus.');
    }

    public function lockTask(ProjectTask $task)
    {
        $task->update([
            'status' => 'locked',
            'join_ditutup_pada' => now(config('app.timezone')),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Join task berhasil ditutup.');
    }

    public function reopenTask(ProjectTask $task)
    {
        $now = now(config('app.timezone'));
        $task->update([
            'status' => 'open',
            'join_dibuka_pada' => $now,
            'join_ditutup_pada' => $now->copy()->addMinutes($task->join_window_minutes ?: 5),
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Join task dibuka ulang.');
    }

    public function submitTask(Request $request, ProjectTaskParticipant $participant)
    {
        if ((int) $participant->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'isi_laporan' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,zip|max:10240',
        ], [
            'isi_laporan.required' => 'Laporan pekerjaan wajib diisi.',
            'lampiran.mimes' => 'Lampiran harus PDF, gambar, WEBP, atau ZIP.',
            'lampiran.max' => 'Lampiran maksimal 10 MB.',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $this->storeTimelineFile($request->file('lampiran'), Auth::id());
        }

        $submission = WorkSubmission::create([
            'task_participant_id' => $participant->id,
            'task_id' => $participant->task_id,
            'user_id' => Auth::id(),
            'tanggal' => now(config('app.timezone'))->toDateString(),
            'isi_laporan' => $data['isi_laporan'],
            'lampiran' => $lampiranPath,
            'status' => WorkSubmission::STATUS_SUBMITTED,
        ]);

        $participant->update([
            'status' => ProjectTaskParticipant::STATUS_SUBMITTED,
            'submitted_at' => now(config('app.timezone')),
            'approved_at' => null,
            'approved_by' => null,
        ]);

        ProjectNoteReply::create([
            'submission_id' => $submission->id,
            'task_id' => $participant->task_id,
            'user_id' => Auth::id(),
            'tipe' => 'submission',
            'isi' => $data['isi_laporan'],
            'lampiran' => $lampiranPath,
        ]);

        return redirect()->route('absensi.index', ['tab' => 'timeline'])->with('success_swal', 'Laporan pekerjaan berhasil dikirim untuk review admin.');
    }

    public function approveSubmission(WorkSubmission $submission)
    {
        $submission->update([
            'status' => WorkSubmission::STATUS_APPROVED,
            'reviewed_at' => now(config('app.timezone')),
            'reviewed_by' => Auth::id(),
            'review_note' => null,
        ]);

        $submission->participant->update([
            'status' => ProjectTaskParticipant::STATUS_APPROVED,
            'approved_at' => now(config('app.timezone')),
            'approved_by' => Auth::id(),
        ]);

        ProjectNoteReply::create([
            'submission_id' => $submission->id,
            'task_id' => $submission->task_id,
            'user_id' => Auth::id(),
            'tipe' => 'approve',
            'isi' => 'Submission disetujui admin.',
        ]);

        // Rekalkulasi progress modul otomatis
        if ($submission->task) {
            $submission->task->recalculateModuleProgress();
        }

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Submission berhasil di-approve.');
    }

    public function revisionSubmission(Request $request, WorkSubmission $submission)
    {
        $data = $request->validate([
            'review_note' => 'required|string',
        ], [
            'review_note.required' => 'Catatan revisi wajib diisi.',
        ]);

        $submission->update([
            'status' => WorkSubmission::STATUS_REVISION,
            'reviewed_at' => now(config('app.timezone')),
            'reviewed_by' => Auth::id(),
            'review_note' => $data['review_note'],
        ]);

        $submission->participant->update([
            'status' => ProjectTaskParticipant::STATUS_REVISION,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        ProjectNoteReply::create([
            'submission_id' => $submission->id,
            'task_id' => $submission->task_id,
            'user_id' => Auth::id(),
            'tipe' => 'revision',
            'isi' => $data['review_note'],
        ]);

        // Rekalkulasi progress modul otomatis
        if ($submission->task) {
            $submission->task->recalculateModuleProgress();
        }

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Submission dikembalikan untuk revisi.');
    }

    public function replySubmission(Request $request, WorkSubmission $submission)
    {
        if ((int) $submission->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'isi' => 'required|string',
        ], [
            'isi.required' => 'Balasan revisi wajib diisi.',
        ]);

        ProjectNoteReply::create([
            'submission_id' => $submission->id,
            'task_id' => $submission->task_id,
            'user_id' => Auth::id(),
            'tipe' => 'reply',
            'isi' => $data['isi'],
        ]);

        return redirect()->route('absensi.index', ['tab' => 'timeline'])->with('success_swal', 'Balasan revisi berhasil dikirim.');
    }

    public function storeNote(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:md_projects,id',
            'user_id' => 'nullable|exists:md_user,id',
            'tanggal' => 'required|date',
            'kategori' => [
                'required',
                Rule::exists('md_master_data', 'kode')
                    ->where(fn ($query) => $query->where('jenis', MasterData::NOTE_KATEGORI)->where('is_active', true)),
            ],
            'judul' => 'required|string|max:150',
            'catatan' => 'nullable|string',
        ], [
            'judul.required' => 'Judul note wajib diisi.',
            'kategori.required' => 'Kategori note wajib dipilih.',
        ]);

        $project = Project::with('members')->findOrFail($data['project_id']);
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        if ($tanggal < $project->tanggal_mulai->toDateString() || $tanggal > $project->tanggal_selesai->toDateString()) {
            return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('error_swal', 'Tanggal note harus berada di rentang project.');
        }

        $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;

        if ($userId && ! $project->members->contains('id', $userId)) {
            return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('error_swal', 'Peserta magang note harus termasuk anggota project.');
        }

        ProjectNote::create([
            'project_id' => $project->id,
            'user_id' => $userId,
            'tanggal' => $tanggal,
            'kategori' => $data['kategori'],
            'judul' => $data['judul'],
            'catatan' => $data['catatan'] ?? null,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Note timeline berhasil ditambahkan.');
    }

    public function assignDay(Request $request)
    {
        $data = $request->validate([
            'project_id' => 'required|exists:md_projects,id',
            'user_id' => 'required|exists:md_user,id',
            'tanggal' => 'required|date',
        ]);

        $project = Project::with('members')->findOrFail($data['project_id']);
        $tanggal = Carbon::parse($data['tanggal'])->toDateString();

        if ($tanggal < $project->tanggal_mulai->toDateString() || $tanggal > $project->tanggal_selesai->toDateString()) {
            return response()->json(['message' => 'Tanggal assignment harus berada di rentang project.'], 422);
        }

        $userId = (int) $data['user_id'];

        if (! $project->members->contains('id', $userId)) {
            return response()->json(['message' => 'Peserta magang ini belum termasuk daftar anggota project.'], 422);
        }

        $assignment = ProjectDayAssignment::updateOrCreate([
            'project_id' => $project->id,
            'user_id' => $userId,
            'tanggal' => $tanggal,
        ]);

        $assignment->load('user');

        return response()->json([
            'id' => $assignment->id,
            'project_id' => $project->id,
            'user_id' => $assignment->user_id,
            'tanggal' => $assignment->tanggal->toDateString(),
            'user_name' => $assignment->user->nama,
        ]);
    }

    public function removeDayAssignment(ProjectDayAssignment $assignment)
    {
        $assignment->delete();

        return response()->json(['message' => 'Assignment peserta magang berhasil dihapus.']);
    }

    public function completeNote(Request $request, ProjectNote $note)
    {
        $request->validate([
            'user_id' => 'nullable|exists:md_user,id',
            'redirect_tab' => 'nullable|in:timeline',
        ]);

        $isAdmin = Auth::check() && Auth::user()->role === 'superadmin';

        if (! $isAdmin) {
            if (! $request->filled('user_id')) {
                abort(403);
            }

            $requestUserId = (int) $request->input('user_id');
            $project = $note->project()->with('members')->firstOrFail();
            $isProjectMember = $note->user_id
                ? (int) $note->user_id === $requestUserId
                : ((int) $project->user_id === $requestUserId || $project->members->contains('id', $requestUserId));

            if (! $isProjectMember) {
                abort(403);
            }

            $note->update([
                'user_selesai_pada' => now(config('app.timezone')),
            ]);
        } else {
            $note->update([
                'selesai_pada' => now(config('app.timezone')),
            ]);
        }

        if ($request->input('redirect_tab') === 'timeline') {
            return redirect()->route('absensi.index', [
                'tab' => 'timeline',
                'user_id' => $request->input('user_id', $note->project->user_id),
            ])->with('success_swal', 'Note berhasil ditandai selesai.');
        }

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Note berhasil ditandai selesai.');
    }

    private function storeTimelineFile($file, int $userId): string
    {
        $relativeDir = 'uploads/timeline';
        $uploadDir = public_path($relativeDir);

        File::ensureDirectoryExists($uploadDir, 0755, true);

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'dat');
        $filename = now(config('app.timezone'))->format('Ymd_His')
            . '_' . $userId
            . '_' . Str::random(10)
            . '.' . $extension;

        $file->move($uploadDir, $filename);

        return $relativeDir . '/' . $filename;
    }

    private function validatedModuleMemberIds(Project $project, array $userIds)
    {
        $projectMemberIds = $project->members->pluck('id')->map(fn ($id) => (int) $id);
        $selectedIds = collect($userIds)->map(fn ($id) => (int) $id)->unique()->values();

        if ($selectedIds->diff($projectMemberIds)->isNotEmpty()) {
            throw ValidationException::withMessages([
                'user_ids' => 'PIC modul harus termasuk anggota project.',
            ]);
        }

        return $selectedIds;
    }

    private function syncPrimaryTimeline(Project $project): ProjectTimeline
    {
        $timeline = ProjectTimeline::where('project_id', $project->id)
            ->orderBy('urutan')
            ->orderBy('id')
            ->first();

        if (! $timeline) {
            return ProjectTimeline::create([
                'project_id' => $project->id,
                'nama' => 'Timeline Project',
                'tanggal_mulai' => $project->tanggal_mulai,
                'tanggal_selesai' => $project->tanggal_selesai,
                'status' => 'berjalan',
                'urutan' => 1,
            ]);
        }

        $timeline->update([
            'nama' => in_array($timeline->nama, ['Timeline Utama', 'Timeline Project'], true) ? 'Timeline Project' : $timeline->nama,
            'tanggal_mulai' => $project->tanggal_mulai,
            'tanggal_selesai' => $project->tanggal_selesai,
            'status' => $timeline->status ?: 'berjalan',
            'urutan' => $timeline->urutan ?: 1,
        ]);

        return $timeline->load('project.members');
    }

    private function deleteProjectChildren(Project $project): void
    {
        $taskIds = ProjectTask::where('project_id', $project->id)->pluck('id');

        if ($taskIds->isNotEmpty()) {
            $submissionIds = DB::table('md_work_submissions')
                ->whereIn('task_id', $taskIds)
                ->pluck('id');

            DB::table('md_project_note_replies')
                ->whereIn('task_id', $taskIds)
                ->when($submissionIds->isNotEmpty(), fn ($query) => $query->orWhereIn('submission_id', $submissionIds))
                ->delete();

            DB::table('md_work_submissions')
                ->whereIn('task_id', $taskIds)
                ->delete();

            DB::table('md_project_task_participants')
                ->whereIn('task_id', $taskIds)
                ->delete();

            DB::table('md_absensi')
                ->whereIn('task_id', $taskIds)
                ->update(['task_id' => null]);

            ProjectTask::whereIn('id', $taskIds)->delete();
        }

        $moduleIds = ProjectModule::where('project_id', $project->id)->pluck('id');

        if ($moduleIds->isNotEmpty()) {
            DB::table('module_members')->whereIn('module_id', $moduleIds)->delete();
            ProjectModule::whereIn('id', $moduleIds)->delete();
        }

        ProjectTimeline::where('project_id', $project->id)->delete();
        ProjectDayAssignment::where('project_id', $project->id)->delete();
        ProjectNote::where('project_id', $project->id)->delete();
        DB::table('md_project_user')->where('project_id', $project->id)->delete();
    }

    public function selfAssignTask(ProjectTask $task)
    {
        if ($task->user_id) {
            return back()->with('error_swal', 'Task ini sudah diambil oleh peserta lain.');
        }

        // Enforce Focused Workflow: check if user already has an active task
        $hasActiveTask = ProjectTask::where('user_id', Auth::id())
            ->whereIn('status', ['sedang_dikerjakan', 'review', 'revision'])
            ->exists();
        if ($hasActiveTask) {
            return back()->with('error_swal', 'Anda hanya dapat mengambil satu tugas dalam satu waktu. Selesaikan tugas aktif Anda terlebih dahulu.');
        }

        // Pastikan user terdaftar di project ini (atau project has no members)
        $project = $task->project;
        $userId = Auth::id();
        if ($project->members()->exists() && !$project->members()->where('md_user.id', $userId)->exists()) {
            return back()->with('error_swal', 'Anda bukan anggota dari project ini.');
        }

        // Auto join project if they are not a member yet
        if (!$project->members()->where('md_user.id', $userId)->exists()) {
            $project->members()->attach($userId);
        }

        $task->update([
            'user_id' => Auth::id(),
            'status' => 'sedang_dikerjakan',
        ]);

        // Compatibility Layer
        ProjectTaskParticipant::updateOrCreate(
            ['task_id' => $task->id, 'user_id' => Auth::id()],
            [
                'status' => 'joined',
                'joined_at' => now(),
                'contribution_percentage' => 100.00,
            ]
        );

        $task->recalculateModuleProgress();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'aktivitas' => Auth::user()->nama . ' mengambil task: ' . $task->judul,
        ]);

        return back()->with('success_swal', 'Berhasil mengambil task pekerjaan.');
    }

    public function selfAssignModule(ProjectModule $module)
    {
        // Enforce Focused Workflow: check if user already has an active task
        $hasActiveTask = ProjectTask::where('user_id', Auth::id())
            ->whereIn('status', ['sedang_dikerjakan', 'review', 'revision'])
            ->exists();
        if ($hasActiveTask) {
            return back()->with('error_swal', 'Anda hanya dapat mengambil satu tugas/modul dalam satu waktu. Selesaikan tugas aktif Anda terlebih dahulu.');
        }

        // Pastikan user terdaftar di project ini (atau project has no members)
        $project = $module->project;
        $userId = Auth::id();
        if ($project->members()->exists() && !$project->members()->where('md_user.id', $userId)->exists()) {
            return back()->with('error_swal', 'Anda bukan anggota dari project ini.');
        }

        // Auto join project if they are not a member yet
        if (!$project->members()->where('md_user.id', $userId)->exists()) {
            $project->members()->attach($userId);
        }

        // Create the task for this module
        $task = ProjectTask::create([
            'project_id' => $project->id,
            'module_id' => $module->id,
            'user_id' => $userId,
            'judul' => 'Pengerjaan Modul: ' . $module->nama,
            'deskripsi' => 'Pengerjaan seluruh modul ' . $module->nama,
            'tanggal_mulai' => $module->tanggal_mulai,
            'tanggal_selesai' => $module->tanggal_selesai,
            'status' => 'sedang_dikerjakan',
            'join_window_minutes' => 999999,
            'urutan' => ProjectTask::where('project_id', $project->id)->max('urutan') + 1,
        ]);

        ProjectTaskParticipant::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'status' => 'joined',
            'joined_at' => now(),
            'contribution_percentage' => 100.00,
        ]);

        $task->recalculateModuleProgress();

        ActivityLog::create([
            'user_id' => $userId,
            'project_id' => $project->id,
            'aktivitas' => Auth::user()->nama . ' mengambil modul: ' . $module->nama,
        ]);

        return back()->with('success_swal', 'Berhasil mengambil modul pekerjaan.');
    }

    public function submitWorkTask(Request $request, ProjectTask $task)
    {
        if ((int) $task->user_id !== (int) Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'laporan_kerja' => 'required|string',
            'lampiran' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,zip|max:10240',
        ], [
            'laporan_kerja.required' => 'Laporan pekerjaan wajib diisi.',
            'lampiran.mimes' => 'Format lampiran harus berupa PDF, JPG, JPEG, PNG, WEBP, atau ZIP.',
            'lampiran.max' => 'Ukuran berkas maksimal 10 MB.',
        ]);

        $lampiranPath = null;
        if ($request->hasFile('lampiran')) {
            $lampiranPath = $this->storeTimelineFile($request->file('lampiran'), Auth::id());
        }

        $task->update([
            'status' => 'review',
            'laporan_kerja' => $data['laporan_kerja'],
            'file_lampiran' => $lampiranPath ?: $task->file_lampiran,
            'tanggal_selesai_kerja' => now(),
        ]);

        // Compatibility Layer
        $participant = ProjectTaskParticipant::updateOrCreate(
            ['task_id' => $task->id, 'user_id' => Auth::id()],
            [
                'status' => 'submitted',
                'submitted_at' => now(),
            ]
        );

        $submission = WorkSubmission::create([
            'task_participant_id' => $participant->id,
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'tanggal' => now()->toDateString(),
            'isi_laporan' => $data['laporan_kerja'],
            'lampiran' => $lampiranPath ?: $task->file_lampiran,
            'status' => 'submitted',
        ]);

        ProjectNoteReply::create([
            'submission_id' => $submission->id,
            'task_id' => $task->id,
            'user_id' => Auth::id(),
            'tipe' => 'submission',
            'isi' => $data['laporan_kerja'],
            'lampiran' => $lampiranPath ?: $task->file_lampiran,
        ]);

        $task->recalculateModuleProgress();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'aktivitas' => Auth::user()->nama . ' menyerahkan pekerjaan task: ' . $task->judul . ' untuk direview',
        ]);

        return redirect()->route('absensi.index', ['tab' => 'timeline'])->with('success_swal', 'Laporan pekerjaan berhasil dikirim.');
    }

    public function approveTask(ProjectTask $task)
    {
        $task->update([
            'status' => 'selesai',
            'catatan_revisi' => null,
        ]);

        // Auto-complete other tasks in the module if this is a "Module-Level" task
        if ($task->module_id && str_starts_with($task->judul, 'Pengerjaan Modul: ')) {
            $otherTasks = ProjectTask::where('module_id', $task->module_id)
                ->where('id', '!=', $task->id)
                ->get();
            
            foreach ($otherTasks as $oTask) {
                $oTask->update([
                    'status' => 'selesai',
                    'catatan_revisi' => null,
                ]);

                if ($oTask->user_id) {
                    ProjectTaskParticipant::where('task_id', $oTask->id)
                        ->where('user_id', $oTask->user_id)
                        ->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => Auth::id(),
                        ]);
                }
            }
        }

        $task->recalculateModuleProgress();

        // Compatibility Layer
        if ($task->user_id) {
            $participant = ProjectTaskParticipant::where('task_id', $task->id)->where('user_id', $task->user_id)->first();
            if ($participant) {
                $participant->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'approved_by' => Auth::id(),
                ]);
            }

            $submission = WorkSubmission::where('task_id', $task->id)->where('user_id', $task->user_id)->latest()->first();
            if ($submission) {
                $submission->update([
                    'status' => 'approved',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                ]);

                ProjectNoteReply::create([
                    'submission_id' => $submission->id,
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'tipe' => 'approve',
                    'isi' => 'Pekerjaan task disetujui admin secara final.',
                ]);
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'aktivitas' => 'Admin menyetujui pengerjaan task: ' . $task->judul,
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Task berhasil di-approve.');
    }

    public function revisionTask(Request $request, ProjectTask $task)
    {
        $data = $request->validate([
            'review_note' => 'required|string',
        ], [
            'review_note.required' => 'Catatan revisi wajib diisi.',
        ]);

        $task->update([
            'status' => 'sedang_dikerjakan',
            'catatan_revisi' => $data['review_note'],
        ]);

        $task->recalculateModuleProgress();

        // Compatibility Layer
        if ($task->user_id) {
            $participant = ProjectTaskParticipant::where('task_id', $task->id)->where('user_id', $task->user_id)->first();
            if ($participant) {
                $participant->update([
                    'status' => 'revision',
                    'approved_at' => null,
                    'approved_by' => null,
                ]);
            }

            $submission = WorkSubmission::where('task_id', $task->id)->where('user_id', $task->user_id)->latest()->first();
            if ($submission) {
                $submission->update([
                    'status' => 'revision',
                    'reviewed_at' => now(),
                    'reviewed_by' => Auth::id(),
                    'review_note' => $data['review_note'],
                ]);

                ProjectNoteReply::create([
                    'submission_id' => $submission->id,
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'tipe' => 'revision',
                    'isi' => $data['review_note'],
                ]);
            }
        }

        ActivityLog::create([
            'user_id' => Auth::id(),
            'project_id' => $task->project_id,
            'aktivitas' => 'Admin menolak/meminta revisi task: ' . $task->judul . ' dengan catatan: ' . $data['review_note'],
        ]);

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Permintaan revisi berhasil dikirim.');
    }
}
