<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Enum;

enum GameResult: string
{
    case PLAYER_WIN = 'player_win';
    case DEALER_WIN = 'dealer_win';
    case PUSH = 'push';
    case PLAYER_BLACKJACK = 'player_blackjack';
    case DEALER_BLACKJACK = 'dealer_blackjack';
    case PLAYER_BUST = 'player_bust';
    case DEALER_BUST = 'dealer_bust';

    public function getName(): string
    {
        return match ($this) {
            self::PLAYER_WIN => 'Player Win',
            self::DEALER_WIN => 'Dealer Win',
            self::PUSH => 'Push',
            self::PLAYER_BLACKJACK => 'Player Blackjack',
            self::DEALER_BLACKJACK => 'Dealer Blackjack',
            self::PLAYER_BUST => 'Player Bust',
            self::DEALER_BUST => 'Dealer Bust',
        };
    }

    public function isPlayerVictory(): bool
    {
        return match ($this) {
            self::PLAYER_WIN, self::PLAYER_BLACKJACK, self::DEALER_BUST => true,
            default => false,
        };
    }

    public function isDealerVictory(): bool
    {
        return match ($this) {
            self::DEALER_WIN, self::DEALER_BLACKJACK, self::PLAYER_BUST => true,
            default => false,
        };
    }

    public function isPush(): bool
    {
        return $this === self::PUSH;
    }
}
