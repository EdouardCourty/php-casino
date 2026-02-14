<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Model;

/**
 * Immutable result of an equity calculation.
 * Contains win/tie/loss probabilities and expected value.
 */
final readonly class EquityResult
{
    /**
     * @param float $winProbability Probability of winning (0.0 to 1.0)
     * @param float $tieProbability Probability of tying (0.0 to 1.0)
     * @param int $iterations Number of scenarios evaluated
     */
    public function __construct(
        public float $winProbability,
        public float $tieProbability,
        public int $iterations,
    ) {
    }

    /**
     * Returns the probability of losing.
     */
    public function getLossProbability(): float
    {
        return 1.0 - $this->winProbability - $this->tieProbability;
    }

    /**
     * Returns the expected value (0.0 to 1.0).
     * EV = P(win) + P(tie) / 2
     */
    public function getExpectedValue(): float
    {
        return $this->winProbability + ($this->tieProbability / 2.0);
    }

    /**
     * Returns win probability as percentage (0-100).
     */
    public function getWinPercentage(): float
    {
        return $this->winProbability * 100.0;
    }

    /**
     * Returns tie probability as percentage (0-100).
     */
    public function getTiePercentage(): float
    {
        return $this->tieProbability * 100.0;
    }

    /**
     * Returns loss probability as percentage (0-100).
     */
    public function getLossPercentage(): float
    {
        return $this->getLossProbability() * 100.0;
    }

    /**
     * Returns expected value as percentage (0-100).
     */
    public function getExpectedValuePercentage(): float
    {
        return $this->getExpectedValue() * 100.0;
    }

    /**
     * Returns array representation for easy serialization.
     *
     * @return array{win: float, tie: float, loss: float, expected_value: float, iterations: int}
     */
    public function toArray(): array
    {
        return [
            'win' => $this->winProbability,
            'tie' => $this->tieProbability,
            'loss' => $this->getLossProbability(),
            'expected_value' => $this->getExpectedValue(),
            'iterations' => $this->iterations,
        ];
    }

    /**
     * Returns array with percentages instead of probabilities.
     *
     * @return array{win_pct: float, tie_pct: float, loss_pct: float, ev_pct: float, iterations: int}
     */
    public function toPercentageArray(): array
    {
        return [
            'win_pct' => $this->getWinPercentage(),
            'tie_pct' => $this->getTiePercentage(),
            'loss_pct' => $this->getLossPercentage(),
            'ev_pct' => $this->getExpectedValuePercentage(),
            'iterations' => $this->iterations,
        ];
    }
}
