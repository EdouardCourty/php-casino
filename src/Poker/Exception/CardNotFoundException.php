<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

use Ecourty\PHPCasino\Common\Model\Card;

class CardNotFoundException extends AbstractPokerException
{
    public static function fromCard(Card $card): self
    {
        return new self("Card not found in deck: {$card->toString()}");
    }
}
