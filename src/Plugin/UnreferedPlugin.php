<?php

namespace Jefyokta\HightexValidator\Plugin;

use Jefyokta\HightexValidator\ValidatedResult;
use Jefyokta\HightexValidator\Validator;

class UnreferedPlugin implements NodePlugin
{

    static $ref = [];
    static $image = [];
    static $table = [];
    static $exceptContext = [];

    public function validate(array $node, ValidatedResult $result, string $context)
    {

        if (in_array($context, self::$exceptContext)) {
            return;
        }
        if ($node['type'] == "imageFigure") {
            $im["id"] = $node["attrs"]['id'];
            $im["caption"] = Validator::mergeText($node['content'][1]["content"] ?? []);
            $im["context"] = $context;
            self::$image[] = $im;
        }
        if ($node['type'] == "figureTable") {
            $tab["id"] = $node["attrs"]['id'];
            $tab["caption"] = Validator::mergeText($node['content'][0]["content"] ?? []);
            $tab["context"] = $context;

            self::$table[] = $tab;
        }
        if ($node["type"] == "refComponent") {
            self::$ref[] = [
                "type" => $node['attrs']['ref'],
                "link" => $node["attrs"]['link']
            ];
        }
    }

    static function reset()
    {
        self::$ref = [];
        self::$image = [];
        self::$table = [];
    }


    static function getUnrefered()
    {

        foreach (self::$image as $key => $value) {
            foreach (self::$ref as $val) {
                if ($val['link'] == $value["id"]) {
                    unset(self::$image[$key]);
                }
            }
        }
        foreach (self::$table as $key => $value["id"]) {
            foreach (self::$ref as $val) {
                if ($val['link'] == $value) {
                    unset(self::$table[$key]);
                }
            }
        }

        return [
            "image" => self::$image,
            "table" => self::$table
        ];
    }

    static function ignoreIfContext(...$contexts)
    {

        foreach ($contexts as $context) {
            self::$exceptContext[] = $context;
        }
    }
}
