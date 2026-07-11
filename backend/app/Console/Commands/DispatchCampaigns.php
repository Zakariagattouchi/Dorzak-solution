<?php

namespace App\Console\Commands;

use App\Services\CampaignService;
use Illuminate\Console\Command;

class DispatchCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch';

    protected $description = 'Send every scheduled marketing campaign whose time has arrived';

    public function handle(CampaignService $campaigns): int
    {
        $count = $campaigns->dispatchDue();

        $this->info("{$count} campaign(s) dispatched.");

        return self::SUCCESS;
    }
}
