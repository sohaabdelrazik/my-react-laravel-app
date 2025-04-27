<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Event;
use Carbon\Carbon;
use DB;

class UpdateEventStatuses extends Command
{
    protected $signature = 'events:update-statuses';
    protected $description = 'Update event statuses based on their due dates';

    public function handle()
    {
        // optional: disable triggers to avoid double-setting
        // DB::unprepared('SET @DISABLE_TRIGGER = 1');

        $now = Carbon::now();

        Event::chunk(100, function ($events) use ($now) {
            foreach ($events as $event) {
                if ($event->due_date > $now) {
                    $event->status = 'Future';
                } elseif ($event->due_date->isSameDay($now)) {
                    $event->status = 'In_Progress';
                } else {
                    $event->status = 'Previous';
                }
                $event->save();
            }
        });

        $this->info('Event statuses updated successfully.');
    }
}
