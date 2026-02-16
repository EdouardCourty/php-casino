<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Service;

use Ecourty\PHPCasino\Roulette\Enum\BetType;
use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;
use Ecourty\PHPCasino\Roulette\Model\Bet;
use Ecourty\PHPCasino\Roulette\Model\OddsResult;

/**
 * Calculates odds, probabilities, and expected values for roulette bets.
 */
final class OddsCalculator
{
    /**
     * Calculates detailed odds statistics for a given bet and roulette type.
     */
    public function calculate(Bet $bet, RouletteType $rouletteType): OddsResult
    {
        $totalNumbers = $rouletteType->getNumberCount();
        $winningNumbersCount = $this->countWinningNumbers($bet, $rouletteType);
        $payoutRatio = $bet->getType()->getPayout();

        // Calculate win probability: P(win) = winning_numbers / total_numbers
        $winProbability = $winningNumbersCount / $totalNumbers;

        // Calculate expected value: EV = (P(win) × payout) - (P(loss) × 1)
        // Or simplified: EV = (winning_numbers × (payout + 1) - total_numbers) / total_numbers
        $expectedValueRatio = (($winningNumbersCount * ($payoutRatio + 1)) - $totalNumbers) / $totalNumbers;
        $expectedValue = $expectedValueRatio * $bet->getAmount();

        // Calculate house edge: negative of expected value ratio
        // House edge represents the casino's advantage (always positive)
        $houseEdge = -$expectedValueRatio;

        return new OddsResult(
            winProbability: $winProbability,
            expectedValue: $expectedValue,
            houseEdge: $houseEdge,
            betAmount: $bet->getAmount(),
            winningNumbersCount: $winningNumbersCount,
            totalNumbers: $totalNumbers,
            payoutRatio: $payoutRatio,
        );
    }

    /**
     * Counts the number of winning outcomes for a bet on a specific roulette type.
     */
    private function countWinningNumbers(Bet $bet, RouletteType $rouletteType): int
    {
        $betType = $bet->getType();

        // For inside bets with specific numbers, count directly
        if ($betType->isInsideBet()) {
            return \count($bet->getNumbers());
        }

        // For outside bets, count matching numbers on the wheel
        return match ($betType) {
            BetType::RED => $this->countNumbersMatching($rouletteType, fn (RouletteNumber $n) => $n->isRed()),
            BetType::BLACK => $this->countNumbersMatching($rouletteType, fn (RouletteNumber $n) => $n->isBlack()),
            BetType::EVEN => $this->countNumbersMatching($rouletteType, fn (RouletteNumber $n) => $n->isEven()),
            BetType::ODD => $this->countNumbersMatching($rouletteType, fn (RouletteNumber $n) => $n->isOdd()),
            BetType::LOW => $this->countNumbersMatching($rouletteType, fn (RouletteNumber $n) => $n->isLow()),
            BetType::HIGH => $this->countNumbersMatching($rouletteType, fn (RouletteNumber $n) => $n->isHigh()),
            BetType::DOZEN => $this->countNumbersMatching(
                $rouletteType,
                fn (RouletteNumber $n) => $n->getDozen() === $bet->getPosition(),
            ),
            BetType::COLUMN => $this->countNumbersMatching(
                $rouletteType,
                fn (RouletteNumber $n) => $n->getColumn() === $bet->getPosition(),
            ),
            default => throw new \LogicException("Unexpected bet type: {$betType->value}"),
        };
    }

    /**
     * Counts numbers on the wheel matching a given condition.
     *
     * @param callable(RouletteNumber): bool $condition
     */
    private function countNumbersMatching(RouletteType $rouletteType, callable $condition): int
    {
        $numbers = $rouletteType->getNumbers();

        return \count(array_filter($numbers, $condition));
    }
}
