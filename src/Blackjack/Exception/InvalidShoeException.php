<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Exception;

use InvalidArgumentException;

final class InvalidShoeException extends InvalidArgumentException
{
    public static function invalidDeckCount(int $count): self
    {
        return new self("Invalid deck count: {$count}. Shoe must contain between 1 and 8 decks.");
    }

    public static function invalidPenetration(float $penetration): self
    {
        return new self("Invalid penetration: {$penetration}. Must be between 0.0 and 1.0.");
    }

    public static function notEnoughCards(int $requested, int $available): self
    {
        return new self("Not enough cards in shoe. Requested: {$requested}, Available: {$available}.");
    }
}
