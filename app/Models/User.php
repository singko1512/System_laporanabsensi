<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'md_user';

    protected $fillable = [
        'nama',
        'email',
    ];

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'user_id');
    }

    public function jadwalMingguan()
    {
        return $this->hasOne(JadwalMingguan::class, 'user_id');
    }
}
