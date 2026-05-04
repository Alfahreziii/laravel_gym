<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlaylistMemberTrainer extends Model
{
    use HasFactory;

    protected $table = 'playlist_member_trainers';

    protected $fillable = [
        'id_member_trainer',
        'latihan',
        'sesi_ke',
        'keterangan',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    public function memberTrainer()
    {
        return $this->belongsTo(MemberTrainer::class, 'id_member_trainer');
    }
}
