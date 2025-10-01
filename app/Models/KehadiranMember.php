<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranMember extends Model
{
    protected $fillable = ['rfid', 'status'];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'rfid', 'id_kartu');
    }
}
