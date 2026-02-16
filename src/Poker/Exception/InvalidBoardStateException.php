<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

use Ecourty\PHPCasino\Poker\Enum\Street;

class InvalidBoardStateException extends AbstractPokerException
{
    public static function invalidDeckBurnCount(Street $street, int $expectedTotal, int $actualTotal): self
    {
        return new self(
            "Invalid deck state for street {$street->value}: expected {$expectedTotal} total cards " .
            "(deck + community + burns), got {$actualTotal}",
        );
    }
}
