<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'md_absensi';

    protected $fillable = [
        'user_id',
        'tanggal',
        'status',
        'foto',
        'laporan',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
