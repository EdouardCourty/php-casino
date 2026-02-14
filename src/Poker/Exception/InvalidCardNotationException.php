<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

class InvalidCardNotationException extends AbstractPokerException
{
    public static function tooShort(string $notation): self
    {
        return new self("Invalid card notation: '{$notation}' (too short, expected at least 2 characters)");
    }

    public static function create(string $notation): self
    {
        return new self("Invalid card notation: '{$notation}'");
    }
}
