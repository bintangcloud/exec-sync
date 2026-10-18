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
        // Fitur Filter: Bisa mencari berdasarkan status (pending/in_progress/done)
        $query = Task::query();
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $tasks = $query->latest()->get();
        return TaskResource::collection($tasks);
    }

    public function store(Request $request)
    {
        // Validasi input untuk Direktur saat membuat tugas
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'sekretaris_id' => 'required|exists:users,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'direktur_id' => $request->user()->id, // Diambil otomatis dari token login Direktur
            'sekretaris_id' => $validated['sekretaris_id'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Task berhasil dibuat',
            'data' => new TaskResource($task)
        ], 201);
    }

    public function show(string $id)
    {
        $task = Task::findOrFail($id);
        return new TaskResource($task);
    }

    public function update(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        // Validasi update: Sekretaris bisa mengubah status dan mengunggah file laporan
        $validated = $request->validate([
            'status' => 'nullable|in:pending,in_progress,done',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png,docx|max:5120', // Maks 5MB
        ]);

        if ($request->has('status')) {
            $task->status = $validated['status'];
        }

        // Logika Upload File
        if ($request->hasFile('attachment')) {
            // Hapus file lama jika ada
            if ($task->attachment_url) {
                Storage::disk('public')->delete($task->attachment_url);
            }
            // Simpan file baru ke folder public/tasks
            $path = $request->file('attachment')->store('tasks', 'public');
            $task->attachment_url = $path;
        }

        $task->save();

        return response()->json([
            'message' => 'Task berhasil diupdate',
            'data' => new TaskResource($task)
        ]);
    }

    public function destroy(string $id)
    {
        $task = Task::findOrFail($id);
        
        // Hapus file fisik jika ada sebelum menghapus data di database
        if ($task->attachment_url) {
            Storage::disk('public')->delete($task->attachment_url);
        }
        
        $task->delete();
        return response()->json(['message' => 'Task berhasil dihapus']);
    }
}