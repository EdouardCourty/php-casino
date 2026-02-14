<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Enum;

/**
 * Card suits in poker (hearts, diamonds, clubs, spades).
 */
enum CardSuit: string
{
    case HEARTS = 'h';
    case DIAMONDS = 'd';
    case CLUBS = 'c';
    case SPADES = 's';

    /**
     * Returns all suits in order.
     *
     * @return array<CardSuit>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Returns the symbol for display.
     */
    public function symbol(): string
    {
        return match ($this) {
            self::HEARTS => '♥',
            self::DIAMONDS => '♦',
            self::CLUBS => '♣',
            self::SPADES => '♠',
        };
    }

    /**
     * Returns whether the suit is red.
     */
    public function isRed(): bool
    {
        return $this === self::HEARTS || $this === self::DIAMONDS;
    }

    /**
     * Returns whether the suit is black.
     */
    public function isBlack(): bool
    {
        return !$this->isRed();
    }
}
