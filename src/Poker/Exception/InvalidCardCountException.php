<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

use Ecourty\PHPCasino\Poker\Enum\Street;

class InvalidCardCountException extends AbstractPokerException
{
    public static function negative(int $count): self
    {
        return new self("Cannot draw or burn a negative number of cards: {$count}");
    }

    public static function forStreet(Street $street, int $expected, int $actual): self
    {
        return new self(
            "Invalid card count for street {$street->value}: expected {$expected} cards, got {$actual}",
        );
    }
}
