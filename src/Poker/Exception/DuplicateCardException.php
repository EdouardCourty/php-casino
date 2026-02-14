<?php

declare(strict_types=1);

namespace Ecourty\PHPCasino\Poker\Exception;

class DuplicateCardException extends AbstractPokerException
{
    /**
     * @param array<string> $duplicates
     */
    public static function fromCards(array $duplicates): self
    {
        $cardList = implode(', ', $duplicates);

        return new self("Duplicate cards found in deck: {$cardList}");
    }
}
