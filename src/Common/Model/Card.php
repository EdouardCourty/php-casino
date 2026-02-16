<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Common\Model;

use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Enum\CardSuit;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardNotationException;
use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;
use Ecourty\PHPCasino\Poker\Exception\InvalidSuitException;

/**
 * Represents a single playing card with a rank and suit.
 */
final readonly class Card
{
    public function __construct(
        public CardRank $rank,
        public CardSuit $suit,
    ) {
    }

    /**
     * Creates a Card from string notation (e.g., "Ah", "Kd", "10s").
     *
     * @throws InvalidCardNotationException If the input string is too short or not in the correct format.
     * @throws InvalidSuitException If the suit character is invalid.
     * @throws InvalidRankException If the rank character is invalid.
     */
    public static function fromString(string $card): self
    {
        if (mb_strlen($card) < 2) {
            throw InvalidCardNotationException::tooShort($card);
        }

        $suitChar = mb_substr($card, -1);
        $rankChar = mb_substr($card, 0, -1);

        $suit = CardSuit::tryFrom($suitChar);
        $rank = CardRank::tryFrom($rankChar);

        if ($suit === null) {
            throw InvalidSuitException::fromString($suitChar);
        }

        if ($rank === null) {
            throw InvalidRankException::fromString($rankChar);
        }

        return new self($rank, $suit);
    }

    /**
     * Converts the card to string notation (e.g., "Ah", "Kd", "10s").
     */
    public function toString(): string
    {
        return $this->rank->value . $this->suit->value;
    }

    /**
     * Magic method for string conversion.
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Checks if this card is equal to another card (same rank and suit).
     */
    public function equals(Card $other): bool
    {
        return $this->rank === $other->rank && $this->suit === $other->suit;
    }

    /**
     * Returns the numeric value of this card's rank (2-14, where Ace=14).
     * Delegates to CardRank::getValue().
     */
    public function getValue(): int
    {
        return $this->rank->getValue();
    }

    /**
     * Returns the human-readable name of this card's rank (e.g., "Ace", "King", "10").
     * Delegates to CardRank::getName().
     */
    public function getName(): string
    {
        return $this->rank->getName();
    }

    /**
     * Checks if this card is a face card (Jack, Queen, King).
     * Delegates to CardRank::isFaceCard().
     */
    public function isFaceCard(): bool
    {
        return $this->rank->isFaceCard();
    }

    /**
     * Checks if this card is a numeric card (2-10).
     * Delegates to CardRank::isNumeric().
     */
    public function isNumeric(): bool
    {
        return $this->rank->isNumeric();
    }
}
