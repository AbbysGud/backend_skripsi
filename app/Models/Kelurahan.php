<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelurahan extends Model
{
    use HasFactory;

    protected $table = 'kelurahan';
    protected $fillable = ['nama', 'kode_kelurahan', 'id_kecamatan'];
    protected $hidden = ['created_at', 'updated_at'];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'id_kecamatan');
    }
    
    public function user()
    {
        return $this->hasMany(User::class, 'id_kelurahan');
    }
}