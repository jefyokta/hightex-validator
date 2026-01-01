<?php

namespace Jefyokta\HightexValidator\Plugin;


class PunctuationPlugin
{

    /**
     * $validator must return true if the text contain errors
     * @param callable(string $text):bool $validator
     */
    public function __construct(private $validator, private $message = '') {}

    function getValidator()
    {

        return $this->validator;
    }

    function  getErrorMessage() {

        return $this->message;
        
    }
}
