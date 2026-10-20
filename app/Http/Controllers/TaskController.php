<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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
        } else {
            return response()->json([
                'message' => 'Role tidak memiliki akses.',
            ], 403);
        }

        $filters = $request->validate([
            'status' => [
                'sometimes',
                'required',
                Rule::in(['pending', 'in_progress', 'done']),
            ],
            'search' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],
            'per_page' => [
                'sometimes',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $keyword = $filters['search'];

            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        $tasks = $query->latest()->paginate(
            $filters['per_page'] ?? 15
        );

        return TaskResource::collection($tasks)->additional([
            'message' => 'Berhasil mengambil daftar tugas.',
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'direktur') {
            return response()->json([
                'message' => 'Hanya direktur yang dapat membuat tugas.',
            ], 403);
        }

        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:10000',
            ],
            'sekretaris_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->where('role', 'sekretaris')
                    ->where('status', 'approved'),
            ],
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'direktur_id' => $user->id,
            'sekretaris_id' => $validated['sekretaris_id'],
            'status' => 'pending',
        ]);

        return (new TaskResource($task))
            ->additional([
                'message' => 'Tugas berhasil dibuat.',
            ])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $id)
    {
        $task = Task::findOrFail($id);

        if (! $this->canAccessTask($request->user(), $task)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses ke tugas ini.',
            ], 403);
        }

        return (new TaskResource($task))->additional([
            'message' => 'Berhasil mengambil detail tugas.',
        ]);
    }

    public function update(Request $request, string $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if (! $this->canAccessTask($user, $task)) {
            return response()->json([
                'message' => 'Kamu tidak memiliki akses ke tugas ini.',
            ], 403);
        }

        $oldAttachment = $task->attachment_url;
        $newAttachment = null;

        if ($user->role === 'sekretaris') {
            $validated = $request->validate([
                'status' => [
                    'sometimes',
                    'required',
                    Rule::in(['pending', 'in_progress', 'done']),
                ],
                'attachment' => [
                    'sometimes',
                    'nullable',
                    'file',
                    'mimes:pdf,jpg,png,docx',
                    'max:5120',
                ],
            ]);

            if (array_key_exists('status', $validated)) {
                $task->status = $validated['status'];
            }

            if ($request->hasFile('attachment')) {
                $newAttachment = $request->file('attachment')
                    ->store('tasks', 'public');

                if (! $newAttachment) {
                    return response()->json([
                        'message' => 'Gagal menyimpan file.',
                    ], 500);
                }

                $task->attachment_url = $newAttachment;
            }
        } elseif ($user->role === 'direktur') {
            if (
                $request->exists('status') ||
                $request->hasFile('attachment') ||
                $request->exists('attachment')
            ) {
                return response()->json([
                    'message' => 'Direktur tidak boleh mengubah status atau file laporan.',
                ], 403);
            }

            $isPut = $request->isMethod('put');

            $validated = $request->validate([
                'title' => [
                    $isPut ? 'required' : 'sometimes',
                    'string',
                    'max:255',
                ],
                'description' => [
                    $isPut ? 'present' : 'sometimes',
                    'nullable',
                    'string',
                    'max:10000',
                ],
                'sekretaris_id' => [
                    $isPut ? 'required' : 'sometimes',
                    'integer',
                    Rule::exists('users', 'id')
                        ->where('role', 'sekretaris')
                        ->where('status', 'approved'),
                ],
            ]);

            if (array_key_exists('title', $validated)) {
                $task->title = $validated['title'];
            }

            if (array_key_exists('description', $validated)) {
                $task->description = $validated['description'];
            }

            if (array_key_exists('sekretaris_id', $validated)) {
                $task->sekretaris_id = $validated['sekretaris_id'];
            }
        } else {
            return response()->json([
                'message' => 'Role tidak memiliki akses.',
            ], 403);
        }

        try {
            $task->save();
        } catch (\Throwable $e) {
            if ($newAttachment) {
                Storage::disk('public')->delete($newAttachment);
            }

            throw $e;
        }

        if ($newAttachment && $oldAttachment) {
            Storage::disk('public')->delete($oldAttachment);
        }
        

        return (new TaskResource($task->refresh()))->additional([
            'message' => 'Tugas berhasil diperbarui.',
        ]);
    }

    public function destroy(Request $request, string $id)
    {
        $user = $request->user();
        $task = Task::findOrFail($id);

        if ($user->role !== 'direktur') {
            return response()->json([
                'message' => 'Hanya direktur yang dapat menghapus tugas.',
            ], 403);
        }

        if ((int) $task->direktur_id !== (int) $user->id) {
            return response()->json([
                'message' => 'Kamu tidak dapat menghapus tugas direktur lain.',
            ], 403);
        }

        $attachment = $task->attachment_url;

        $task->delete();

        if ($attachment) {
            Storage::disk('public')->delete($attachment);
        }

        return response()->json([
            'message' => 'Tugas berhasil dihapus.',
        ]);
    }

    private function canAccessTask($user, Task $task): bool
    {
        if ($user->role === 'direktur') {
            return (int) $task->direktur_id === (int) $user->id;
        }

        if ($user->role === 'sekretaris') {
            return (int) $task->sekretaris_id === (int) $user->id;
        }

        return false;
    }
}