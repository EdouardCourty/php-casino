<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Poker\Unit;

use Ecourty\PHPCasino\Poker\Model\EquityResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EquityResult::class)]
class EquityResultTest extends TestCase
{
    public function testConstruction(): void
    {
        $result = new EquityResult(0.5, 0.1, 1000);

        $this->assertSame(0.5, $result->winProbability);
        $this->assertSame(0.1, $result->tieProbability);
        $this->assertSame(1000, $result->iterations);
    }

    public function testGetLossProbability(): void
    {
        $result = new EquityResult(0.5, 0.1, 1000);

        $this->assertEqualsWithDelta(0.4, $result->getLossProbability(), 0.001);
    }

    public function testGetExpectedValue(): void
    {
        $result = new EquityResult(0.5, 0.1, 1000);

        // EV = 0.5 + (0.1 / 2) = 0.55
        $this->assertEqualsWithDelta(0.55, $result->getExpectedValue(), 0.001);
    }

    public function testGetPercentages(): void
    {
        $result = new EquityResult(0.5, 0.1, 1000);

        $this->assertEqualsWithDelta(50.0, $result->getWinPercentage(), 0.001);
        $this->assertEqualsWithDelta(10.0, $result->getTiePercentage(), 0.001);
        $this->assertEqualsWithDelta(40.0, $result->getLossPercentage(), 0.001);
        $this->assertEqualsWithDelta(55.0, $result->getExpectedValuePercentage(), 0.001);
    }

    public function testToArray(): void
    {
        $result = new EquityResult(0.5, 0.1, 1000);
        $array = $result->toArray();

        $this->assertArrayHasKey('win', $array);
        $this->assertArrayHasKey('tie', $array);
        $this->assertArrayHasKey('loss', $array);
        $this->assertArrayHasKey('expected_value', $array);
        $this->assertArrayHasKey('iterations', $array);

        $this->assertEqualsWithDelta(0.5, $array['win'], 0.001);
        $this->assertEqualsWithDelta(0.1, $array['tie'], 0.001);
        $this->assertEqualsWithDelta(0.4, $array['loss'], 0.001);
        $this->assertEqualsWithDelta(0.55, $array['expected_value'], 0.001);
        $this->assertSame(1000, $array['iterations']);
    }

    public function testToPercentageArray(): void
    {
        $result = new EquityResult(0.5, 0.1, 1000);
        $array = $result->toPercentageArray();

        $this->assertArrayHasKey('win_pct', $array);
        $this->assertArrayHasKey('tie_pct', $array);
        $this->assertArrayHasKey('loss_pct', $array);
        $this->assertArrayHasKey('ev_pct', $array);

        $this->assertEqualsWithDelta(50.0, $array['win_pct'], 0.001);
        $this->assertEqualsWithDelta(10.0, $array['tie_pct'], 0.001);
        $this->assertEqualsWithDelta(40.0, $array['loss_pct'], 0.001);
        $this->assertEqualsWithDelta(55.0, $array['ev_pct'], 0.001);
    }
}
