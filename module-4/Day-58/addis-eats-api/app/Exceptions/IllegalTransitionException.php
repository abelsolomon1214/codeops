<?php

namespace App\Exceptions;

use RuntimeException;

class IllegalTransitionException extends RuntimeException
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
    ) {
        parent::__construct("Cannot transition order from '{$from}' to '{$to}'.");
    }
}
