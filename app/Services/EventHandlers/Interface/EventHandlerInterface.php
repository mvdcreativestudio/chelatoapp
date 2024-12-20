<?php

namespace App\Services\EventHandlers\Interface;

interface EventHandlerInterface
{
    public function handle(int $storeId, array $data = []);
}
