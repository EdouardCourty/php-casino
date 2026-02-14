<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

class InvalidSuitException extends AbstractPokerException
{
    public static function fromString(string $suit): self
    {
        return new self("Invalid card suit: '{$suit}' (expected: h, d, c, or s)");
    }
}
