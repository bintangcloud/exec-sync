<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    // Kolom yang diizinkan untuk diisi data
    protected $fillable = [
        'title', 'description', 'status', 
        'attachment_url', 'direktur_id', 'sekretaris_id'
    ];

    // Relasi: Satu tugas dimiliki oleh satu direktur
    public function direktur()
    {
        return $this->belongsTo(User::class, 'direktur_id');
    }

    // Relasi: Satu tugas dikerjakan oleh satu sekretaris
    public function sekretaris()
    {
        return $this->belongsTo(User::class, 'sekretaris_id');
    }
}