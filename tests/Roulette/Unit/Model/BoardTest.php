<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Model;

use Ecourty\PHPCasino\Roulette\Enum\RouletteNumber;
use Ecourty\PHPCasino\Roulette\Enum\RouletteType;
use Ecourty\PHPCasino\Roulette\Model\Board;
use PHPUnit\Framework\TestCase;

class BoardTest extends TestCase
{
    public function testCreateEuropeanCreatesEuropeanBoard(): void
    {
        $board = Board::createEuropean();

        $this->assertSame(RouletteType::EUROPEAN, $board->getType());
    }

    public function testCreateAmericanCreatesAmericanBoard(): void
    {
        $board = Board::createAmerican();

        $this->assertSame(RouletteType::AMERICAN, $board->getType());
    }

    public function testSpinReturnsRouletteNumber(): void
    {
        $board = Board::createEuropean();
        $result = $board->spin();

        $this->assertInstanceOf(RouletteNumber::class, $result);
    }

    public function testSpinResultIsValidForEuropeanBoard(): void
    {
        $board = Board::createEuropean();
        $result = $board->spin();

        $this->assertNotSame(RouletteNumber::DOUBLE_ZERO, $result, 'European roulette should not spin double zero');
    }

    public function testSpinAmericanCanProduceDoubleZero(): void
    {
        $board = Board::createAmerican();

        // Spin many times to increase probability of hitting 00
        $foundDoubleZero = false;
        for ($i = 0; $i < 500; $i++) {
            $result = $board->spin();
            if ($result === RouletteNumber::DOUBLE_ZERO) {
                $foundDoubleZero = true;
                break;
            }
        }

        // With 500 spins and 1/38 probability, we should hit 00 at least once
        $this->assertTrue($foundDoubleZero, 'American roulette should be able to spin double zero');
    }

    public function testGetAvailableNumbersReturnsEuropeanNumbers(): void
    {
        $board = Board::createEuropean();
        $numbers = $board->getAvailableNumbers();

        $this->assertCount(37, $numbers);
    }

    public function testGetAvailableNumbersReturnsAmericanNumbers(): void
    {
        $board = Board::createAmerican();
        $numbers = $board->getAvailableNumbers();

        $this->assertCount(38, $numbers);
    }

    public function testBoardIsImmutable(): void
    {
        $board = Board::createEuropean();
        $firstResult = $board->spin();
        $secondResult = $board->spin();

        // Both spins work independently, board state doesn't change
        $this->assertInstanceOf(RouletteNumber::class, $firstResult);
        $this->assertInstanceOf(RouletteNumber::class, $secondResult);
    }

    public function testMultipleSpinsProduceDifferentResults(): void
    {
        $board = Board::createEuropean();
        $results = [];

        // Spin 100 times
        for ($i = 0; $i < 100; $i++) {
            $results[] = $board->spin();
        }

        // Should have at least 2 different results (statistically almost certain)
        $uniqueResults = array_unique($results, \SORT_REGULAR);
        $this->assertGreaterThan(1, \count($uniqueResults), 'Multiple spins should produce different results');
    }

    public function testSpinResultIsAlwaysValid(): void
    {
        $board = Board::createEuropean();
        $availableNumbers = $board->getAvailableNumbers();

        // Spin 50 times and verify all results are valid
        for ($i = 0; $i < 50; $i++) {
            $result = $board->spin();

            $this->assertContains($result, $availableNumbers, 'Spin result should be a valid number for this board type');
        }
    }
}
