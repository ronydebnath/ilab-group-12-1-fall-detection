<?php

namespace App\Observers;

use App\Models\ElderlyProfile;

class ElderlyProfileObserver
{
    /**
     * Handle the ElderlyProfile "created" event.
     */
    public function created(ElderlyProfile $elderlyProfile): void
    {
        //
    }

    /**
     * Handle the ElderlyProfile "updated" event.
     */
    public function updated(ElderlyProfile $elderlyProfile): void
    {
        //
    }

    /**
     * Handle the ElderlyProfile "deleted" event.
     */
    public function deleted(ElderlyProfile $elderlyProfile): void
    {
        //
    }

    /**
     * Handle the ElderlyProfile "restored" event.
     */
    public function restored(ElderlyProfile $elderlyProfile): void
    {
        //
    }

    /**
     * Handle the ElderlyProfile "force deleted" event.
     */
    public function forceDeleted(ElderlyProfile $elderlyProfile): void
    {
        //
    }
}
