<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Model;

use Ecourty\PHPCasino\Common\Model\Card;
use Ecourty\PHPCasino\Poker\Exception\InvalidCardNotationException;
use Ecourty\PHPCasino\Poker\Exception\InvalidRankException;
use Ecourty\PHPCasino\Poker\Exception\InvalidSuitException;

/**
 * Represents the range of possible hands for a player.
 * Can be a single specific hand or multiple possible hands.
 */
final readonly class PlayerRange
{
    /**
     * @param array<array{0: Card, 1: Card}> $possibleHands Array of possible 2-card combinations
     */
    public function __construct(
        public array $possibleHands,
    ) {
    }

    /**
     * Creates a range from a single specific hand.
     *
     * @param array<string|Card> $holeCards Exactly 2 cards (strings or Card objects)
     *
     * @throws InvalidCardNotationException If card notation is invalid
     * @throws InvalidRankException If card rank is invalid
     * @throws InvalidSuitException If card suit is invalid
     */
    public static function fromSpecificHand(array $holeCards): self
    {
        $cards = array_map(
            static fn (string|Card $card) => $card instanceof Card ? $card : Card::fromString($card),
            $holeCards,
        );

        return new self([[$cards[0], $cards[1]]]);
    }

    /**
     * Creates a range from multiple possible hands.
     *
     * @param array<array<string|Card>> $hands Array of 2-card combinations
     *
     * @throws InvalidCardNotationException If card notation is invalid
     * @throws InvalidRankException If card rank is invalid
     * @throws InvalidSuitException If card suit is invalid
     */
    public static function fromMultipleHands(array $hands): self
    {
        $possibleHands = [];

        foreach ($hands as $hand) {
            $cards = array_map(
                static fn (string|Card $card) => $card instanceof Card ? $card : Card::fromString($card),
                $hand,
            );
            $possibleHands[] = [$cards[0], $cards[1]];
        }

        return new self($possibleHands);
    }

    /**
     * Returns the number of possible hand combinations in this range.
     */
    public function count(): int
    {
        return \count($this->possibleHands);
    }

    /**
     * Returns whether this range contains only one specific hand.
     */
    public function isSpecificHand(): bool
    {
        return $this->count() === 1;
    }

    /**
     * Returns the specific hand if this range contains only one.
     *
     * @return array{0: Card, 1: Card}|null
     */
    public function getSpecificHand(): ?array
    {
        return $this->isSpecificHand() ? $this->possibleHands[0] : null;
    }
}
