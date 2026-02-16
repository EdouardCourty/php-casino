<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Model;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;
use Ecourty\PHPCasino\Roulette\Exception\SpinException;
use Random\RandomException;

/**
 * Represents an immutable roulette wheel/board.
 * Provides random number generation based on the roulette type.
 */
final readonly class Board
{
    public function __construct(
        private RouletteType $type,
    ) {
    }

    /**
     * Creates a new European roulette board (single zero).
     */
    public static function createEuropean(): self
    {
        return new self(RouletteType::EUROPEAN);
    }

    /**
     * Creates a new American roulette board (double zero).
     */
    public static function createAmerican(): self
    {
        return new self(RouletteType::AMERICAN);
    }

    /**
     * Returns the roulette type (European or American).
     */
    public function getType(): RouletteType
    {
        return $this->type;
    }

    /**
     * Spins the roulette wheel and returns the result.
     * Uses PHP's random_int() for cryptographically secure random number generation.
     *
     * @throws SpinException if random number generation fails
     */
    public function spin(): RouletteNumber
    {
        $availableNumbers = $this->getAvailableNumbers();
        $count = \count($availableNumbers);
        \assert($count > 0, 'Available numbers cannot be empty');

        try {
            $randomIndex = random_int(0, $count - 1);
        } catch (RandomException $e) {
            throw SpinException::fromRandomException($e);
        }

        return $availableNumbers[$randomIndex];
    }

    /**
     * Returns all available numbers for this board's roulette type.
     *
     * @return array<RouletteNumber>
     */
    public function getAvailableNumbers(): array
    {
        return $this->type->getNumbers();
    }
}
