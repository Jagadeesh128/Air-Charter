<?php

namespace App\Enum;

enum FlightStatus: string
{
    case Scheduled = 'scheduled';
    case Delayed = 'delayed';
    case Cancelled = 'cancelled';
}
