<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    use HasFactory;

    protected $table = 'provinsi';
    protected $fillable = ['nama', 'kode_provinsi'];
    protected $hidden = ['created_at', 'updated_at'];

    // Definisi relasi ke Kota
    public function kota()
    {
        return $this->hasMany(Kota::class, 'id_provinsi');
    }
}