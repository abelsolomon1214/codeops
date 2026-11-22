<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Placed     = 'placed';
    case Confirmed  = 'confirmed';
    case Preparing  = 'preparing';
    case Dispatched = 'dispatched';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';
}
