<?php

namespace App\Console\Commands;

use App\Services\Business\PremiumExpiry;
use Illuminate\Console\Command;

class NotifyExpiredPremium extends Command
{
    protected $signature = 'premium:notify-expired';

    protected $description = 'Tell students whose Premium trial, grant or sponsorship has reached its end date';

    public function handle(PremiumExpiry $expiry): int
    {
        $sent = $expiry->run();

        $this->info($sent === 1 ? '1 notification sent.' : "{$sent} notifications sent.");

        return self::SUCCESS;
    }
}
