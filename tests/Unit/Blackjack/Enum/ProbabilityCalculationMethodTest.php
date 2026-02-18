<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Unit\Blackjack\Enum;

use Ecourty\PHPCasino\Blackjack\Enum\ProbabilityCalculationMethod;
use PHPUnit\Framework\TestCase;

final class ProbabilityCalculationMethodTest extends TestCase
{
    public function testEnumerationEnumValue(): void
    {
        $this->assertSame('enumeration', ProbabilityCalculationMethod::ENUMERATION->value);
    }

    public function testMonteCarloEnumValue(): void
    {
        $this->assertSame('monte_carlo', ProbabilityCalculationMethod::MONTE_CARLO->value);
    }

    public function testGetNameForEnumeration(): void
    {
        $this->assertSame('Enumeration', ProbabilityCalculationMethod::ENUMERATION->getName());
    }

    public function testGetNameForMonteCarlo(): void
    {
        $this->assertSame('Monte Carlo', ProbabilityCalculationMethod::MONTE_CARLO->getName());
    }

    public function testGetDescriptionForEnumeration(): void
    {
        $description = ProbabilityCalculationMethod::ENUMERATION->getDescription();

        $this->assertStringContainsString('enumeration', $description);
        $this->assertStringContainsString('accuracy', $description);
    }

    public function testGetDescriptionForMonteCarlo(): void
    {
        $description = ProbabilityCalculationMethod::MONTE_CARLO->getDescription();

        $this->assertStringContainsString('sampling', $description);
        $this->assertStringContainsString('fast', $description);
    }

    public function testAllCasesExist(): void
    {
        $cases = ProbabilityCalculationMethod::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ProbabilityCalculationMethod::ENUMERATION, $cases);
        $this->assertContains(ProbabilityCalculationMethod::MONTE_CARLO, $cases);
    }
}
