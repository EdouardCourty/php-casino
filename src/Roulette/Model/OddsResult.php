<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Model;

/**
 * Immutable result of odds calculation for a roulette bet.
 * Contains win/loss probabilities, expected value, house edge, and detailed statistics.
 */
final readonly class OddsResult
{
    /**
     * @param float $winProbability Probability of winning (0.0 to 1.0)
     * @param float $expectedValue Expected value in monetary units (positive = player advantage, negative = house advantage)
     * @param float $houseEdge House edge percentage (0.0 to 1.0)
     * @param float $betAmount Original bet amount
     * @param int $winningNumbersCount Number of winning outcomes
     * @param int $totalNumbers Total number of possible outcomes on the wheel
     * @param int $payoutRatio Payout ratio (e.g., 35 for 35:1)
     */
    public function __construct(
        public float $winProbability,
        public float $expectedValue,
        public float $houseEdge,
        public float $betAmount,
        public int $winningNumbersCount,
        public int $totalNumbers,
        public int $payoutRatio,
    ) {
    }

    /**
     * Returns the probability of losing.
     */
    public function getLossProbability(): float
    {
        return 1.0 - $this->winProbability;
    }

    /**
     * Returns win probability as percentage (0-100).
     */
    public function getWinPercentage(): float
    {
        return $this->winProbability * 100.0;
    }

    /**
     * Returns loss probability as percentage (0-100).
     */
    public function getLossPercentage(): float
    {
        return $this->getLossProbability() * 100.0;
    }

    /**
     * Returns house edge as percentage (0-100).
     */
    public function getHouseEdgePercentage(): float
    {
        return $this->houseEdge * 100.0;
    }

    /**
     * Returns the expected profit/loss for this bet.
     * Positive = expected profit, Negative = expected loss.
     */
    public function getExpectedProfit(): float
    {
        return $this->expectedValue;
    }

    /**
     * Returns the expected total payout (including original stake).
     */
    public function getExpectedPayout(): float
    {
        return $this->betAmount + $this->expectedValue;
    }

    /**
     * Returns the expected profit as a percentage of the bet amount.
     */
    public function getExpectedProfitPercentage(): float
    {
        if ($this->betAmount === 0.0) {
            return 0.0;
        }

        return ($this->expectedValue / $this->betAmount) * 100.0;
    }

    /**
     * Returns the actual payout amount if the bet wins (including original stake).
     */
    public function getWinPayout(): float
    {
        return $this->betAmount * ($this->payoutRatio + 1);
    }

    /**
     * Returns the actual profit amount if the bet wins (excluding original stake).
     */
    public function getWinProfit(): float
    {
        return $this->betAmount * $this->payoutRatio;
    }

    /**
     * Returns array representation for easy serialization.
     *
     * @return array{
     *     win_probability: float,
     *     loss_probability: float,
     *     expected_value: float,
     *     house_edge: float,
     *     bet_amount: float,
     *     winning_numbers: int,
     *     total_numbers: int,
     *     payout_ratio: int
     * }
     */
    public function toArray(): array
    {
        return [
            'win_probability' => $this->winProbability,
            'loss_probability' => $this->getLossProbability(),
            'expected_value' => $this->expectedValue,
            'house_edge' => $this->houseEdge,
            'bet_amount' => $this->betAmount,
            'winning_numbers' => $this->winningNumbersCount,
            'total_numbers' => $this->totalNumbers,
            'payout_ratio' => $this->payoutRatio,
        ];
    }

    /**
     * Returns array with percentages and detailed statistics.
     *
     * @return array{
     *     win_pct: float,
     *     loss_pct: float,
     *     expected_value: float,
     *     expected_profit_pct: float,
     *     house_edge_pct: float,
     *     bet_amount: float,
     *     winning_numbers: int,
     *     total_numbers: int,
     *     payout_ratio: int,
     *     win_payout: float,
     *     win_profit: float
     * }
     */
    public function toPercentageArray(): array
    {
        return [
            'win_pct' => $this->getWinPercentage(),
            'loss_pct' => $this->getLossPercentage(),
            'expected_value' => $this->expectedValue,
            'expected_profit_pct' => $this->getExpectedProfitPercentage(),
            'house_edge_pct' => $this->getHouseEdgePercentage(),
            'bet_amount' => $this->betAmount,
            'winning_numbers' => $this->winningNumbersCount,
            'total_numbers' => $this->totalNumbers,
            'payout_ratio' => $this->payoutRatio,
            'win_payout' => $this->getWinPayout(),
            'win_profit' => $this->getWinProfit(),
        ];
    }
}
