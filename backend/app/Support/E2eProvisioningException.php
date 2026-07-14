<?php

namespace App\Support;

use RuntimeException;
use Throwable;

final class E2eProvisioningException extends RuntimeException
{
    public function __construct(
        public readonly string $database,
        public readonly string $role,
        public readonly string $lifecycleId,
        Throwable $previous,
    ) {
        parent::__construct('E2E candidate requires lifecycle cleanup.', 0, $previous);
    }
}
