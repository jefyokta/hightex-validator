<?php

namespace Jefyokta\HightexValidator\Errors;

class PunctuationError
{

    /**
     * @var PunctuationErrorResult[]
     */
    public $errors = [];

    /**
     * 
     */
    public function __construct(
        //the text that contain error
        public $text,
        //define context where is this text
        public $context = ''
    ) {}

    /**
     * define what error type in current text
     */
    function addErrorDesc(PunctuationErrorResult $err)
    {
        $this->errors[] = $err;
        return $this;
    }
};
