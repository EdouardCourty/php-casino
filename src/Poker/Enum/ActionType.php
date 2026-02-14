<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Enum;

/**
 * Represents the different types of poker actions a player can take.
 */
enum ActionType: string
{
    case FOLD = 'fold';
    case CHECK = 'check';
    case CALL = 'call';
    case BET = 'bet';
    case RAISE = 'raise';
    case ALL_IN = 'all_in';

    /**
     * Returns true if this action type requires an amount to be specified.
     */
    public function requiresAmount(): bool
    {
        return match ($this) {
            self::BET, self::RAISE, self::ALL_IN => true,
            self::FOLD, self::CHECK, self::CALL => false,
        };
    }

    /**
     * Returns true if this action type allows an amount to be specified.
     * (CALL can optionally specify the amount being called)
     */
    public function allowsAmount(): bool
    {
        return match ($this) {
            self::BET, self::RAISE, self::ALL_IN, self::CALL => true,
            self::FOLD, self::CHECK => false,
        };
    }

    /**
     * Returns true if this is an aggressive action (puts money in the pot voluntarily).
     */
    public function isAggressive(): bool
    {
        return match ($this) {
            self::BET, self::RAISE, self::ALL_IN => true,
            self::FOLD, self::CHECK, self::CALL => false,
        };
    }

    /**
     * Returns true if this is a passive action (doesn't put money or minimal money).
     */
    public function isPassive(): bool
    {
        return match ($this) {
            self::CHECK, self::CALL => true,
            self::FOLD, self::BET, self::RAISE, self::ALL_IN => false,
        };
    }

    /**
     * Returns true if this action ends the player's participation in the hand.
     */
    public function endsParticipation(): bool
    {
        return $this === self::FOLD;
    }

    /**
     * Returns true if this action involves putting chips in the pot.
     */
    public function putsMoneyInPot(): bool
    {
        return match ($this) {
            self::CALL, self::BET, self::RAISE, self::ALL_IN => true,
            self::FOLD, self::CHECK => false,
        };
    }
}
