<?php

namespace Insurance\ValueObjects;

class ValidationResult
{
    private array $errors = [];

    public function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    public function merge(self $other): void
    {
        $this->errors = array_merge($this->errors, $other->errors);
    }

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}