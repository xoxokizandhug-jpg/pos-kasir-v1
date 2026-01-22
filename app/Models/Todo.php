<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Todo extends Model
{
    protected $table = 'task'; // ✅ ini sesuai tabel kamu

    public $timestamps = false;

    protected $fillable = [
        'nama',
        'status',
        'prioritas',
        'tanggal',
    ];
}
