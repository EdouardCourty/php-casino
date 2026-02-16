<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

use Ecourty\PHPCasino\Common\Model\Card;

class NoValidHandFoundException extends AbstractPokerException
{
    /**
     * @param array<Card> $cards
     */
    public static function fromCards(array $cards): self
    {
        $cardStrings = array_map(static fn (Card $card) => $card->toString(), $cards);

        return new self('No valid hand found from cards: ' . implode(', ', $cardStrings));
    }
}
