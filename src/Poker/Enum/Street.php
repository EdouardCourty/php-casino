<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Enum;

/**
 * Represents the different streets (phases) in a poker hand.
 */
enum Street: string
{
    case PREFLOP = 'preflop';
    case FLOP = 'flop';
    case TURN = 'turn';
    case RIVER = 'river';
    case SHOWDOWN = 'showdown';

    /**
     * Returns the number of community cards that should be on the board at this street.
     */
    public function getCommunityCardsCount(): int
    {
        return match ($this) {
            self::PREFLOP => 0,
            self::FLOP => 3,
            self::TURN => 4,
            self::RIVER => 5,
            self::SHOWDOWN => 5,
        };
    }

    /**
     * Returns the next street, or null if this is the last street.
     */
    public function getNextStreet(): ?self
    {
        return match ($this) {
            self::PREFLOP => self::FLOP,
            self::FLOP => self::TURN,
            self::TURN => self::RIVER,
            self::RIVER => self::SHOWDOWN,
            self::SHOWDOWN => null,
        };
    }

    /**
     * Returns true if this street is after the flop (has community cards).
     */
    public function isPostflop(): bool
    {
        return $this !== self::PREFLOP;
    }

    /**
     * Returns true if betting action can occur on this street.
     */
    public function allowsBetting(): bool
    {
        return $this !== self::SHOWDOWN;
    }

    /**
     * Returns the number of cards to deal when entering this street.
     * (Flop deals 3, Turn deals 1, River deals 1)
     */
    public function getCardsToDeal(): int
    {
        return match ($this) {
            self::PREFLOP => 0,
            self::FLOP => 3,
            self::TURN => 1,
            self::RIVER => 1,
            self::SHOWDOWN => 0,
        };
    }

    /**
     * Returns the total number of cards that should have been burned before reaching this street.
     * (1 burn before flop, 1 before turn, 1 before river = cumulative)
     */
    public function getTotalBurnsBeforeStreet(): int
    {
        return match ($this) {
            self::PREFLOP => 0,
            self::FLOP => 1,
            self::TURN => 2,
            self::RIVER => 3,
            self::SHOWDOWN => 3,
        };
    }
}
