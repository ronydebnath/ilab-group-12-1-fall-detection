<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FallEvent;
use App\Services\AlertSystemService;
use Carbon\Carbon;

class CheckFallEventAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fall-events:check-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Fetch threshold from AlertSystemConfig
        $config = app(AlertSystemService::class)->getConfig();
        $threshold = $config->settings['alert_threshold'] ?? 30;
        $cutoff = now()->subSeconds($threshold);

        $events = FallEvent::where('status', 'detected')
            ->where('updated_at', '<=', $cutoff)
            ->get();

        foreach ($events as $event) {
            app(AlertSystemService::class)->processFallEvent($event);
        }

        $this->info("Checked and processed " . $events->count() . " fall events.");
    }
}
