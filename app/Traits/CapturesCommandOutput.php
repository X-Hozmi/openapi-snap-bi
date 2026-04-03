<?php

namespace App\Traits;

trait CapturesCommandOutput
{
    /**
     * @var list<array<string, string>>
     */
    protected array $capturedOutput = [];

    /**
     * @param  string  $string
     * @param  int|string|null  $verbosity
     */
    public function info($string, $verbosity = null): void
    {
        $this->capturedOutput[] = ['type' => 'info', 'message' => $string];
        parent::info($string, $verbosity);
    }

    /**
     * @param  string  $string
     * @param  int|string|null  $verbosity
     */
    public function error($string, $verbosity = null): void
    {
        $this->capturedOutput[] = ['type' => 'error', 'message' => $string];
        parent::error($string, $verbosity);
    }

    /**
     * @return list<array<string, string>>
     */
    public function getCapturedOutput(): array
    {
        return $this->capturedOutput; // @codeCoverageIgnore
    }
}
