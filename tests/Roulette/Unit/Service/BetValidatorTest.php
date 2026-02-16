<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Service;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Exception\InvalidBetException;
use Ecourty\PHPCasino\Roulette\Service\BetValidator;
use PHPUnit\Framework\TestCase;

class BetValidatorTest extends TestCase
{
    // ===== SPLIT TESTS =====

    public function testValidSplitHorizontal(): void
    {
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::ONE, RouletteNumber::TWO]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::SEVEN, RouletteNumber::EIGHT]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::THIRTY_FIVE, RouletteNumber::THIRTY_SIX]));
    }

    public function testValidSplitVertical(): void
    {
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::ONE, RouletteNumber::FOUR]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::FIFTEEN, RouletteNumber::EIGHTEEN]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::THIRTY_THREE, RouletteNumber::THIRTY_SIX]));
    }

    public function testValidSplitWithZero(): void
    {
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::ZERO, RouletteNumber::ONE]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::ZERO, RouletteNumber::TWO]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::ZERO, RouletteNumber::THREE]));
    }

    public function testValidSplitWithDoubleZero(): void
    {
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::DOUBLE_ZERO, RouletteNumber::TWO]));
        $this->assertTrue(BetValidator::isValidSplit([RouletteNumber::DOUBLE_ZERO, RouletteNumber::THREE]));
    }

    public function testInvalidSplitNotAdjacent(): void
    {
        $this->assertFalse(BetValidator::isValidSplit([RouletteNumber::ONE, RouletteNumber::FIVE]));
        $this->assertFalse(BetValidator::isValidSplit([RouletteNumber::ONE, RouletteNumber::THIRTY_SIX]));
        $this->assertFalse(BetValidator::isValidSplit([RouletteNumber::SEVEN, RouletteNumber::FOURTEEN]));
    }

    public function testInvalidSplitAcrossRowBoundary(): void
    {
        // 3 and 4 are not adjacent (different rows)
        $this->assertFalse(BetValidator::isValidSplit([RouletteNumber::THREE, RouletteNumber::FOUR]));
        $this->assertFalse(BetValidator::isValidSplit([RouletteNumber::TWELVE, RouletteNumber::THIRTEEN]));
    }

    public function testInvalidSplitZeroWithDoubleZero(): void
    {
        $this->assertFalse(BetValidator::isValidSplit([RouletteNumber::ZERO, RouletteNumber::DOUBLE_ZERO]));
    }

    public function testValidateSplitThrowsOnInvalid(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid split bet');

        BetValidator::validateSplit([RouletteNumber::ONE, RouletteNumber::TEN]);
    }

    // ===== STREET TESTS =====

    public function testValidStreetRegular(): void
    {
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE]));
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::SEVEN, RouletteNumber::EIGHT, RouletteNumber::NINE]));
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::THIRTY_FOUR, RouletteNumber::THIRTY_FIVE, RouletteNumber::THIRTY_SIX]));
    }

    public function testValidStreetWithZero(): void
    {
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::ZERO, RouletteNumber::ONE, RouletteNumber::TWO]));
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::ZERO, RouletteNumber::TWO, RouletteNumber::THREE]));
    }

    public function testValidStreetWithDoubleZero(): void
    {
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::DOUBLE_ZERO, RouletteNumber::TWO, RouletteNumber::THREE]));
    }

    public function testValidStreetOrderDoesNotMatter(): void
    {
        // Should auto-sort before validation
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::THREE, RouletteNumber::ONE, RouletteNumber::TWO]));
        $this->assertTrue(BetValidator::isValidStreet([RouletteNumber::NINE, RouletteNumber::SEVEN, RouletteNumber::EIGHT]));
    }

    public function testInvalidStreetNotConsecutive(): void
    {
        $this->assertFalse(BetValidator::isValidStreet([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::FOUR]));
        $this->assertFalse(BetValidator::isValidStreet([RouletteNumber::SEVEN, RouletteNumber::FOURTEEN, RouletteNumber::TWENTY_SIX]));
    }

    public function testInvalidStreetNotInSameRow(): void
    {
        // 1, 2, 4 are consecutive but span two rows
        $this->assertFalse(BetValidator::isValidStreet([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::FOUR]));
        $this->assertFalse(BetValidator::isValidStreet([RouletteNumber::TWO, RouletteNumber::THREE, RouletteNumber::FOUR]));
    }

    public function testValidateStreetThrowsOnInvalid(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid street bet');

        BetValidator::validateStreet([RouletteNumber::ONE, RouletteNumber::THREE, RouletteNumber::FIVE]);
    }

    // ===== CORNER TESTS =====

    public function testValidCornerRegular(): void
    {
        $this->assertTrue(BetValidator::isValidCorner([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::FOUR, RouletteNumber::FIVE]));
        $this->assertTrue(BetValidator::isValidCorner([RouletteNumber::TEN, RouletteNumber::ELEVEN, RouletteNumber::THIRTEEN, RouletteNumber::FOURTEEN]));
        $this->assertTrue(BetValidator::isValidCorner([RouletteNumber::THIRTY_TWO, RouletteNumber::THIRTY_THREE, RouletteNumber::THIRTY_FIVE, RouletteNumber::THIRTY_SIX]));
    }

    public function testValidCornerWithZero(): void
    {
        $this->assertTrue(BetValidator::isValidCorner([RouletteNumber::ZERO, RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE]));
    }

    public function testValidCornerOrderDoesNotMatter(): void
    {
        $this->assertTrue(BetValidator::isValidCorner([RouletteNumber::FIVE, RouletteNumber::ONE, RouletteNumber::FOUR, RouletteNumber::TWO]));
    }

    public function testInvalidCornerNotSquare(): void
    {
        $this->assertFalse(BetValidator::isValidCorner([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE, RouletteNumber::FOUR]));
        $this->assertFalse(BetValidator::isValidCorner([RouletteNumber::ONE, RouletteNumber::FIVE, RouletteNumber::TEN, RouletteNumber::FIFTEEN]));
    }

    public function testInvalidCornerColumn3Boundary(): void
    {
        // 3, 4, 6, 7 don't form a valid corner (3 is in column 3)
        $this->assertFalse(BetValidator::isValidCorner([RouletteNumber::THREE, RouletteNumber::FOUR, RouletteNumber::SIX, RouletteNumber::SEVEN]));
    }

    public function testValidateCornerThrowsOnInvalid(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid corner bet');

        BetValidator::validateCorner([RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::SEVEN, RouletteNumber::EIGHT]);
    }

    // ===== LINE TESTS =====

    public function testValidLineRegular(): void
    {
        $this->assertTrue(BetValidator::isValidLine([
            RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE,
            RouletteNumber::FOUR, RouletteNumber::FIVE, RouletteNumber::SIX,
        ]));
        $this->assertTrue(BetValidator::isValidLine([
            RouletteNumber::THIRTEEN, RouletteNumber::FOURTEEN, RouletteNumber::FIFTEEN,
            RouletteNumber::SIXTEEN, RouletteNumber::SEVENTEEN, RouletteNumber::EIGHTEEN,
        ]));
        $this->assertTrue(BetValidator::isValidLine([
            RouletteNumber::THIRTY_ONE, RouletteNumber::THIRTY_TWO, RouletteNumber::THIRTY_THREE,
            RouletteNumber::THIRTY_FOUR, RouletteNumber::THIRTY_FIVE, RouletteNumber::THIRTY_SIX,
        ]));
    }

    public function testValidLineOrderDoesNotMatter(): void
    {
        $this->assertTrue(BetValidator::isValidLine([
            RouletteNumber::SIX, RouletteNumber::ONE, RouletteNumber::FOUR,
            RouletteNumber::THREE, RouletteNumber::FIVE, RouletteNumber::TWO,
        ]));
    }

    public function testInvalidLineNotConsecutive(): void
    {
        $this->assertFalse(BetValidator::isValidLine([
            RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::THREE,
            RouletteNumber::SEVEN, RouletteNumber::EIGHT, RouletteNumber::NINE,
        ]));
    }

    public function testInvalidLineNotTwoStreets(): void
    {
        // 2-7 are consecutive but don't form two streets
        $this->assertFalse(BetValidator::isValidLine([
            RouletteNumber::TWO, RouletteNumber::THREE, RouletteNumber::FOUR,
            RouletteNumber::FIVE, RouletteNumber::SIX, RouletteNumber::SEVEN,
        ]));
    }

    public function testInvalidLineWithZero(): void
    {
        $this->assertFalse(BetValidator::isValidLine([
            RouletteNumber::ZERO, RouletteNumber::ONE, RouletteNumber::TWO,
            RouletteNumber::THREE, RouletteNumber::FOUR, RouletteNumber::FIVE,
        ]));
    }

    public function testValidateLineThrowsOnInvalid(): void
    {
        $this->expectException(InvalidBetException::class);
        $this->expectExceptionMessage('Invalid line bet');

        BetValidator::validateLine([
            RouletteNumber::ONE, RouletteNumber::TWO, RouletteNumber::SEVEN,
            RouletteNumber::EIGHT, RouletteNumber::NINE, RouletteNumber::TEN,
        ]);
    }
}
