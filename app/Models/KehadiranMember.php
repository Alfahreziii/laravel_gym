<?php

namespace App\Models;


class KehadiranMember extends TenantModel
{
    protected $fillable = ['rfid', 'nama', 'status', 'foto'];
}
