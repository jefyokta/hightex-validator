<?php

namespace Tests\Helper;

use Jefyokta\HightexValidator\Plugin\PunctuationPlugin;

class PunctuationPluginExample implements PunctuationPlugin
{

    public function validate(string $text): bool
    {
        return true;
    }
    public function getMessage(): string
    {
        return "Test";
    }

    public function getMatches(): ?string
    {
        return null;
    }
}
