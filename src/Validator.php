<?php

namespace Jefyokta\HightexValidator;

use Jefyokta\HightexValidator\Plugin\NodePlugin;
use Jefyokta\HightexValidator\Errors\PunctuationError;
use Jefyokta\HightexValidator\Errors\PunctuationErrorResult;
use Jefyokta\HightexValidator\Exception\PluginException;
use Jefyokta\HightexValidator\Plugin\MultiplePunctuation;
use Jefyokta\HightexValidator\Plugin\PunctuationPlugin;
use Jefyokta\HightexValidator\Plugin\SpaceBefore;
use Jefyokta\HightexValidator\Plugin\UnreferedPlugin;

class Validator
{


    /**
     * @var class-string<PunctuationPlugin>[]
     */
    private $punctuationPlugins = [
        SpaceBefore::class,
        MultiplePunctuation::class,
    ];

    /**
     * @var class-string<NodePlugin>[]
     */
    private $nodePlugin = [
        UnreferedPlugin::class,

    ];

    private $context;
    const INLINE_PLACEHOLDER = "\u{FFFC}";



    public function __construct(private $nodes = [], $context = '', $plugins = [])
    {

        $this->context = $context;

        if (!empty($plugins)) {
            foreach ($plugins as $plug) {

                if (!is_string($plug) || !class_exists($plug)) {
                    throw new PluginException("Plugin must be a valid class name");
                }

                if (is_subclass_of($plug, PunctuationPlugin::class)) {
                    $this->punctuationPlugins[] = $plug;
                    continue;
                }

                if (is_subclass_of($plug, NodePlugin::class)) {
                    $this->nodePlugin[] = $plug;
                    continue;
                }

                throw new PluginException("Cannot add plugin with class {$plug}");
            }
        }
    }

    static function make($nodes = [],  $context = '', $plugins = [],)
    {

        return (new Validator($nodes,  $context, $plugins));
    }
    public function check(?array $nodes = null, ?ValidatedResult $result = null): ValidatedResult
    {
        $result ??= new ValidatedResult;

        $nodes = $nodes ?? $this->nodes;

        foreach ($nodes as $index => $node) {
            if (!empty($this->nodePlugin)) {
                foreach ($this->nodePlugin as $plug) {
                    $this->makeNodePlugin($plug)->validate($node, $result, $this->context);
                }
            }


            if (in_array($node['type'], $this->isContainText(), true)) {
                $text = self::mergeText($node['content'] ?? []);
                $this->validatePunctuation($text, $result);
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
    static function mergeText(array $nodes): string
    {
        $result = '';

        foreach ($nodes as $n) {

            if (($n['type'] ?? null) === 'text') {
                $result .= $n['text'];
            } else  if (!empty($n['content']) && is_array($n['content'])) {
                $result .= self::mergeText($n['content']);
            } elseif (($n['type'] ?? null) == 'hardBreak') {
                $result .= "\n";
            } else {
                $result .= "{" . ($n['type'] ?? self::INLINE_PLACEHOLDER) . "}";
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

        // $text = str_replace(self::INLINE_PLACEHOLDER, '', $text);

        $puncError = null;


        if (!empty($this->punctuationPlugins)) {
            foreach ($this->punctuationPlugins as $plug) {
                $err = ($plug = $this->makePuncPlugin($plug))
                    ->validate($text);;
                if ($err) {
                    $puncError ??= new PunctuationError($text, $this->context);
                    $puncError->addErrorDesc(new PunctuationErrorResult($plug->getMessage(), $plug->getMatches()));
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
