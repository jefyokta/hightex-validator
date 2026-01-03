<?php

namespace Jefyokta\HightexValidator\Errors;

use Stringable;

class PunctuationErrorResult implements Stringable
{
   
  

    public function __toString(): string
    {

        return $this->desc;
    }

    public function __construct(  private $desc = '', public $match = '') {

    }
    
};
