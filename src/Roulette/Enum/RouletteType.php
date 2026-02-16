<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Enum;

/**
 * Represents the type of roulette wheel (European vs American).
 */
enum RouletteType: string
{
    case EUROPEAN = 'european';
    case AMERICAN = 'american';

    /**
     * Checks if this roulette type has a double zero.
     */
    public function hasDoubleZero(): bool
    {
        return $this === self::AMERICAN;
    }

    /**
     * Returns the total number of pockets on this roulette wheel.
     */
    public function getNumberCount(): int
    {
        return match ($this) {
            self::EUROPEAN => 37, // 0, 1-36
            self::AMERICAN => 38, // 0, 00, 1-36
        };
    }

    /**
     * Returns all available numbers for this roulette type.
     *
     * @return array<RouletteNumber>
     */
    public function getNumbers(): array
    {
        return match ($this) {
            self::EUROPEAN => RouletteNumber::europeanNumbers(),
            self::AMERICAN => RouletteNumber::americanNumbers(),
        };
    }
}
