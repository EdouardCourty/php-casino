<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Enum;

/**
 * Poker hand rankings from weakest to strongest.
 * Higher values = stronger hands.
 */
enum HandRank: int
{
    case HIGH_CARD = 1;
    case ONE_PAIR = 2;
    case TWO_PAIR = 3;
    case THREE_OF_A_KIND = 4;
    case STRAIGHT = 5;
    case FLUSH = 6;
    case FULL_HOUSE = 7;
    case FOUR_OF_A_KIND = 8;
    case STRAIGHT_FLUSH = 9;
    case ROYAL_FLUSH = 10;

    /**
     * Returns a human-readable description of the hand rank.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::ROYAL_FLUSH => 'Royal Flush',
            self::STRAIGHT_FLUSH => 'Straight Flush',
            self::FOUR_OF_A_KIND => 'Four of a Kind',
            self::FULL_HOUSE => 'Full House',
            self::FLUSH => 'Flush',
            self::STRAIGHT => 'Straight',
            self::THREE_OF_A_KIND => 'Three of a Kind',
            self::TWO_PAIR => 'Two Pair',
            self::ONE_PAIR => 'One Pair',
            self::HIGH_CARD => 'High Card',
        };
    }
}
