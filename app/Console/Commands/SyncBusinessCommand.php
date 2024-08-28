<?php

namespace App\Console\Commands;

use App\Actions\BusinessActions\SyncBusinessPayItemsAction;
use App\Models\Business;
use Illuminate\Console\Command;

class SyncBusinessCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-business {businessId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payment items for a specific business';

    /**
     * Execute the console command.
     */
    public function handle(SyncBusinessPayItemsAction $syncAction)
    {
        $businessId = $this->argument('businessId');
        $business   = Business::findOrFail($businessId);

        $syncAction->handle($business);

        $this->info("Synced payment items for business: {$business->name}");
    }
}
