<?php

namespace App\Services\Converter;

;

use App\Services\Converter\DTO\PageXmlPageData;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

class HtmlToPageXmlConverter extends AbstractPageXmlConverter
{
    private const BLOCK_TAGS = [
        'p', 'div', 'section', 'article', 'blockquote',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'ul', 'ol', 'table',
    ];

    public function convert(string $html, PageXmlPageData $pageData): DOMDocument
    {
        $page = $this->createBasePageDocument($pageData);
        $pageElement = $page->getElementsByTagName('Page')->item(0);

        if (!$pageElement instanceof DOMElement) {
            return $page;
        }

        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><!DOCTYPE html><html><body>' . $html . '</body></html>',
        );
        libxml_clear_errors();

        $body = $dom->getElementsByTagName('body')->item(0);

        if (!$body instanceof DOMElement) {
            return $page;
        }

        $context = $this->createContext($pageData);
        $blocks = $this->collectBlocks($body);

        foreach ($blocks as $block) {
            $region = $this->createRegionFromBlock($page, $block, $context);

            if ($region instanceof DOMElement) {
                $pageElement->appendChild($region);
            }
        }

        $this->appendReadingOrder($page, $pageElement, $context['readingOrder']);

        return $page;
    }

    private function collectBlocks(DOMElement $root): array
    {
        $blocks = [];

        foreach ($root->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = $this->normalizeWhitespace($child->nodeValue ?? '');
                if ($text !== '') {
                    $blocks[] = [
                        'tag' => 'p',
                        'type' => 'paragraph',
                        'node' => null,
                        'text' => $text,
                        'lines' => [['plain' => $text, 'custom' => []]],
                        'custom' => null,
                    ];
                }
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::BLOCK_TAGS, true)) {
                $blocks[] = $this->mapBlock($child);
                continue;
            }

            $text = $this->normalizeWhitespace($child->textContent ?? '');
            if ($text === '') {
                continue;
            }

            $blocks[] = [
                'tag' => $tag,
                'type' => 'paragraph',
                'node' => $child,
                'text' => $text,
                'lines' => [['plain' => $text, 'custom' => []]],
                'custom' => [
                    'sourceTag' => $tag,
                ],
            ];
        }

        return $blocks;
    }

    private function mapBlock(DOMElement $element): array
    {
        $tag = strtolower($element->tagName);

        return match ($tag) {
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6' => $this->mapHeadingBlock($element, $tag),
            'ul', 'ol' => $this->mapListBlock($element, $tag),
            'table' => $this->mapTableBlock($element),
            default => $this->mapParagraphBlock($element, $tag),
        };
    }

    private function mapHeadingBlock(DOMElement $element, string $tag): array
    {
        $line = $this->buildAnnotatedLine($element);

        return [
            'tag' => $tag,
            'type' => 'heading',
            'node' => $element,
            'text' => $line['plain'],
            'lines' => [$line],
            'custom' => [
                'sourceTag' => $tag,
                'htmlStructure' => 'heading',
                'level' => (int) substr($tag, 1),
            ],
        ];
    }

    private function mapParagraphBlock(DOMElement $element, string $tag): array
    {
        $lines = $this->buildAnnotatedLinesFromNode($element);
        $plain = implode("\n", array_map(static fn(array $line): string => $line['plain'], $lines));
        $custom = [
            'sourceTag' => $tag,
            'htmlStructure' => 'paragraph',
        ];

        $alignment = $this->extractTextAlign($element);
        if ($alignment !== null) {
            $custom['align'] = $alignment;
        }

        return [
            'tag' => $tag,
            'type' => 'paragraph',
            'node' => $element,
            'text' => $plain,
            'lines' => $lines,
            'custom' => $custom,
        ];
    }

    private function mapListBlock(DOMElement $element, string $tag): array
    {
        $lines = [];
        $index = 1;

        foreach ($element->childNodes as $child) {
            if (!$child instanceof DOMElement || strtolower($child->tagName) !== 'li') {
                continue;
            }

            $line = $this->buildAnnotatedLine($child);
            $prefix = $tag === 'ol' ? $index . '. ' : '• ';
            $line = $this->prependToLine($line, $prefix);
            $line['custom']['listItem'] = true;
            $line['custom']['listType'] = $tag;
            $line['custom']['itemIndex'] = $index;
            $lines[] = $line;
            $index++;
        }

        return [
            'tag' => $tag,
            'type' => 'list',
            'node' => $element,
            'text' => implode("\n", array_map(static fn(array $line): string => $line['plain'], $lines)),
            'lines' => $lines,
            'custom' => [
                'sourceTag' => $tag,
                'htmlStructure' => 'list',
                'listType' => $tag,
            ],
        ];
    }

    private function mapTableBlock(DOMElement $element): array
    {
        $xpath = new DOMXPath($element->ownerDocument);
        $rows = $xpath->query('.//tr', $element);
        $lines = [];
        $rowIndex = 1;

        foreach ($rows as $row) {
            if (!$row instanceof DOMElement) {
                continue;
            }

            $cells = [];
            foreach ($row->childNodes as $cell) {
                if (!$cell instanceof DOMElement) {
                    continue;
                }

                $cellTag = strtolower($cell->tagName);
                if (!in_array($cellTag, ['td', 'th'], true)) {
                    continue;
                }

                $cells[] = $this->normalizeWhitespace($cell->textContent ?? '');
            }

            $plain = implode(' | ', array_filter($cells, static fn(string $value): bool => $value !== ''));
            if ($plain === '') {
                continue;
            }

            $lines[] = [
                'plain' => $plain,
                'custom' => [
                    'rowIndex' => $rowIndex,
                    'cells' => $cells,
                ],
            ];
            $rowIndex++;
        }

        return [
            'tag' => 'table',
            'type' => 'table',
            'node' => $element,
            'text' => implode("\n", array_map(static fn(array $line): string => $line['plain'], $lines)),
            'lines' => $lines,
            'custom' => [
                'sourceTag' => 'table',
                'htmlStructure' => 'table',
            ],
        ];
    }

    private function createRegionFromBlock(DOMDocument $page, array $block, array &$context): ?DOMElement
    {
        if (empty($block['lines'])) {
            return null;
        }

        $regionName = $block['type'] === 'table' ? 'TableRegion' : 'TextRegion';
        $region = $page->createElement($regionName);
        $regionId = 'region_' . $context['regionNumber']++;
        $region->setAttribute('id', $regionId);

        if ($regionName === 'TextRegion') {
            $region->setAttribute('type', $block['type']);
        }

        $lineCount = count($block['lines']);
        $top = $context['currentY'];
        $height = max($context['regionPadding'] + ($lineCount * $context['lineHeight']), $context['lineHeight']);
        $bottom = min($context['pageHeight'], $top + $height);

        $region->appendChild($this->createCoords($page, 0, $top, $context['pageWidth'], $bottom));

        foreach ($block['lines'] as $lineData) {
            $line = $page->createElement('TextLine');
            $lineId = 'line_' . $context['lineNumber']++;
            $line->setAttribute('id', $lineId);

            $lineTop = $context['currentY'];
            $lineBottom = min($context['pageHeight'], $lineTop + $context['lineHeight']);
            $line->appendChild($this->createCoords($page, 0, $lineTop, $context['pageWidth'], $lineBottom));
            $line->appendChild($this->createTextEquiv($page, $lineData['plain']));

            $lineCustom = $lineData['custom'] ?? null;
            if (is_array($lineCustom) && $lineCustom !== []) {
                $line->appendChild(
                    $page->createElement(
                        'Custom',
                        json_encode($lineCustom, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    ),
                );
            }

            $region->appendChild($line);
            $context['currentY'] = min($context['pageHeight'], $lineBottom);
        }

        if (is_array($block['custom']) && $block['custom'] !== []) {
            $region->appendChild(
                $page->createElement(
                    'Custom',
                    json_encode($block['custom'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ),
            );
        }

        $context['readingOrder'][] = $regionId;
        $context['currentY'] = min($context['pageHeight'], $bottom + $context['blockGap']);

        return $region;
    }

    private function buildAnnotatedLinesFromNode(DOMElement $element): array
    {
        $segments = $this->collectSegments($element);
        $lines = [];
        $buffer = [];

        foreach ($segments as $segment) {
            if (($segment['break'] ?? false) === true) {
                $lines[] = $this->finalizeLineSegments($buffer);
                $buffer = [];
                continue;
            }

            $buffer[] = $segment;
        }

        $lines[] = $this->finalizeLineSegments($buffer);

        return array_values(array_filter($lines, static fn(array $line): bool => $line['plain'] !== ''));
    }

    private function buildAnnotatedLine(DOMElement $element): array
    {
        $lines = $this->buildAnnotatedLinesFromNode($element);

        if ($lines === []) {
            return ['plain' => '', 'custom' => []];
        }

        if (count($lines) === 1) {
            return $lines[0];
        }

        return [
            'plain' => implode(' ', array_map(static fn(array $line): string => $line['plain'], $lines)),
            'custom' => [
                'fragments' => array_map(static fn(array $line): array => [
                    'plain' => $line['plain'],
                    'custom' => $line['custom'],
                ], $lines),
            ],
        ];
    }

    private function collectSegments(DOMNode $node, array $style = [], array $flags = []): array
    {
        $segments = [];

        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text = html_entity_decode($child->nodeValue ?? '', ENT_QUOTES | ENT_XML1, 'UTF-8');
                if ($text !== '') {
                    $segments[] = [
                        'text' => $text,
                        'style' => $style,
                        'flags' => $flags,
                    ];
                }
                continue;
            }

            if (!$child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if ($tag === 'br') {
                $segments[] = ['break' => true];
                continue;
            }

            if ($tag === 'img' && str_contains($child->getAttribute('class'), 'tct_missing')) {
                $segments[] = [
                    'text' => '⟦missing⟧',
                    'style' => $style,
                    'flags' => [...$flags, 'missing' => true],
                ];
                continue;
            }

            [$childStyle, $childFlags] = $this->deriveStyleAndFlags($child, $style, $flags);
            $segments = [...$segments, ...$this->collectSegments($child, $childStyle, $childFlags)];
        }

        return $segments;
    }

    private function deriveStyleAndFlags(DOMElement $element, array $style, array $flags): array
    {
        $tag = strtolower($element->tagName);
        $childStyle = $style;
        $childFlags = $flags;

        if (in_array($tag, ['strong', 'b'], true)) {
            $childStyle['bold'] = true;
        }

        if (in_array($tag, ['em', 'i'], true)) {
            $childStyle['italic'] = true;
        }

        if ($tag === 'sup') {
            $childFlags['superscript'] = true;
        }

        $inlineStyle = strtolower($element->getAttribute('style'));
        if (str_contains($inlineStyle, 'text-decoration: underline')) {
            $childStyle['underline'] = true;
        }

        $class = $element->getAttribute('class');
        if (str_contains($class, 'tct-uncertain')) {
            $childFlags['uncertain'] = true;
        }

        if ($class !== '') {
            $childFlags['class'] = $class;
        }

        return [$childStyle, $childFlags];
    }

    private function finalizeLineSegments(array $segments): array
    {
        $plain = '';
        $spans = [];

        foreach ($segments as $segment) {
            $text = $segment['text'] ?? '';
            if ($text === '') {
                continue;
            }

            $normalized = $this->normalizeSegmentText($text, $plain === '');
            if ($normalized === '') {
                continue;
            }

            $from = mb_strlen($plain, 'UTF-8');
            $plain .= $normalized;
            $to = mb_strlen($plain, 'UTF-8');

            $style = $segment['style'] ?? [];
            $flags = $segment['flags'] ?? [];

            if ($style !== [] || $flags !== []) {
                $entry = ['from' => $from, 'to' => $to];
                foreach ($style as $key => $value) {
                    if ($value) {
                        $entry[$key] = true;
                    }
                }
                foreach ($flags as $key => $value) {
                    if ($value !== null && $value !== false && $value !== '') {
                        $entry[$key] = $value;
                    }
                }
                $spans[] = $entry;
            }
        }

        $plain = trim(preg_replace('/\s+/u', ' ', $plain) ?? '');

        return [
            'plain' => $plain,
            'custom' => $spans === [] ? [] : ['spans' => $spans],
        ];
    }

    private function prependToLine(array $line, string $prefix): array
    {
        $prefixLength = mb_strlen($prefix, 'UTF-8');
        $plain = $prefix . $line['plain'];
        $custom = $line['custom'] ?? [];

        if (isset($custom['spans']) && is_array($custom['spans'])) {
            $custom['spans'] = array_map(
                static fn(array $span): array => [
                    ...$span,
                    'from' => ($span['from'] ?? 0) + $prefixLength,
                    'to' => ($span['to'] ?? 0) + $prefixLength,
                ],
                $custom['spans'],
            );
        }

        return [
            'plain' => $plain,
            'custom' => $custom,
        ];
    }

    private function extractTextAlign(DOMElement $element): ?string
    {
        $style = strtolower($element->getAttribute('style'));

        return match (true) {
            str_contains($style, 'text-align: right') => 'right',
            str_contains($style, 'text-align: center') => 'center',
            str_contains($style, 'text-align: left') => 'left',
            default => null,
        };
    }

    private function normalizeSegmentText(string $text, bool $isLineStart): string
    {
        $text = preg_replace('/\s+/u', ' ', $text) ?? '';
        if ($isLineStart) {
            return ltrim($text);
        }

        return $text;
    }

    private function normalizeWhitespace(string $text): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_XML1, 'UTF-8')) ?? '');
    }

    private function createContext(PageXmlPageData $pageData): array
    {
        return [
            'regionNumber' => 1,
            'lineNumber' => 1,
            'pageWidth' => max((int) $pageData->width, 1000),
            'pageHeight' => max((int) $pageData->height, 2000),
            'lineHeight' => 80,
            'regionPadding' => 20,
            'blockGap' => 20,
            'currentY' => 0,
            'readingOrder' => [],
        ];
    }
}
