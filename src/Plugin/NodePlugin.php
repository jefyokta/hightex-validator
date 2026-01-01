<?php

namespace Jefyokta\HightexValidator\Plugin;


class NodePlugin
{

    /**
     * $validator must return true if the text contain errors
     * @param callable(array $text, ValidatedResult $result) $validator
     */
    public function __construct(private $validator,private $name) {}

    function getValidator(){

        return $this->validator;
    }
}
