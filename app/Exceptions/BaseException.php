<?php

namespace App\Exceptions;

use Exception;

class BaseException extends Exception
{
    /** @var array<mixed> */
    protected array $record;

    /**
     * @param  array<mixed>  $record
     */
    public function __construct(string $message, int $code = 0, array $record = [])
    {
        parent::__construct($message, $code);
        $this->record = $record;
    }

    /**
     * Get the record associated with the exception.
     *
     * @return array<mixed>
     */
    public function getRecord(): ?array
    {
        return $this->record; // @codeCoverageIgnore
    }
}
