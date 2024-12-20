<?php

namespace App\Traits;

use ReflectionClass;

trait EnumHelper
{
    public static function activeValues(): array
    {
        // Usa ReflectionClass para obtener solo las constantes definidas
        $reflector = new ReflectionClass(static::class);
        $constants = $reflector->getConstants();

        return array_values($constants);
    }
}
