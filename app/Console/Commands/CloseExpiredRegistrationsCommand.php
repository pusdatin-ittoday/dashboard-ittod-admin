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
    protected $description = 'Automatically open and close event registrations based on registration timeline dates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        $eventsWithRegTimeline = Event::whereHas('timelines', function ($query) {
                $query->where('is_registration', true);
            })
            ->with(['timelines' => function ($query) {
                $query->where('is_registration', true);
            }])
            ->get();

        $closedCount = 0;
        $openedCount = 0;

        foreach ($eventsWithRegTimeline as $event) {
            $regTimeline = $event->timelines->first();
            if (!$regTimeline) {
                continue;
            }

            $startDate = $regTimeline->end_date ? $regTimeline->date : null;
            $deadline = $regTimeline->end_date ?? $regTimeline->date;

            // Check if registration should be open or closed
            if ($startDate && $now->lessThan($startDate)) {
                if ($event->is_active) {
                    $event->update(['is_active' => false]);
                    $this->info("Held registration (not started yet) for event [{$event->id}] {$event->title} (opens: {$startDate->format('Y-m-d H:i:s')})");
                    $closedCount++;
                }
            } elseif ($deadline && $now->greaterThan($deadline)) {
                if ($event->is_active) {
                    $event->update(['is_active' => false]);
                    $this->info("Closed registration for event [{$event->id}] {$event->title} (deadline: {$deadline->format('Y-m-d H:i:s')})");
                    $closedCount++;
                }
            } else {
                if (!$event->is_active) {
                    $event->update(['is_active' => true]);
                    $this->info("Opened registration for event [{$event->id}] {$event->title} (open window started)");
                    $openedCount++;
                }
            }
        }

        $this->line("Registration sync complete: {$openedCount} event(s) opened, {$closedCount} event(s) closed/held.");

        return Command::SUCCESS;
    }
}
