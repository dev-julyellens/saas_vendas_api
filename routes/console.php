<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('health:check')->everyFiveMinutes()->withoutOverlapping();
