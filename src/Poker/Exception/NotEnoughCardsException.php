<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

class NotEnoughCardsException extends AbstractPokerException
{
    public static function forDraw(int $requested, int $available): self
    {
        return new self("Cannot draw {$requested} cards, only {$available} remaining in deck");
    }

    public static function forBurn(int $requested, int $available): self
    {
        return new self("Cannot burn {$requested} cards, only {$available} remaining in deck");
    }
}
