<?php

namespace App\Services\Converter;

use App\Services\Converter\DTO\AltoPageData;
use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlToAltoConverter extends AbstractAltoConverter
{
    private const BLOCK_LEVEL_ELEMENTS = [
        'p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'li', 'blockquote', 'section', 'article',
    ];

    public function convert(string $html, AltoPageData $pageData): DOMDocument
    {
        $alto = $this->createBaseAltoDocument($pageData);

        $printSpace = $alto->getElementsByTagName('PrintSpace')->item(0);

        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        $body = $dom->getElementsByTagName('body')->item(0);

        if ($body) {
            $context = $this->createContext();
            $this->walkNode($body, $alto, $printSpace, $context);

            // flush any trailing content not closed by a block-level element
            $this->flushLine($alto, $context);
            $this->flushBlock($printSpace, $context);
        }

        return $alto;
    }

    private function walkNode(
        DOMNode $node,
        DOMDocument $alto,
        DOMElement $printSpace,
        array &$context,
    ): void {
        foreach ($node->childNodes as $child) {
            match ($child->nodeType) {
                XML_TEXT_NODE  => $this->handleTextNode($child->nodeValue, $alto, $context),
                XML_ELEMENT_NODE => $this->handleElementNode($child, $alto, $printSpace, $context),
                default => null,
            };
        }
    }

    private function handleElementNode(
        DOMElement $element,
        DOMDocument $alto,
        DOMElement $printSpace,
        array &$context,
    ): void {
        $tag = strtolower($element->nodeName);

        if ($tag === 'br') {
            $this->flushLine($alto, $context);
            return;
        }

        if (in_array($tag, self::BLOCK_LEVEL_ELEMENTS, true)) {
            $this->flushLine($alto, $context);
            $this->flushBlock($printSpace, $context);

            $this->walkNode($element, $alto, $printSpace, $context);

            $this->flushLine($alto, $context);
            $this->flushBlock($printSpace, $context);
            return;
        }

        // walk over inline elements
        $this->walkNode($element, $alto, $printSpace, $context);
    }

    private function handleTextNode(string $rawText, DOMDocument $alto, array &$context): void
    {
        $words = preg_split('/\s+/', trim($rawText), flags: PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return;
        }

        $this->ensureOpenBlock($alto, $context);
        $this->ensureOpenLine($alto, $context);

        foreach ($words as $word) {
            if ($context['currentLine']->hasChildNodes()) {
                $sp = $alto->createElement('SP');
                $sp->setAttribute('HPOS', '0');
                $sp->setAttribute('VPOS', '0');
                $sp->setAttribute('WIDTH', '0');
                $context['currentLine']->appendChild($sp);
            }

            $string = $alto->createElement('String');
            $string->setAttribute('ID', 'string_' . $context['stringNumber']++);
            $string->setAttribute('CONTENT', $word);
            $string->setAttribute('HPOS', '0');
            $string->setAttribute('VPOS', '0');
            $string->setAttribute('WIDTH', '0');
            $string->setAttribute('HEIGHT', '0');
            $context['currentLine']->appendChild($string);
        }
    }

    private function flushLine(DOMDocument $alto, array &$context): void
    {
        if ($context['currentLine'] === null || !$context['currentLine']->hasChildNodes()) {
            $context['currentLine'] = null;
            return;
        }

        $this->ensureOpenBlock($alto, $context);
        $context['currentBlock']->appendChild($context['currentLine']);
        $context['currentLine'] = null;
    }

    private function flushBlock(DOMElement $printSpace, array &$context): void
    {
        if ($context['currentBlock'] === null || !$context['currentBlock']->hasChildNodes()) {
            $context['currentBlock'] = null;
            return;
        }

        $printSpace->appendChild($context['currentBlock']);
        $context['currentBlock'] = null;
    }

    private function ensureOpenBlock(DOMDocument $alto, array &$context): void
    {
        if ($context['currentBlock'] !== null) {
            return;
        }

        $block = $alto->createElement('TextBlock');
        $block->setAttribute('ID', 'block_' . $context['blockNumber']++);
        $block->setAttribute('HPOS', '0');
        $block->setAttribute('VPOS', '0');
        $block->setAttribute('WIDTH', '0');
        $block->setAttribute('HEIGHT', '0');
        $context['currentBlock'] = $block;
    }

    private function ensureOpenLine(DOMDocument $alto, array &$context): void
    {
        if ($context['currentLine'] !== null) {
            return;
        }

        $line = $alto->createElement('TextLine');
        $line->setAttribute('ID', 'line_' . $context['lineNumber']++);
        $line->setAttribute('HPOS', '0');
        $line->setAttribute('VPOS', '0');
        $line->setAttribute('WIDTH', '0');
        $line->setAttribute('HEIGHT', '0');
        $context['currentLine'] = $line;
    }

    // initial state
    private function createContext(): array
    {
        return [
            'blockNumber'  => 1,
            'lineNumber'   => 1,
            'stringNumber' => 1,
            'currentBlock' => null,
            'currentLine'  => null,
        ];
    }
}
