<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Model;

final readonly class ProbabilityResult
{
    public function __construct(
        public float $winProbability,
        public float $lossProbability,
        public float $pushProbability,
        public float $expectedValue,
        public int $scenariosConsidered,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $total = $this->winProbability + $this->lossProbability + $this->pushProbability;

        if (abs($total - 1.0) > 0.0001) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Probabilities must sum to 1.0, got %.4f (win: %.4f, loss: %.4f, push: %.4f)',
                    $total,
                    $this->winProbability,
                    $this->lossProbability,
                    $this->pushProbability
                )
            );
        }

        if ($this->scenariosConsidered < 1) {
            throw new \InvalidArgumentException(
                "Scenarios considered must be at least 1, got {$this->scenariosConsidered}"
            );
        }
    }

    public function getWinProbabilityPercent(): float
    {
        return $this->winProbability * 100;
    }

    public function getLossProbabilityPercent(): float
    {
        return $this->lossProbability * 100;
    }

    public function getPushProbabilityPercent(): float
    {
        return $this->pushProbability * 100;
    }

    public function isPlayerFavored(): bool
    {
        return $this->winProbability > $this->lossProbability;
    }

    public function isDealerFavored(): bool
    {
        return $this->lossProbability > $this->winProbability;
    }

    public function isEven(): bool
    {
        return abs($this->winProbability - $this->lossProbability) < 0.001;
    }
}
