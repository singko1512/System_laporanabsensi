<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bidang extends Model
{
    use HasFactory;

    protected $table = 'md_bidang';

    protected $fillable = [
        'nama',
    ];

    public function pembimbingMagangs()
    {
        return $this->hasMany(PembimbingMagang::class, 'bidang_id');
    }
}
