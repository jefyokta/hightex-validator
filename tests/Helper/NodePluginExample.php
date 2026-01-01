<?php

namespace Tests\Helper;

use Jefyokta\HightexValidator\Plugin\NodePlugin;
use Jefyokta\HightexValidator\ValidatedResult;

class NodePluginExample implements NodePlugin
{

    public function validate(array $node , ValidatedResult &$result)
    {

        $result->nodeErrors[] = "test";
        // throw new \Exception('Not implemented');
    }
}
