<?php

declare(strict_types=1);

namespace ClickPesa\Exceptions;

class RateLimitException extends ClickPesaException
{
    protected ?int $retryAfterSeconds;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?int $statusCode = 400,
        ?array $responseBody = null,
        ?int $retryAfterSeconds = null
    ) {
        parent::__construct($message, $code, $statusCode, $responseBody);
        $this->retryAfterSeconds = $retryAfterSeconds;
    }

    public function getRetryAfterSeconds(): ?int
    {
        return $this->retryAfterSeconds;
    }
}
