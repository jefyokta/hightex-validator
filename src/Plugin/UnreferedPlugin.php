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
        if (in_array($context, self::$exceptContext, true)) {
            return;
        }

        if ($node['type'] === 'imageFigure') {
            self::$image[] = [
                'id'      => $node['attrs']['id'],
                'caption' => Validator::mergeText($node['content'][1]['content'] ?? []),
                'context' => $context,
            ];
        }

        if ($node['type'] === 'figureTable') {
            self::$table[] = [
                'id'      => $node['attrs']['id'],
                'caption' => Validator::mergeText($node['content'][0]['content'] ?? []),
                'context' => $context,
            ];
        }

        if ($node['type'] === 'refComponent') {
            self::$ref[] = [
                'type' => $node['attrs']['ref'],
                'link' => $node['attrs']['link'],
            ];
        }

        if (isset($node["content"]) && !empty($node['content'])) {
            foreach ($node['content'] as $n) {
                $this->validate($n, $result, $context);
            }
        }
    }

    static function reset(): void
    {
        self::$ref = [];
        self::$image = [];
        self::$table = [];
        self::$exceptContext = [];
    }

    static function getUnrefered(): array
    {
        foreach (self::$image as $key => $image) {
            foreach (self::$ref as $ref) {
                if ($ref['link'] === $image['id']) {
                    unset(self::$image[$key]);
                    break;
                }
            }
        }

        foreach (self::$table as $key => $table) {
            foreach (self::$ref as $ref) {
                if ($ref['link'] === $table['id']) {
                    unset(self::$table[$key]);
                    break;
                }
            }
        }

        return [
            'image' => array_values(self::$image),
            'table' => array_values(self::$table),
        ];
    }

    static function ignoreIfContext(string ...$contexts): void
    {
        foreach ($contexts as $context) {
            self::$exceptContext[] = $context;
        }
    }
}
