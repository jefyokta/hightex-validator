<?php

namespace Jefyokta\HightexValidator;

use Jefyokta\HightexValidator\Plugin\NodePlugin;
use Jefyokta\HightexValidator\Errors\PunctuationError;
use Jefyokta\HightexValidator\Exception\PluginException;
use Jefyokta\HightexValidator\Plugin\PunctuationPlugin;

class Validator
{


    /**
     * @var class-string<PunctuationPlugin>[]
     */
    private $punctuationPlugins = [];

    /**
     * @var class-string<NodePlugin>[]
     */
    private $nodePlugin = [];

    private $context;
    const INLINE_PLACEHOLDER = "\u{FFFC}";


    public function __construct(private $nodes = [], $context = '', $plugins = [])
    {

        $this->context = $context;

        if (!empty($plugins)) {
            foreach ($plugins as $plug) {
                if ((new $plug) instanceof PunctuationPlugin) {
                    $this->punctuationPlugins[] = $plug;
                    continue;
                }

                if ((new $plug)  instanceof NodePlugin) {
                    $this->nodePlugin[] = $plug;
                    continue;
                }
                throw new PluginException("Cannot add plugin with class {$plug}");
            }
        }
    }

    static function make($nodes = [],  $context = '', $plugins = [],)
    {

        return (new static($nodes,  $context, $plugins));
    }
    public function check(?array $nodes = null, ?ValidatedResult $result = null): ValidatedResult
    {
        $result ??= new ValidatedResult;

        $nodes = $nodes ?? $this->nodes;

        foreach ($nodes as $index => $node) {

            if ($node['type'] === 'image') {
                if (
                    !isset($nodes[$index + 1]) ||
                    $nodes[$index + 1]['type'] !== 'figcaption'
                ) {
                    $result->unreferedImage++;
                }
            }

            if ($node['type'] === 'table') {
                if (
                    !isset($nodes[$index - 1]) ||
                    $nodes[$index - 1]['type'] !== 'figcaption'
                ) {
                    $result->unreferedTable++;
                }
            }
            if (in_array($node['type'], $this->isContainText(), true)) {
                $text = $this->mergeText($node['content'] ?? []);
                $this->validatePunctuation($text, $result);
            }


            if (!empty($this->nodePlugin)) {

                foreach ($this->nodePlugin as $plug) {
                    $this->makeNodePlugin($plug)->validate($node, $result);
                }
            }

            if (!empty($node['content']) && is_array($node['content'])) {
                $this->check($node['content'], $result);
            }
        }

        return $result;
    }

    private  function isContainText()
    {

        return ["paragraph", "figcaption"];
    }
    function mergeText(array $nodes): string
    {
        $result = '';

        foreach ($nodes as $n) {

            if (($n['type'] ?? null) === 'text') {
                $result .= $n['text'];
            } else  if (!empty($n['content']) && is_array($n['content'])) {
                $result .= $this->mergeText($n['content']);
            } else {
                $result .= self::INLINE_PLACEHOLDER;
            }
        }

        return $result;
    }
    function getPlugins()
    {

        return [...$this->nodePlugin, ...$this->punctuationPlugins];
    }


    private function validatePunctuation(string $text, ValidatedResult $result): void
    {
        if ($text === '') {
            return;
        }

        $text = str_replace(self::INLINE_PLACEHOLDER, '', $text);

        $puncError = null;

        if (preg_match('/\s+[,.!?;]/u', $text)) {
            $puncError ??= new PunctuationError($text, $this->context);
            $puncError->addErrorDesc("Terdapat spasi sebelum tanda baca.");
        }


        // if (preg_match('/\s{2,}/u', $text)) {
        //     $puncError ??= new PunctuationError($text, $this->context);
        //     $puncError->addErrorDesc("Terdapat spasi ganda atau lebih.");
        // }

        if (preg_match('/([,.!?])\1+/u', $text)) {
            $puncError ??= new PunctuationError($text, $this->context);
            $puncError->addErrorDesc("Tanda baca ditulis berulang.");
        }

        if (!empty($this->punctuationPlugins)) {
            foreach ($this->punctuationPlugins as $plug) {
                $err = ($plug = $this->makePuncPlugin($plug))
                    ->validate($text);;
                if ($err) {
                    $puncError ??= new PunctuationError($text, $this->context);
                    $puncError->addErrorDesc(
                        $plug->getMessage()
                    );
                }
            }
        }

        if ($puncError) {
            $result->addPunctuacionError($puncError);
        }
    }

    private function makeNodePlugin($class): NodePlugin
    {

        return new $class;
    }

    private function makePuncPlugin($class): PunctuationPlugin
    {

        return new $class;
    }
}
