<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SesiMemberTrainer extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_member_trainer',
        'type',
        'sesi',
        'current_sesi',
        'description',
    ];

    public function membertrainer()
    {
        return $this->belongsTo(MemberTrainer::class);
    }
}
