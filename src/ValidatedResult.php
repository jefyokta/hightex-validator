<?php

namespace Jefyokta\HightexValidator;

use Jefyokta\HightexValidator\Errors\PunctuationError;

class ValidatedResult
{

    public $unreferedImage = 0;
    public $unreferedTable = 0;
    /**
     * @var PunctuationError[]
     */
    public $punctuationErrors = [];

    function isOk(): bool
    {

        return empty($this->punctuationErrors) && $this->unreferedImage == 0 && $this->unreferedTable == 0;;
    }


    function addPunctuacionError(PunctuationError $punctuation)
    {
        $this->punctuationErrors[] = $punctuation;
    }
};
