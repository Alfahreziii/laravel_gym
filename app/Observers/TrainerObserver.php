<?php

namespace App\Observers;

use App\Models\Trainer;

class TrainerObserver
{
    /**
     * Handle events after all transactions are committed.
     */
    public bool $afterCommit = true;

    /**
     * Listen to user email verification
     * Update trainer status dari 'pending' ke 'nonaktif' setelah email terverifikasi
     */
    public function updated(Trainer $trainer)
    {
        // Cek apakah user sudah verifikasi email dan status masih pending
        if ($trainer->user && 
            $trainer->user->hasVerifiedEmail() && 
            $trainer->status === Trainer::STATUS_PENDING) {
            
            $trainer->update(['status' => Trainer::STATUS_NONAKTIF]);
        }
    }
}