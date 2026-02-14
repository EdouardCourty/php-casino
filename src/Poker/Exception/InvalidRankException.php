<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

class InvalidRankException extends AbstractPokerException
{
    public static function fromString(string $rank): self
    {
        return new self("Invalid card rank: '{$rank}' (expected: 2-10, J, Q, K, or A)");
    }

    public static function fromValue(int $value): self
    {
        return new self("Invalid rank value: {$value} (expected: 2-14)");
    }
}
