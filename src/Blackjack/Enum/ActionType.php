<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Enum;

enum ActionType: string
{
    case HIT = 'hit';
    case STAND = 'stand';
    case DOUBLE_DOWN = 'double_down';
    case SPLIT = 'split';
    case SURRENDER = 'surrender';
    case INSURANCE = 'insurance';

    public function getName(): string
    {
        return match ($this) {
            self::HIT => 'Hit',
            self::STAND => 'Stand',
            self::DOUBLE_DOWN => 'Double Down',
            self::SPLIT => 'Split',
            self::SURRENDER => 'Surrender',
            self::INSURANCE => 'Insurance',
        };
    }
}
