<?php

namespace Jefyokta\HightexValidator\Plugin;


interface PunctuationPlugin
{

    /**
     * $validator must return true if the text contain errors
     * @param callable(string $text):bool $validator
     */
    public function validate(string $text): bool;

    public function getMessage():string;
}
