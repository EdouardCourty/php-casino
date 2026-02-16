<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Enum;

use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;

/**
 * Card ranks in poker (from 2 to Ace).
 */
enum CardRank: string
{
    case TWO = '2';
    case THREE = '3';
    case FOUR = '4';
    case FIVE = '5';
    case SIX = '6';
    case SEVEN = '7';
    case EIGHT = '8';
    case NINE = '9';
    case TEN = '10';
    case JACK = 'J';
    case QUEEN = 'Q';
    case KING = 'K';
    case ACE = 'A';

    /**
     * Returns all ranks in order.
     *
     * @return array<CardRank>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Returns the numeric value of this rank for hand evaluation.
     * Higher values = stronger ranks. Ace is highest (14).
     */
    public function getValue(): int
    {
        return match ($this) {
            self::TWO => 2,
            self::THREE => 3,
            self::FOUR => 4,
            self::FIVE => 5,
            self::SIX => 6,
            self::SEVEN => 7,
            self::EIGHT => 8,
            self::NINE => 9,
            self::TEN => 10,
            self::JACK => 11,
            self::QUEEN => 12,
            self::KING => 13,
            self::ACE => 14,
        };
    }

    /**
     * Creates a CardRank from its numeric value.
     */
    public static function fromValue(int $value): self
    {
        return match ($value) {
            2 => self::TWO,
            3 => self::THREE,
            4 => self::FOUR,
            5 => self::FIVE,
            6 => self::SIX,
            7 => self::SEVEN,
            8 => self::EIGHT,
            9 => self::NINE,
            10 => self::TEN,
            11 => self::JACK,
            12 => self::QUEEN,
            13 => self::KING,
            14 => self::ACE,
            default => throw InvalidRankException::fromValue($value),
        };
    }

    /**
     * Creates a CardRank from its string value.
     */
    public static function fromString(string $rank): self
    {
        return self::from($rank);
    }

    /**
     * Returns the human-readable name of this rank.
     * Used for displaying hand descriptions.
     */
    public function getName(): string
    {
        return match ($this) {
            self::TWO => '2',
            self::THREE => '3',
            self::FOUR => '4',
            self::FIVE => '5',
            self::SIX => '6',
            self::SEVEN => '7',
            self::EIGHT => '8',
            self::NINE => '9',
            self::TEN => '10',
            self::JACK => 'Jack',
            self::QUEEN => 'Queen',
            self::KING => 'King',
            self::ACE => 'Ace',
        };
    }

    /**
     * Checks if this rank is a face card (Jack, Queen, King).
     */
    public function isFaceCard(): bool
    {
        return match ($this) {
            self::JACK, self::QUEEN, self::KING => true,
            default => false,
        };
    }

    /**
     * Checks if this rank is a numeric card (2-10).
     */
    public function isNumeric(): bool
    {
        return \in_array($this, [
            self::TWO,
            self::THREE,
            self::FOUR,
            self::FIVE,
            self::SIX,
            self::SEVEN,
            self::EIGHT,
            self::NINE,
            self::TEN,
        ], true);
    }
}
