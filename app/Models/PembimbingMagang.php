<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembimbingMagang extends Model
{
    use HasFactory;

    protected $table = 'md_pembimbing_magang';

    protected $fillable = [
        'nama',
        'bidang_id',
    ];

    public function bidang()
    {
        return $this->belongsTo(Bidang::class, 'bidang_id');
    }
}
