<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Model;

use Ecourty\PHPCasino\Blackjack\Exception\InvalidShoeException;
use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Common\Model\Deck;

final class Shoe
{
    /**
     * @var array<Card>
     */
    private array $cards = [];
    private readonly int $cutCardPosition;
    private int $cardsDealt = 0;

    /**
     * @param int $deckCount Number of standard 52-card decks to include in the shoe (e.g., 1 to 8)
     * @param float $penetration  Value between 0.0 and 1.0 indicating when to reshuffle the shoe (e.g., 0.75 means reshuffle after 75% of the cards have been dealt)
     */
    public function __construct(
        private readonly int $deckCount,
        private readonly float $penetration = 0.75,
    ) {
        if ($deckCount < 1 || $deckCount > 8) {
            throw InvalidShoeException::invalidDeckCount($deckCount);
        }

        if ($penetration < 0.0 || $penetration > 1.0) {
            throw InvalidShoeException::invalidPenetration($penetration);
        }

        $totalCards = $deckCount * Deck::MAX_COUNT;
        $this->cutCardPosition = (int) ($totalCards * $penetration);

        $this->reset();
    }

    public static function fromGameRules(GameRules $rules): self
    {
        return new self($rules->deckCount, $rules->shoePenetration);
    }

    public function reset(): self
    {
        $this->cards = [];
        $this->cardsDealt = 0;

        for ($i = 0; $i < $this->deckCount; $i++) {
            $deck = new Deck();
            $this->cards = array_merge($this->cards, $deck->getRemainingCards());
        }

        return $this;
    }

    public function shuffle(): self
    {
        shuffle($this->cards);

        return $this;
    }

    public function reshuffle(): self
    {
        return $this->reset()->shuffle();
    }

    /**
     * @return array<Card>
     */
    public function draw(int $count = 1): array
    {
        if ($count < 0) {
            throw new \InvalidArgumentException("Cannot draw negative number of cards: {$count}");
        }

        if ($count > $this->getCardCount()) {
            throw InvalidShoeException::notEnoughCards($count, $this->getCardCount());
        }

        $drawn = [];
        for ($i = 0; $i < $count; $i++) {
            $card = array_shift($this->cards);
            if ($card === null) {
                throw InvalidShoeException::notEnoughCards($count, \count($drawn));
            }

            $drawn[] = $card;
            $this->cardsDealt++;
        }

        return $drawn;
    }

    public function getCardCount(): int
    {
        return \count($this->cards);
    }

    public function getTotalCardCount(): int
    {
        return $this->deckCount * Deck::MAX_COUNT;
    }

    public function getCardsDealt(): int
    {
        return $this->cardsDealt;
    }

    public function isEmpty(): bool
    {
        return $this->getCardCount() === 0;
    }

    public function needsReshuffle(): bool
    {
        return $this->cardsDealt >= $this->cutCardPosition;
    }

    /**
     * @return array<Card>
     */
    public function getRemainingCards(): array
    {
        return $this->cards;
    }

    public function getDeckCount(): int
    {
        return $this->deckCount;
    }

    public function getPenetration(): float
    {
        return $this->penetration;
    }

    public function getCutCardPosition(): int
    {
        return $this->cutCardPosition;
    }
}
