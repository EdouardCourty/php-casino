<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Model;

use Ecourty\PHPCasino\Roulette\Enum\BetType;
use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Exception\InvalidBetException;
use Ecourty\PHPCasino\Roulette\Service\BetValidator;

/**
 * Represents an immutable roulette bet.
 */
final readonly class Bet
{
    /**
     * @param BetType $type Bet type
     * @param float $amount Bet amount (must be positive)
     * @param array<RouletteNumber> $numbers Numbers involved in the bet (for inside bets)
     * @param int|null $position Position for dozen/column bets (1, 2, or 3)
     *
     * @throws InvalidBetException If bet configuration is invalid
     */
    public function __construct(
        private BetType $type,
        private float $amount,
        private array $numbers = [],
        private ?int $position = null,
    ) {
        if ($this->amount <= 0) {
            throw InvalidBetException::invalidAmount($this->amount);
        }

        // Validate number count for inside bets
        $expectedCount = $this->type->getExpectedNumberCount();
        if ($expectedCount !== null && \count($this->numbers) !== $expectedCount) {
            throw InvalidBetException::invalidNumberCount($this->type, $expectedCount, \count($this->numbers));
        }

        // Validate position for dozen/column bets
        if (($this->type === BetType::DOZEN || $this->type === BetType::COLUMN) && $this->position === null) {
            throw new InvalidBetException("Position required for {$this->type->value} bet");
        }

        if ($this->position !== null && ($this->position < 1 || $this->position > 3)) {
            if ($this->type === BetType::DOZEN) {
                throw InvalidBetException::invalidDozen($this->position);
            }
            if ($this->type === BetType::COLUMN) {
                throw InvalidBetException::invalidColumn($this->position);
            }
        }

        // Validate bet layout (numbers must form valid combinations on the table)
        $this->validateBetLayout();
    }

    /**
     * Validates that the bet numbers form a valid combination on the table.
     */
    private function validateBetLayout(): void
    {
        match ($this->type) {
            BetType::SPLIT => BetValidator::validateSplit($this->numbers),
            BetType::STREET => BetValidator::validateStreet($this->numbers),
            BetType::CORNER => BetValidator::validateCorner($this->numbers),
            BetType::LINE => BetValidator::validateLine($this->numbers),
            default => null, // STRAIGHT_UP, FIVE_NUMBER, and outside bets don't need layout validation
        };
    }

    /**
     * Creates a straight-up bet on a single number.
     */
    public static function straightUp(RouletteNumber $number, float $amount): self
    {
        return new self(BetType::STRAIGHT_UP, $amount, [$number]);
    }

    /**
     * Creates a split bet on two adjacent numbers.
     *
     * @param array<RouletteNumber> $numbers Must be exactly 2 numbers
     */
    public static function split(array $numbers, float $amount): self
    {
        return new self(BetType::SPLIT, $amount, $numbers);
    }

    /**
     * Creates a street bet on three numbers in a row.
     *
     * @param array<RouletteNumber> $numbers Must be exactly 3 numbers
     */
    public static function street(array $numbers, float $amount): self
    {
        return new self(BetType::STREET, $amount, $numbers);
    }

    /**
     * Creates a corner bet on four numbers in a square.
     *
     * @param array<RouletteNumber> $numbers Must be exactly 4 numbers
     */
    public static function corner(array $numbers, float $amount): self
    {
        return new self(BetType::CORNER, $amount, $numbers);
    }

    /**
     * Creates a five-number bet (0, 00, 1, 2, 3).
     * Only valid for American roulette.
     */
    public static function fiveNumber(float $amount): self
    {
        return new self(BetType::FIVE_NUMBER, $amount, [
            RouletteNumber::ZERO,
            RouletteNumber::DOUBLE_ZERO,
            RouletteNumber::ONE,
            RouletteNumber::TWO,
            RouletteNumber::THREE,
        ]);
    }

    /**
     * Creates a line bet on six numbers (two adjacent streets).
     *
     * @param array<RouletteNumber> $numbers Must be exactly 6 numbers
     */
    public static function line(array $numbers, float $amount): self
    {
        return new self(BetType::LINE, $amount, $numbers);
    }

    /**
     * Creates a bet on red numbers.
     */
    public static function red(float $amount): self
    {
        return new self(BetType::RED, $amount);
    }

    /**
     * Creates a bet on black numbers.
     */
    public static function black(float $amount): self
    {
        return new self(BetType::BLACK, $amount);
    }

    /**
     * Creates a bet on even numbers.
     */
    public static function even(float $amount): self
    {
        return new self(BetType::EVEN, $amount);
    }

    /**
     * Creates a bet on odd numbers.
     */
    public static function odd(float $amount): self
    {
        return new self(BetType::ODD, $amount);
    }

    /**
     * Creates a bet on low numbers (1-18, Manque).
     */
    public static function low(float $amount): self
    {
        return new self(BetType::LOW, $amount);
    }

    /**
     * Creates a bet on high numbers (19-36, Passe).
     */
    public static function high(float $amount): self
    {
        return new self(BetType::HIGH, $amount);
    }

    /**
     * Creates a dozen bet.
     *
     * @param int $dozen Which dozen (1, 2, or 3)
     */
    public static function dozen(int $dozen, float $amount): self
    {
        return new self(BetType::DOZEN, $amount, [], $dozen);
    }

    /**
     * Creates a column bet.
     *
     * @param int $column Which column (1, 2, or 3)
     */
    public static function column(int $column, float $amount): self
    {
        return new self(BetType::COLUMN, $amount, [], $column);
    }

    public function getType(): BetType
    {
        return $this->type;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return array<RouletteNumber>
     */
    public function getNumbers(): array
    {
        return $this->numbers;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * Checks if this bet wins with the given result.
     */
    public function isWinning(RouletteNumber $result): bool
    {
        return match ($this->type) {
            BetType::STRAIGHT_UP, BetType::SPLIT, BetType::STREET,
            BetType::CORNER, BetType::FIVE_NUMBER, BetType::LINE => \in_array($result, $this->numbers, true),
            BetType::RED => $result->isRed(),
            BetType::BLACK => $result->isBlack(),
            BetType::EVEN => $result->isEven(),
            BetType::ODD => $result->isOdd(),
            BetType::LOW => $result->isLow(),
            BetType::HIGH => $result->isHigh(),
            BetType::DOZEN => $result->getDozen() === $this->position,
            BetType::COLUMN => $result->getColumn() === $this->position,
        };
    }

    /**
     * Calculates the total payout for this bet (including original stake).
     * Returns 0 if the bet loses.
     */
    public function calculatePayout(RouletteNumber $result): float
    {
        if (!$this->isWinning($result)) {
            return 0.0;
        }

        // Payout formula: amount * (payout ratio + 1)
        // Example: $10 bet on straight up (35:1) = $10 * 36 = $360 total (includes original $10)
        return $this->amount * ($this->type->getPayout() + 1);
    }

    /**
     * Calculates only the profit (payout minus original stake).
     * Returns negative amount (loss) if bet loses.
     */
    public function calculateProfit(RouletteNumber $result): float
    {
        if (!$this->isWinning($result)) {
            return -$this->amount;
        }

        return $this->amount * $this->type->getPayout();
    }
}
