<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Enum;

/**
 * Represents all possible bet types in roulette with their payout ratios.
 */
enum BetType: string
{
    // Inside bets
    case STRAIGHT_UP = 'straight_up';      // Single number, 35:1
    case SPLIT = 'split';                  // 2 adjacent numbers, 17:1
    case STREET = 'street';                // 3 numbers in a row, 11:1
    case CORNER = 'corner';                // 4 numbers in a square, 8:1
    case FIVE_NUMBER = 'five_number';      // 0,00,1,2,3 (American only), 6:1
    case LINE = 'line';                    // 6 numbers (2 streets), 5:1

    // Outside bets
    case COLUMN = 'column';                // 12 numbers in a column, 2:1
    case DOZEN = 'dozen';                  // 12 numbers in a dozen, 2:1
    case RED = 'red';                      // 18 red numbers, 1:1
    case BLACK = 'black';                  // 18 black numbers, 1:1
    case EVEN = 'even';                    // 18 even numbers, 1:1
    case ODD = 'odd';                      // 18 odd numbers, 1:1
    case LOW = 'low';                      // 1-18 (Manque), 1:1
    case HIGH = 'high';                    // 19-36 (Passe), 1:1

    /**
     * Returns the payout ratio for this bet type.
     * Example: 35 means 35:1 payout (bet $10, win $350 profit + $10 back = $360 total)
     */
    public function getPayout(): int
    {
        return match ($this) {
            self::STRAIGHT_UP => 35,
            self::SPLIT => 17,
            self::STREET => 11,
            self::CORNER => 8,
            self::FIVE_NUMBER => 6,
            self::LINE => 5,
            self::COLUMN, self::DOZEN => 2,
            self::RED, self::BLACK, self::EVEN, self::ODD, self::LOW, self::HIGH => 1,
        };
    }

    /**
     * Checks if this is an inside bet (bet on specific numbers or small groups).
     */
    public function isInsideBet(): bool
    {
        return \in_array($this, [
            self::STRAIGHT_UP,
            self::SPLIT,
            self::STREET,
            self::CORNER,
            self::FIVE_NUMBER,
            self::LINE,
        ], true);
    }

    /**
     * Checks if this is an outside bet (bet on large groups, colors, parity, etc.).
     */
    public function isOutsideBet(): bool
    {
        return !$this->isInsideBet();
    }

    /**
     * Returns the expected number count for this bet type.
     * Returns null for bets that don't use specific numbers (e.g., RED, EVEN).
     */
    public function getExpectedNumberCount(): ?int
    {
        return match ($this) {
            self::STRAIGHT_UP => 1,
            self::SPLIT => 2,
            self::STREET => 3,
            self::CORNER => 4,
            self::FIVE_NUMBER => 5,
            self::LINE => 6,
            default => null, // Outside bets don't specify numbers
        };
    }
}
