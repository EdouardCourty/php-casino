<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Model;

use Ecourty\PHPCasino\Common\Enum\CardRank;
use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Poker\Enum\HandRank;

/**
 * Represents a poker hand evaluation result.
 * Immutable value object containing the best 5-card hand, its rank, and kickers.
 */
final readonly class Hand
{
    /**
     * @param array<Card> $cards The 5 cards that form the best hand
     * @param array<CardRank> $kickers Ranks for tiebreaking (sorted desc by value)
     */
    public function __construct(
        public HandRank $rank,
        public array $cards,
        public array $kickers,
    ) {
    }

    /**
     * Returns the numeric value of the hand rank (for backward compatibility).
     */
    public function getRankValue(): int
    {
        return $this->rank->value;
    }

    /**
     * Returns the human-readable description of the hand.
     */
    public function getDescription(): string
    {
        return $this->rank->getDescription();
    }

    /**
     * Returns a detailed description including kickers.
     * Example: "Full House (Aces over Kings)", "Flush (Ace high)"
     */
    public function getDetailedDescription(): string
    {
        if (empty($this->kickers)) {
            return $this->rank->getDescription();
        }

        $kickerNames = array_map(
            fn (CardRank $rank) => $rank->getName(),
            $this->kickers,
        );

        return match ($this->rank) {
            HandRank::HIGH_CARD => $this->rank->getDescription() . ' (' . implode(', ', $kickerNames) . ')',
            HandRank::ONE_PAIR => $this->rank->getDescription() . ' of ' . $kickerNames[0] . 's',
            HandRank::TWO_PAIR => $this->rank->getDescription() . ' (' . $kickerNames[0] . 's and ' . $kickerNames[1] . 's)',
            HandRank::THREE_OF_A_KIND => $this->rank->getDescription() . ' (' . $kickerNames[0] . 's)',
            HandRank::STRAIGHT => $this->rank->getDescription() . ' (' . $kickerNames[0] . ' high)',
            HandRank::FLUSH => $this->rank->getDescription() . ' (' . $kickerNames[0] . ' high)',
            HandRank::FULL_HOUSE => $this->rank->getDescription() . ' (' . $kickerNames[0] . 's over ' . $kickerNames[1] . 's)',
            HandRank::FOUR_OF_A_KIND => $this->rank->getDescription() . ' (' . $kickerNames[0] . 's)',
            HandRank::STRAIGHT_FLUSH => $this->rank->getDescription() . ' (' . $kickerNames[0] . ' high)',
            HandRank::ROYAL_FLUSH => $this->rank->getDescription(), // No kickers needed, always 10-J-Q-K-A
        };
    }

    /**
     * Returns array format for backward compatibility.
     *
     * @return array{rank: int, kickers: array<int>, cards: array<string>, description: string, detailed_description: string}
     */
    public function toArray(): array
    {
        return [
            'rank' => $this->rank->value,
            'kickers' => array_map(static fn (CardRank $rank) => $rank->getValue(), $this->kickers),
            'cards' => array_map(static fn (Card $card) => $card->toString(), $this->cards),
            'description' => $this->getDescription(),
            'detailed_description' => $this->getDetailedDescription(),
        ];
    }
}
