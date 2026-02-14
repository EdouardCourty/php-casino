<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Model;

use Ecourty\PHPCasino\Poker\Enum\CardRank;
use Ecourty\PHPCasino\Poker\Enum\CardSuit;
use Ecourty\PHPCasino\Poker\Exception\CardNotFoundException;
use Ecourty\PHPCasino\Poker\Exception\DuplicateCardException;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardCountException;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardNotationException;
use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;
use Ecourty\PHPCasino\Poker\Exception\InvalidSuitException;
use Ecourty\PHPCasino\Poker\Exception\NotEnoughCardsException;

/**
 * Represents a deck of 52 playing cards.
 * Provides fluent interface for common operations (shuffle, burn, draw).
 */
final class Deck
{
    /**
     * Standard deck size (52 cards)
     */
    public const int MAX_COUNT = 52;

    /**
     * @var array<Card> Cards remaining in the deck
     */
    private array $cards;

    /**
     * Creates a new 52-card deck.
     */
    public function __construct()
    {
        $this->reset();
    }

    /**
     * Creates a new shuffled 52-card deck.
     * Convenience method equivalent to (new Deck())->shuffle().
     */
    public static function shuffled(): self
    {
        return (new self())->shuffle();
    }

    /**
     * Creates a Deck from an array of card strings.
     * Used to restore deck state from an external store.
     *
     * @param array<string> $cardStrings Array of card notations (e.g., ['Ah', 'Kd', '10s'])
     * @param bool $throwOnDuplicate If true, throws exception on duplicate cards. If false, silently ignores duplicates.
     *
     * @throws InvalidCardNotationException If any card string is invalid
     * @throws InvalidRankException If any card rank is invalid
     * @throws InvalidSuitException If any card suit is invalid
     * @throws DuplicateCardException If duplicate cards are found and $throwOnDuplicate is true
     */
    public static function fromStringArray(array $cardStrings, bool $throwOnDuplicate = true): self
    {
        $deck = new self();
        $deck->cards = []; // Empty the default 52-card deck

        foreach ($cardStrings as $cardString) {
            $card = Card::fromString($cardString);
            $deck->addCard($card, throwOnDuplicate: $throwOnDuplicate);
        }

        return $deck;
    }

    /**
     * Shuffles the deck in place.
     * Returns $this for method chaining.
     */
    public function shuffle(): self
    {
        shuffle($this->cards);

        return $this;
    }

    /**
     * Draws N cards from the top of the deck.
     * The drawn cards are removed from the deck.
     *
     * @throws InvalidCardCountException If $count is negative
     * @throws NotEnoughCardsException If there are not enough cards to draw
     *
     * @return array<int<0, max>, Card> The drawn cards
     */
    public function draw(int $count): array
    {
        if ($count < 0) {
            throw InvalidCardCountException::negative($count);
        }

        if ($count > \count($this->cards)) {
            throw NotEnoughCardsException::forDraw($count, \count($this->cards));
        }

        $drawn = [];
        for ($i = 0; $i < $count; $i++) {
            $card = array_shift($this->cards);
            if ($card === null) {
                throw NotEnoughCardsException::forDraw($count, \count($this->cards) + \count($drawn));
            }

            $drawn[] = $card;
        }

        return $drawn;
    }

    /**
     * Burns (discards) N cards from the top of the deck.
     * This follows poker rules where a card is burned before flop/turn/river.
     * Returns $this for method chaining.
     *
     * @throws InvalidCardCountException If $count is negative
     * @throws NotEnoughCardsException If there are not enough cards to burn
     */
    public function burn(int $count = 1): self
    {
        if ($count < 0) {
            throw InvalidCardCountException::negative($count);
        }

        if ($count > \count($this->cards)) {
            throw NotEnoughCardsException::forBurn($count, \count($this->cards));
        }

        for ($i = 0; $i < $count; $i++) {
            array_shift($this->cards);
        }

        return $this;
    }

    /**
     * Resets the deck to a fresh 52-card state.
     * Useful for reusing a deck without creating a new instance.
     * Returns $this for method chaining.
     */
    public function reset(): self
    {
        $this->cards = [];
        foreach (CardSuit::all() as $suit) {
            foreach (CardRank::all() as $rank) {
                $this->cards[] = new Card($rank, $suit);
            }
        }

        return $this;
    }

    /**
     * Removes specific cards from the deck.
     * Useful for creating a deck without already-distributed cards.
     * Returns $this for method chaining.
     *
     * @param array<Card> $cardsToRemove Cards to remove
     * @param bool $throwIfNotFound If true, throws exception if a card is not found. If false, silently ignores missing cards.
     *
     * @throws CardNotFoundException If a card is not found and $throwIfNotFound is true
     */
    public function removeCards(array $cardsToRemove, bool $throwIfNotFound = true): self
    {
        foreach ($cardsToRemove as $card) {
            $this->removeCard($card, throwIfNotFound: $throwIfNotFound);
        }

        return $this;
    }

    /**
     * Adds a single card to the deck.
     * Returns $this for method chaining.
     *
     * @throws DuplicateCardException If the card already exists in the deck and $throwOnDuplicate is true
     */
    public function addCard(Card $card, bool $throwOnDuplicate = true): self
    {
        // Check if card already exists
        foreach ($this->cards as $existingCard) {
            if ($existingCard->equals($card)) {
                if ($throwOnDuplicate) {
                    throw DuplicateCardException::fromCards([$card->toString()]);
                }

                return $this; // Card already exists, do nothing
            }
        }

        $this->cards[] = $card;

        return $this;
    }

    /**
     * Adds multiple cards to the deck.
     * Returns $this for method chaining.
     *
     * @param array<Card> $cardsToAdd Cards to add
     * @param bool $throwOnDuplicate If true, throws exception on duplicate cards. If false, silently ignores duplicates.
     *
     * @throws DuplicateCardException If a duplicate card is found and $throwOnDuplicate is true
     */
    public function addCards(array $cardsToAdd, bool $throwOnDuplicate = true): self
    {
        foreach ($cardsToAdd as $card) {
            $this->addCard($card, throwOnDuplicate: $throwOnDuplicate);
        }

        return $this;
    }

    /**
     * Removes a single card from the deck.
     * Returns $this for method chaining.
     *
     * @throws CardNotFoundException If the card is not found and $throwIfNotFound is true
     */
    public function removeCard(Card $card, bool $throwIfNotFound = true): self
    {
        foreach ($this->cards as $index => $existingCard) {
            if ($existingCard->equals($card)) {
                unset($this->cards[$index]);
                $this->cards = array_values($this->cards); // Re-index array

                return $this;
            }
        }

        // Card not found
        if ($throwIfNotFound) {
            throw CardNotFoundException::fromCard($card);
        }

        return $this; // Card not found but don't throw
    }

    /**
     * Returns the number of cards remaining in the deck.
     */
    public function count(): int
    {
        return \count($this->cards);
    }

    /**
     * Checks if the deck is empty (no cards remaining).
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Returns all remaining cards in the deck (for debugging/testing).
     *
     * @return array<Card>
     */
    public function getRemainingCards(): array
    {
        return $this->cards;
    }
}
