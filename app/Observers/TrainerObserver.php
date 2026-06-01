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
     * Handle the Trainer "updated" event.
     * (Email verification logic removed — no longer required)
     */
    public function updated(Trainer $trainer)
    {
        //
    }
}
