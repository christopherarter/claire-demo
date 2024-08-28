<?php

namespace App\Jobs;

use App\Actions\BusinessActions\SyncBusinessPayItemsAction;
use App\Models\Business;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SyncBusinessJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Business $business)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(SyncBusinessPayItemsAction $syncBusinessPayItemsAction): void
    {
        $syncBusinessPayItemsAction->handle($this->business);
    }
}
