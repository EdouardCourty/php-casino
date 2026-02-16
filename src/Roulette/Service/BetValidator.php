<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Service;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Exception\InvalidBetException;

/**
 * Validates roulette bet number combinations according to table layout.
 */
final class BetValidator
{
    /**
     * Validates that two numbers form a valid split bet (adjacent on the table).
     *
     * @param array<RouletteNumber> $numbers
     * @throws InvalidBetException if the split is invalid
     */
    public static function validateSplit(array $numbers): void
    {
        if (!self::isValidSplit($numbers)) {
            throw InvalidBetException::invalidSplit();
        }
    }

    /**
     * Validates that three numbers form a valid street bet (consecutive on same row).
     *
     * @param array<RouletteNumber> $numbers
     * @throws InvalidBetException if the street is invalid
     */
    public static function validateStreet(array $numbers): void
    {
        if (!self::isValidStreet($numbers)) {
            throw InvalidBetException::invalidStreet();
        }
    }

    /**
     * Validates that four numbers form a valid corner bet (2x2 square).
     *
     * @param array<RouletteNumber> $numbers
     * @throws InvalidBetException if the corner is invalid
     */
    public static function validateCorner(array $numbers): void
    {
        if (!self::isValidCorner($numbers)) {
            throw InvalidBetException::invalidCorner();
        }
    }

    /**
     * Validates that six numbers form a valid line bet (two adjacent streets).
     *
     * @param array<RouletteNumber> $numbers
     * @throws InvalidBetException if the line is invalid
     */
    public static function validateLine(array $numbers): void
    {
        if (!self::isValidLine($numbers)) {
            throw InvalidBetException::invalidLine();
        }
    }

    /**
     * Checks if two numbers form a valid split bet.
     *
     * @param array<RouletteNumber> $numbers
     */
    public static function isValidSplit(array $numbers): bool
    {
        if (\count($numbers) !== 2) {
            return false;
        }

        $values = self::getNumericValues($numbers);
        sort($values);
        [$a, $b] = $values;

        // Special cases with 0 and 00
        if ($a === 0) {
            // 0 is adjacent to 1, 2, 3
            return \in_array($b, [1, 2, 3], true);
        }

        // 00 (represented as -1) is adjacent to 2, 3
        if ($a === -1) {
            return \in_array($b, [2, 3], true);
        }

        // No regular numbers are adjacent to 00
        if ($b === -1) {
            return false;
        }

        // Horizontally adjacent (same row, difference of 1)
        if ($b === $a + 1 && self::areInSameRow($a, $b)) {
            return true;
        }

        // Vertically adjacent (difference of 3)
        if ($b === $a + 3) {
            return true;
        }

        return false;
    }

    /**
     * Checks if three numbers form a valid street bet.
     *
     * @param array<RouletteNumber> $numbers
     */
    public static function isValidStreet(array $numbers): bool
    {
        if (\count($numbers) !== 3) {
            return false;
        }

        $values = self::getNumericValues($numbers);
        sort($values);
        [$a, $b, $c] = $values;

        // Special street: 0-1-2
        if ($a === 0 && $b === 1 && $c === 2) {
            return true;
        }

        // Special street: 0-2-3
        if ($a === 0 && $b === 2 && $c === 3) {
            return true;
        }

        // Special street: 00-2-3 (00 = -1)
        if ($a === -1 && $b === 2 && $c === 3) {
            return true;
        }

        // No streets with 0 or 00 beyond the special cases
        if ($a <= 0) {
            return false;
        }

        // Regular streets: must be consecutive
        if ($b !== $a + 1 || $c !== $b + 1) {
            return false;
        }

        // Must start at column 1 (n % 3 == 1)
        return $a % 3 === 1;
    }

    /**
     * Checks if four numbers form a valid corner bet.
     *
     * @param array<RouletteNumber> $numbers
     */
    public static function isValidCorner(array $numbers): bool
    {
        if (\count($numbers) !== 4) {
            return false;
        }

        $values = self::getNumericValues($numbers);
        sort($values);
        [$a, $b, $c, $d] = $values;

        // Special corner: 0-1-2-3
        if ($a === 0 && $b === 1 && $c === 2 && $d === 3) {
            return true;
        }

        // No regular corners with 0 or 00
        if ($a <= 0) {
            return false;
        }

        // Pattern: n, n+1, n+3, n+4 where n is not in column 3
        if ($b === $a + 1 && $c === $a + 3 && $d === $a + 4) {
            // Must not be in column 3 (n % 3 != 0)
            return $a % 3 !== 0;
        }

        return false;
    }

    /**
     * Checks if six numbers form a valid line bet.
     *
     * @param array<RouletteNumber> $numbers
     */
    public static function isValidLine(array $numbers): bool
    {
        if (\count($numbers) !== 6) {
            return false;
        }

        $values = self::getNumericValues($numbers);
        sort($values);

        // No lines with 0 or 00
        if ($values[0] <= 0) {
            return false;
        }

        $first = $values[0];

        // Must start at column 1
        if ($first % 3 !== 1) {
            return false;
        }

        // Must be 6 consecutive numbers
        for ($i = 1; $i < 6; $i++) {
            if ($values[$i] !== $values[$i - 1] + 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extracts numeric values from RouletteNumbers.
     * Returns array of integers where 0 = 0, 00 = -1, others = face value.
     *
     * @param array<RouletteNumber> $numbers
     * @return array<int>
     */
    private static function getNumericValues(array $numbers): array
    {
        return array_map(function (RouletteNumber $number): int {
            $value = $number->getValue();

            // Special handling for 00 (represented as string '00')
            if ($value === '00') {
                return -1;
            }

            return (int) $value;
        }, $numbers);
    }

    /**
     * Checks if two numbers are in the same row on the table.
     */
    private static function areInSameRow(int $a, int $b): bool
    {
        if ($a <= 0 || $b <= 0) {
            return false;
        }

        return (int) floor(($a - 1) / 3) === (int) floor(($b - 1) / 3);
    }
}
