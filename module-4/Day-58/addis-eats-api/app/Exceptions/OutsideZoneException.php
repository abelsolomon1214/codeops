<?php

namespace App\Exceptions;

use RuntimeException;

class OutsideZoneException extends RuntimeException
{
    public function __construct(public readonly string $area)
    {
        parent::__construct("Addis Eats does not deliver to {$area}.");
    }
}
