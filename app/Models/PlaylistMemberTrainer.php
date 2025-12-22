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
        'id_playlist_trainer',
        'sesi_ke',
        'keterangan',
        'completed',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    /**
     * Relasi ke MemberTrainer
     */
    public function memberTrainer()
    {
        return $this->belongsTo(MemberTrainer::class, 'id_member_trainer');
    }

    /**
     * Relasi ke PlaylistTrainer
     */
    public function playlistTrainer()
    {
        return $this->belongsTo(PlaylistTrainer::class, 'id_playlist_trainer');
    }
}