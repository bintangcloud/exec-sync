<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Task::query();

        if ($user->role === 'direktur') {
            $query->where('direktur_id', $user->id);
        } elseif ($user->role === 'sekretaris') {
            $query->where('sekretaris_id', $user->id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->latest()->get();
        
        return response()->json([
            'message' => 'Berhasil mengambil daftar tugas',
            'data' => TaskResource::collection($tasks)
        ], 200);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'direktur') {
            return response()->json([
                'message' => 'Akses ditolak. Hanya Direktur yang dapat membuat tugas.'
            ], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sekretaris_id' => 'required|exists:users,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'direktur_id' => $user->id,
            'sekretaris_id' => $validated['sekretaris_id'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Tugas berhasil dibuat',
            'data' => new TaskResource($task)
        ], 201);
    }

    public function show(Request $request, string $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if ($user->role === 'direktur' && $task->direktur_id !== $user->id) {
            return response()->json(['message' => 'Kamu tidak memiliki akses ke tugas ini.'], 403);
        }
        if ($user->role === 'sekretaris' && $task->sekretaris_id !== $user->id) {
            return response()->json(['message' => 'Kamu tidak memiliki akses ke tugas ini.'], 403);
        }

        return response()->json([
            'message' => 'Berhasil mengambil detail tugas',
            'data' => new TaskResource($task)
        ], 200);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if ($user->role === 'sekretaris') {
            if ($task->sekretaris_id !== $user->id) {
                return response()->json(['message' => 'Ini bukan tugas milikmu!'], 403);
            }

            $validated = $request->validate([
                'status' => 'nullable|in:pending,in_progress,done',
                'attachment' => 'nullable|file|mimes:pdf,jpg,png,docx|max:5120',
            ]);

            if ($request->has('status')) {
                $task->status = $validated['status'];
            }

            if ($request->hasFile('attachment')) {
                if ($task->attachment_url) {
                    Storage::disk('public')->delete($task->attachment_url);
                }
                $task->attachment_url = $request->file('attachment')->store('tasks', 'public');
            }

        } elseif ($user->role === 'direktur') {
            if ($task->direktur_id !== $user->id) {
                return response()->json(['message' => 'Kamu tidak bisa mengubah tugas buatan direktur lain!'], 403);
            }

            if ($request->has('status') || $request->hasFile('attachment')) {
                return response()->json([
                    'message' => 'Direktur tidak memiliki izin untuk mengubah status atau file laporan tugas.'
                ], 403);
            }

            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'sekretaris_id' => 'nullable|exists:users,id',
            ]);

            if ($request->has('title')) {
                $task->title = $validated['title'];
            }
            if ($request->has('description')) {
                $task->description = $validated['description'];
            }
            if ($request->has('sekretaris_id')) {
                $task->sekretaris_id = $validated['sekretaris_id'];
            }
        }

        $task->save();

        return response()->json([
            'message' => 'Tugas berhasil diupdate',
            'data' => new TaskResource($task)
        ], 200);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if ($user->role !== 'direktur') {
            return response()->json(['message' => 'Hanya Direktur yang dapat menghapus tugas.'], 403);
        }
        if ($task->direktur_id !== $user->id) {
            return response()->json(['message' => 'Kamu tidak dapat menghapus tugas buatan direktur lain.'], 403);
        }
        
        if ($task->attachment_url) {
            Storage::disk('public')->delete($task->attachment_url);
        }
        
        $task->delete();
        
        return response()->json(['message' => 'Tugas berhasil dihapus dengan aman'], 200);
    }
}