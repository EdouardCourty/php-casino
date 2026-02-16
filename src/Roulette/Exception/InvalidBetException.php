<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Exception;

use Ecourty\PHPCasino\Roulette\Enum\BetType;

class InvalidBetException extends AbstractRouletteException
{
    public static function invalidNumberCount(BetType $type, int $expected, int $actual): self
    {
        return new self(
            "Invalid number count for bet type {$type->value}: expected {$expected}, got {$actual}",
        );
    }

    public static function fiveNumberOnEuropean(): self
    {
        return new self('Five-number bet (0,00,1,2,3) is only available on American roulette');
    }

    public static function invalidAmount(float $amount): self
    {
        return new self("Bet amount must be positive, got {$amount}");
    }

    public static function invalidDozen(int $dozen): self
    {
        return new self("Dozen must be 1, 2, or 3, got {$dozen}");
    }

    public static function invalidColumn(int $column): self
    {
        return new self("Column must be 1, 2, or 3, got {$column}");
    }

    public static function doubleZeroOnEuropean(): self
    {
        return new self('Double zero (00) is not available on European roulette');
    }

    public static function invalidSplit(): self
    {
        return new self('Invalid split bet: numbers are not adjacent on the table');
    }

    public static function invalidStreet(): self
    {
        return new self('Invalid street bet: numbers do not form a valid street');
    }

    public static function invalidCorner(): self
    {
        return new self('Invalid corner bet: numbers do not form a valid corner');
    }

    public static function invalidLine(): self
    {
        return new self('Invalid line bet: numbers do not form a valid line');
    }
}
