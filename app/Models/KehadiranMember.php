<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KehadiranMember extends Model
{
    protected $fillable = ['rfid', 'nama', 'status', 'foto'];
}
