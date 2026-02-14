<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

/**
 * Base exception for all poker-related errors.
 * Allows catching any poker exception with a single catch block.
 */
abstract class AbstractPokerException extends \Exception
{
}
