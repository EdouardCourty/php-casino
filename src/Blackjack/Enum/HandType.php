<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Enum;

enum HandType: string
{
    case SOFT = 'soft';
    case HARD = 'hard';
    case BLACKJACK = 'blackjack';
    case BUST = 'bust';

    public function getName(): string
    {
        return match ($this) {
            self::SOFT => 'Soft',
            self::HARD => 'Hard',
            self::BLACKJACK => 'Blackjack',
            self::BUST => 'Bust',
        };
    }

    public function isBust(): bool
    {
        return $this === self::BUST;
    }

    public function isBlackjack(): bool
    {
        return $this === self::BLACKJACK;
    }

    public function isSoft(): bool
    {
        return $this === self::SOFT;
    }

    public function isHard(): bool
    {
        return $this === self::HARD;
    }
}
