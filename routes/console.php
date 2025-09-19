<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schedule;

Schedule::command('habits:send-daily')->dailyAt('10:00');
