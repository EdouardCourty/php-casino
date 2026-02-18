<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Blackjack\Exception;

use Ecourty\PHPCasino\Blackjack\Enum\ActionType;
use InvalidArgumentException;

final class InvalidActionException extends InvalidArgumentException
{
    public static function actionNotAllowed(ActionType $action, string $reason): self
    {
        return new self("Action '{$action->getName()}' is not allowed: {$reason}");
    }

    public static function insuranceNotAvailable(): self
    {
        return new self('Insurance is only available when dealer shows an Ace.');
    }

    public static function surrenderNotAllowed(): self
    {
        return new self('Surrender is only allowed on the initial hand (2 cards).');
    }
}
