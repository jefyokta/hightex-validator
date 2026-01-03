<?php

namespace Jefyokta\HightexValidator\Plugin;


interface PunctuationPlugin
{

    public function validate(string $text): bool;

    public function getMessage(): string;

    public function getMatches(): ?string;
}
