<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseExpiredRegistrationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:close-expired-registrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically close event registrations whose registration timeline deadline has passed';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $activeEvents = Event::where('is_active', true)
            ->whereHas('timelines', function ($query) {
                $query->where('is_registration', true);
            })
            ->with(['timelines' => function ($query) {
                $query->where('is_registration', true);
            }])
            ->get();

        $closedCount = 0;

        foreach ($activeEvents as $event) {
            $regTimeline = $event->timelines->first();
            if (!$regTimeline) {
                continue;
            }

            $deadline = $regTimeline->end_date ?? $regTimeline->date;
            if ($deadline && $now->greaterThan($deadline)) {
                $event->update(['is_active' => false]);
                $this->info("Closed registration for event [{$event->id}] {$event->title} (deadline: {$deadline->format('Y-m-d H:i:s')})");
                $closedCount++;
            }
        }

        if ($closedCount === 0) {
            $this->line('No expired event registrations found.');
        } else {
            $this->info("Total {$closedCount} event registration(s) automatically closed.");
        }

        return Command::SUCCESS;
    }
}
