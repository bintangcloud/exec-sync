<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'direktur_id',
        'sekretaris_id',
        'status',
        'attachment_url',
    ];

    // Relasi: satu tugas dimiliki oleh satu direktur
    public function direktur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'direktur_id');
    }

    // Relasi: satu tugas ditangani oleh satu sekretaris
    public function sekretaris(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sekretaris_id');
    }
}



