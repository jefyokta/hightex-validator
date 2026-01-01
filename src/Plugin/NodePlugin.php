<?php

namespace Jefyokta\HightexValidator\Plugin;

use Jefyokta\HightexValidator\ValidatedResult;

interface NodePlugin
{
    public function validate(array $node, ValidatedResult &$result);

}
