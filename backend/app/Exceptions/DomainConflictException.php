<?php

namespace App\Exceptions;

use Illuminate\Http\JsonResponse;
use RuntimeException;

/**
 * A business-rule conflict that maps to HTTP 409 with a stable machine `code`
 * (LAST_OWNER, INSUFFICIENT_STOCK, MIN_ORDER_NOT_MET, ...). The frontend keys its
 * copy off the code, not the message. See docs 12 §11.
 *
 * Named errorCode (not code) because Exception already declares $code.
 */
class DomainConflictException extends RuntimeException
{
    /** @param array<string, mixed> $details */
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $this->getMessage(),
            'code' => $this->errorCode,
            'details' => $this->details ?: null,
        ], fn ($v) => $v !== null), 409);
    }
}
