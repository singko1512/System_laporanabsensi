<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectDayAssignment;
use App\Models\ProjectNote;
use App\Models\MasterData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectTimelineController extends Controller
{
    public function storeProject(Request $request)
    {
        $data = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:md_user,id',
            'nama' => 'required|string|max:150',
            'kebutuhan' => 'nullable|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
        ], [
            'user_ids.required' => 'Pilih minimal satu peserta magang terlebih dahulu.',
            'nama.required' => 'Nama project wajib diisi.',
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
        ]);

        $userIds = collect($data['user_ids'])->map(fn ($id) => (int) $id)->unique()->values();

        $project = Project::create([
            'user_id' => $userIds->first(),
            'nama' => $data['nama'],
            'kebutuhan' => $data['kebutuhan'] ?? null,
            'tanggal_mulai' => $data['tanggal_mulai'],
            'tanggal_selesai' => $data['tanggal_selesai'],
            'status' => 'aktif',
        ]);

        $project->members()->sync($userIds->all());

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Project timeline berhasil dibuat.');
    }

    public function updateProject(Request $request, Project $project)
    {
        $data = $request->validate([
            'user_ids' => 'required|array|min:1',
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

        $userIds = collect($data['user_ids'])->map(fn ($id) => (int) $id)->unique()->values();

        $project->update([
            'user_id' => $userIds->first(),
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

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Project timeline berhasil diperbarui.');
    }

    public function destroyProject(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Project timeline berhasil dihapus.');
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

        $isAdmin = (bool) session('admin_authenticated');

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
        }

        $note->update([
            'selesai_pada' => now(config('app.timezone')),
        ]);

        if ($request->input('redirect_tab') === 'timeline') {
            return redirect()->route('absensi.index', [
                'tab' => 'timeline',
                'user_id' => $request->input('user_id', $note->project->user_id),
            ])->with('success_swal', 'Note berhasil ditandai selesai.');
        }

        return redirect()->route('admin.dashboard', ['tab' => 'timeline'])->with('success_swal', 'Note berhasil ditandai selesai.');
    }
}
