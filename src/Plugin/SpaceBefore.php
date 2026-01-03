<?php

namespace Jefyokta\HightexValidator\Plugin;

class SpaceBefore implements PunctuationPlugin{
    private $match = '/\s+[,.!?;]/u';
    public function validate(string $text): bool
    {
      return preg_match($this->match, $text);
    }

    public function getMatches(): ?string
    {
        return $this->match;
    }

    public function getMessage(): string
    {
       return "Terdapat spasi sebelum tanda baca.";
    }
}
