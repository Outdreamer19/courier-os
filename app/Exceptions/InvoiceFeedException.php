<?php

namespace App\Exceptions;

use Exception;

class InvoiceFeedException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $userMessage = 'Billing service is temporarily unavailable. Please try again.',
        private readonly ?array $context = null,
    ) {
        parent::__construct($message);
    }

    public function userMessage(): string
    {
        return $this->userMessage;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function context(): ?array
    {
        return $this->context;
    }
}
