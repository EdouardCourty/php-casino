<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Exception;

use InvalidArgumentException;

final class InvalidHandException extends InvalidArgumentException
{
    public static function emptyHand(): self
    {
        return new self('Hand cannot be empty.');
    }

    public static function cannotSplit(int $cardCount): self
    {
        return new self("Cannot split a hand with {$cardCount} cards. Split requires exactly 2 cards.");
    }

    public static function cannotSplitDifferentRanks(string $rank1, string $rank2): self
    {
        return new self("Cannot split cards with different ranks: {$rank1} and {$rank2}.");
    }

    public static function cannotDoubleDown(int $cardCount): self
    {
        return new self("Cannot double down with {$cardCount} cards. Double down requires exactly 2 cards.");
    }
}
