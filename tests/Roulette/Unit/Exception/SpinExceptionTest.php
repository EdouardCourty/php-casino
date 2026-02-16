<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Tests\Roulette\Unit\Exception;

use Ecourty\PHPCasino\Roulette\Exception\SpinException;
use PHPUnit\Framework\TestCase;

class SpinExceptionTest extends TestCase
{
    public function testFromRandomExceptionCreatesSpinException(): void
    {
        $randomException = new \Random\RandomException('Random generation failed');
        $spinException = SpinException::fromRandomException($randomException);

        $this->assertInstanceOf(SpinException::class, $spinException);
        $this->assertSame($randomException, $spinException->getPrevious());
        $this->assertStringContainsString('Failed to generate random number', $spinException->getMessage());
    }

    public function testExceptionMessageIsDescriptive(): void
    {
        $randomException = new \Random\RandomException('Test error');
        $spinException = SpinException::fromRandomException($randomException);

        $this->assertSame('Failed to generate random number for roulette spin', $spinException->getMessage());
    }

    public function testExceptionPreservesPreviousException(): void
    {
        $randomException = new \Random\RandomException('Original error message');
        $spinException = SpinException::fromRandomException($randomException);

        $previous = $spinException->getPrevious();
        $this->assertInstanceOf(\Random\RandomException::class, $previous);
        $this->assertSame('Original error message', $previous->getMessage());
    }
}
