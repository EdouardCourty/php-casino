<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

/**
 * Exception thrown when equity calculator input is invalid.
 */
class InvalidEquityInputException extends AbstractPokerException
{
    public static function duplicateCards(string $card): self
    {
        return new self(\sprintf('Duplicate card found in input: %s', $card));
    }

    public static function invalidHoleCardsCount(int $count): self
    {
        return new self(\sprintf('Hero must have exactly 2 hole cards, got %d', $count));
    }

    public static function invalidCommunityCardsCount(int $count): self
    {
        return new self(\sprintf('Community cards must be between 0 and 5, got %d', $count));
    }

    public static function noOpponents(): self
    {
        return new self('At least one opponent range is required');
    }

    public static function emptyOpponentRange(int $index): self
    {
        return new self(\sprintf('Opponent #%d has an empty range (no possible hands)', $index));
    }

    public static function invalidIterations(int $iterations): self
    {
        return new self(\sprintf('Iterations must be positive, got %d', $iterations));
    }
}
