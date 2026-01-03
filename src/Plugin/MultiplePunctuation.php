<?php

namespace Jefyokta\HightexValidator\Plugin;

class MultiplePunctuation implements PunctuationPlugin
{
  private $match = '/([!?.,])\1+/';
public function validate(string $text): bool
{
    if (preg_match('/\.{4,}/u', $text)) {
        return true;
    }

    $withoutEllipsis = preg_replace('/\.{3}|…/u', '', $text);

    return preg_match('/([!?.,])\1+/u', $withoutEllipsis) === 1;
}


public function getMatches(): ?string
{
    return '/\.{4,}|([!?.,])\1+/u';
}

  public function getMessage(): string
  {
    return "Tanda baca ditulis berulang.";
  }
}
