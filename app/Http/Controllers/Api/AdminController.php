<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;


class AdminController extends Controller
{
       public function pendingDirectors(Request $request)
    {
        abort_unless($request->user()->role === 'admin', 403);

        $users = User::where('role', 'direktur')
            ->where('status', 'pending')
            ->select('id', 'name', 'email', 'role', 'status')
            ->latest()
            ->paginate(15);

        return response()->json($users);
    }

    public function approveDirector(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403);

        if (
            $user->role !== 'direktur' ||
            $user->status !== 'pending'
        ) {
            return response()->json([
                'message' => 'Akun direktur tidak ditemukan atau sudah diproses.',
            ], 422);
        }

        $user->update([
            'status' => 'approved',
        ]);

        return response()->json([
            'message' => 'Pendaftaran direktur disetujui.',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
            ],
        ]);
    }

    public function rejectDirector(Request $request, User $user)
    {
        abort_unless($request->user()->role === 'admin', 403);

        if (
            $user->role !== 'direktur' ||
            $user->status !== 'pending'
        ) {
            return response()->json([
                'message' => 'Akun direktur tidak ditemukan atau sudah diproses.',
            ], 422);
        }

        $user->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'message' => 'Pendaftaran direktur ditolak.',
        ]);
    }
}
