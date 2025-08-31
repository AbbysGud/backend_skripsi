<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NgrokServer extends Model
{
    use HasFactory;

    protected $table = 'ngrok_servers';
    protected $fillable = ['http_url', 'websocket_url', 'websocket_port'];
    public $timestamps = false;
}
