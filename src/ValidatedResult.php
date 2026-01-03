<?php

namespace Jefyokta\HightexValidator;

use Jefyokta\HightexValidator\Errors\PunctuationError;

class ValidatedResult
{

    public $unreferedImage = 0;
    public $unreferedTable = 0;
    public $unfigImage = 0;
    /**
     * @var PunctuationError[]
     */
    public $punctuationErrors = [];

    public $nodeErrors = [];

    function isOk(): bool
    {
        return  empty($this->nodeErrors) && empty($this->punctuationErrors) && $this->unreferedImage == 0 && $this->unreferedTable == 0;;
    }


    function addPunctuacionError(PunctuationError $punctuation)
    {
        $this->punctuationErrors[] = $punctuation;
    }
};
