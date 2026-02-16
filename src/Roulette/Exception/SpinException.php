<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Roulette\Exception;

use Random\RandomException;

/**
 * Thrown when random number generation fails during a spin.
 */
final class SpinException extends AbstractRouletteException
{
    public static function fromRandomException(RandomException $previous): self
    {
        return new self(
            'Failed to generate random number for roulette spin',
            0,
            $previous,
        );
    }
}
