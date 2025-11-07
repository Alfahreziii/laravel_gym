<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Verified;
use App\Models\Trainer;

class UpdateTrainerStatusAfterEmailVerified
{
    /**
     * Handle the event.
     */
    public function handle(Verified $event): void
    {
        $user = $event->user;
        
        // Cek apakah user adalah trainer
        if ($user->isTrainer() && $user->trainer) {
            $trainer = $user->trainer;
            
            // Update status dari pending ke nonaktif
            if ($trainer->status === Trainer::STATUS_PENDING) {
                $trainer->update(['status' => Trainer::STATUS_NONAKTIF]);
            }
        }
    }
}