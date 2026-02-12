<?php

namespace App\Livewire\Traits;

trait WithFlasher
{
    /**
     * Flash a success message.
     */
    public function flashSuccess(string $message): void
    {
        flash()->success($message);
    }

    /**
     * Flash an error message.
     */
    public function flashError(string $message): void
    {
        flash()->error($message);
    }

    /**
     * Flash a warning message.
     */
    public function flashWarning(string $message): void
    {
        flash()->warning($message);
    }

    /**
     * Flash an info message.
     */
    public function flashInfo(string $message): void
    {
        flash()->info($message);
    }
}
