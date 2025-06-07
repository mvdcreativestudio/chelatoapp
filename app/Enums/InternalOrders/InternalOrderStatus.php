<?php

namespace App\Enums\InternalOrders;

enum InternalOrderStatus: string
{
    case PENDING = 'pending';
    case ACCEPTED = 'accepted';
    case cancelled = 'cancelled';
    case DELIVERED = 'delivered';
}
