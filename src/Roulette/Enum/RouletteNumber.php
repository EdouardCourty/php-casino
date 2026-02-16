<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Enum;

/**
 * Represents all possible numbers on a roulette wheel.
 * Includes utility methods to check number properties (color, parity, range, position).
 */
enum RouletteNumber: string
{
    case ZERO = '0';
    case DOUBLE_ZERO = '00';
    case ONE = '1';
    case TWO = '2';
    case THREE = '3';
    case FOUR = '4';
    case FIVE = '5';
    case SIX = '6';
    case SEVEN = '7';
    case EIGHT = '8';
    case NINE = '9';
    case TEN = '10';
    case ELEVEN = '11';
    case TWELVE = '12';
    case THIRTEEN = '13';
    case FOURTEEN = '14';
    case FIFTEEN = '15';
    case SIXTEEN = '16';
    case SEVENTEEN = '17';
    case EIGHTEEN = '18';
    case NINETEEN = '19';
    case TWENTY = '20';
    case TWENTY_ONE = '21';
    case TWENTY_TWO = '22';
    case TWENTY_THREE = '23';
    case TWENTY_FOUR = '24';
    case TWENTY_FIVE = '25';
    case TWENTY_SIX = '26';
    case TWENTY_SEVEN = '27';
    case TWENTY_EIGHT = '28';
    case TWENTY_NINE = '29';
    case THIRTY = '30';
    case THIRTY_ONE = '31';
    case THIRTY_TWO = '32';
    case THIRTY_THREE = '33';
    case THIRTY_FOUR = '34';
    case THIRTY_FIVE = '35';
    case THIRTY_SIX = '36';

    /**
     * Returns the numeric value of the number.
     * Returns 0 for ZERO, '00' string for DOUBLE_ZERO, and int for 1-36.
     */
    public function getValue(): int|string
    {
        if ($this === self::DOUBLE_ZERO) {
            return '00';
        }

        return (int) $this->value;
    }

    /**
     * Checks if this is a zero (0 or 00).
     */
    public function isZero(): bool
    {
        return $this === self::ZERO || $this === self::DOUBLE_ZERO;
    }

    /**
     * Checks if this number is red.
     * Red numbers: 1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36
     */
    public function isRed(): bool
    {
        return \in_array($this, [
            self::ONE, self::THREE, self::FIVE, self::SEVEN, self::NINE,
            self::TWELVE, self::FOURTEEN, self::SIXTEEN, self::EIGHTEEN,
            self::NINETEEN, self::TWENTY_ONE, self::TWENTY_THREE, self::TWENTY_FIVE,
            self::TWENTY_SEVEN, self::THIRTY, self::THIRTY_TWO, self::THIRTY_FOUR, self::THIRTY_SIX,
        ], true);
    }

    /**
     * Checks if this number is black.
     * Black numbers: 2,4,6,8,10,11,13,15,17,20,22,24,26,28,29,31,33,35
     */
    public function isBlack(): bool
    {
        return \in_array($this, [
            self::TWO, self::FOUR, self::SIX, self::EIGHT, self::TEN,
            self::ELEVEN, self::THIRTEEN, self::FIFTEEN, self::SEVENTEEN,
            self::TWENTY, self::TWENTY_TWO, self::TWENTY_FOUR, self::TWENTY_SIX,
            self::TWENTY_EIGHT, self::TWENTY_NINE, self::THIRTY_ONE, self::THIRTY_THREE, self::THIRTY_FIVE,
        ], true);
    }

    /**
     * Checks if this number is green (0 or 00).
     */
    public function isGreen(): bool
    {
        return $this->isZero();
    }

    /**
     * Checks if this number is even (excludes 0 and 00).
     */
    public function isEven(): bool
    {
        if ($this->isZero()) {
            return false;
        }

        /** @var int $value */
        $value = $this->getValue();
        return $value % 2 === 0;
    }

    /**
     * Checks if this number is odd.
     */
    public function isOdd(): bool
    {
        if ($this->isZero()) {
            return false;
        }

        /** @var int $value */
        $value = $this->getValue();
        return $value % 2 === 1;
    }

    /**
     * Checks if this number is low (1-18, also called Manque).
     */
    public function isLow(): bool
    {
        if ($this->isZero()) {
            return false;
        }

        return $this->getValue() >= 1 && $this->getValue() <= 18;
    }

    /**
     * Checks if this number is high (19-36, also called Passe).
     */
    public function isHigh(): bool
    {
        if ($this->isZero()) {
            return false;
        }

        return $this->getValue() >= 19 && $this->getValue() <= 36;
    }

    /**
     * Returns the dozen (1st, 2nd, or 3rd) this number belongs to.
     * Returns null for 0 and 00.
     */
    public function getDozen(): ?int
    {
        if ($this->isZero()) {
            return null;
        }

        $value = $this->getValue();

        if ($value >= 1 && $value <= 12) {
            return 1;
        }

        if ($value >= 13 && $value <= 24) {
            return 2;
        }

        return 3; // 25-36
    }

    /**
     * Returns the column (1st, 2nd, or 3rd) this number belongs to.
     * Returns null for 0 and 00.
     *
     * Column layout:
     * - Column 1: 1, 4, 7, 10, 13, 16, 19, 22, 25, 28, 31, 34
     * - Column 2: 2, 5, 8, 11, 14, 17, 20, 23, 26, 29, 32, 35
     * - Column 3: 3, 6, 9, 12, 15, 18, 21, 24, 27, 30, 33, 36
     */
    public function getColumn(): ?int
    {
        if ($this->isZero()) {
            return null;
        }

        /** @var int $value */
        $value = $this->getValue();
        $remainder = $value % 3;

        if ($remainder === 1) {
            return 1;
        }

        if ($remainder === 2) {
            return 2;
        }

        return 3; // remainder === 0
    }

    /**
     * Returns all roulette numbers (European + American).
     *
     * @return array<self>
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * Returns European roulette numbers (excludes DOUBLE_ZERO).
     *
     * @return array<self>
     */
    public static function europeanNumbers(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $number) => $number !== self::DOUBLE_ZERO,
        ));
    }

    /**
     * Returns American roulette numbers (all including DOUBLE_ZERO).
     *
     * @return array<self>
     */
    public static function americanNumbers(): array
    {
        return self::all();
    }
}
