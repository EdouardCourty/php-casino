<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Model;

use Ecourty\PHPCasino\Blackjack\Exception\InvalidHandException;
use Ecourty\PHPCasino\Common\Model\Card;

final class Hand
{
    /**
     * @var array<Card>
     */
    private array $cards = [];

    /**
     * @param array<Card> $cards
     */
    public function __construct(array $cards = [])
    {
        foreach ($cards as $card) {
            $this->addCard($card);
        }
    }

    public static function fromCards(Card ...$cards): self
    {
        return new self($cards);
    }

    public function addCard(Card $card): self
    {
        $this->cards[] = $card;

        return $this;
    }

    /**
     * @return array<Card>
     */
    public function getCards(): array
    {
        return $this->cards;
    }

    public function getCardCount(): int
    {
        return \count($this->cards);
    }

    public function isEmpty(): bool
    {
        return $this->getCardCount() === 0;
    }

    public function canSplit(): bool
    {
        if ($this->getCardCount() !== 2) {
            return false;
        }

        return $this->cards[0]->rank === $this->cards[1]->rank;
    }

    public function canDoubleDown(): bool
    {
        return $this->getCardCount() === 2;
    }

    public function validateNotEmpty(): void
    {
        if ($this->isEmpty()) {
            throw InvalidHandException::emptyHand();
        }
    }

    public function validateCanSplit(): void
    {
        if ($this->getCardCount() !== 2) {
            throw InvalidHandException::cannotSplit($this->getCardCount());
        }

        if ($this->cards[0]->rank !== $this->cards[1]->rank) {
            throw InvalidHandException::cannotSplitDifferentRanks(
                $this->cards[0]->rank->value,
                $this->cards[1]->rank->value,
            );
        }
    }

    public function validateCanDoubleDown(): void
    {
        if ($this->getCardCount() !== 2) {
            throw InvalidHandException::cannotDoubleDown($this->getCardCount());
        }
    }
}
