<?php

namespace App\Services\Jira;

use DOMDocument;
use DOMElement;
use DOMNode;

class JiraDescriptionService
{
    /**
     * Convert Quill HTML into Jira ADF.
     */
    public function fromQuill(string $html): array
    {
        $html = trim($html);

        if ($html === '') {
            return [
                'type' => 'doc',
                'version' => 1,
                'content' => [],
            ];
        }

        $dom = new DOMDocument();

        libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8">' . $html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        $content = [];

        foreach ($dom->childNodes as $node) {
            $this->parseBlock($node, $content);
        }

        return [
            'type' => 'doc',
            'version' => 1,
            'content' => $content,
        ];
    }

    /**
     * Parse block-level HTML elements.
     */
    protected function parseBlock(DOMNode $node, array &$content): void
    {
        if ($node->nodeType === XML_TEXT_NODE) {

            $text = trim($node->textContent);

            if ($text !== '') {
                $content[] = $this->paragraph([
                    $this->text($text)
                ]);
            }

            return;
        }

        if (!$node instanceof DOMElement) {
            return;
        }

        $tag = strtolower($node->tagName);

        switch ($tag) {

            case 'p':
                $content[] = $this->paragraph(
                    $this->parseInline($node)
                );
                break;

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':

                $content[] = [
                    'type' => 'heading',
                    'attrs' => [
                        'level' => (int) substr($tag, 1),
                    ],
                    'content' => $this->parseInline($node),
                ];

                break;

            case 'blockquote':

                $content[] = [
                    'type' => 'blockquote',
                    'content' => [
                        $this->paragraph(
                            $this->parseInline($node)
                        ),
                    ],
                ];

                break;

            case 'pre':

                $content[] = [
                    'type' => 'codeBlock',
                    'attrs' => [
                        'language' => null,
                    ],
                    'content' => [
                        [
                            'type' => 'text',
                            'text' => $node->textContent,
                        ],
                    ],
                ];

                break;

            case 'ul':
                $content[] = $this->parseList($node, 'bulletList');
                break;

            case 'ol':
                $content[] = $this->parseList($node, 'orderedList');
                break;

            case 'br':

                $content[] = $this->paragraph([
                    $this->text('')
                ]);

                break;

            case 'div':

                foreach ($node->childNodes as $child) {
                    $this->parseBlock($child, $content);
                }

                break;

            default:

                $inline = $this->parseInline($node);

                if (!empty($inline)) {
                    $content[] = $this->paragraph($inline);
                }

                break;
        }
    }

    /**
     * Parse inline formatting.
     */
    protected function parseInline(
        DOMNode $node,
        array $marks = []
    ): array {

        $result = [];

        if ($node->nodeType === XML_TEXT_NODE) {

            $text = $node->textContent;

            if ($text === '') {
                return [];
            }

            $item = [
                'type' => 'text',
                'text' => $text,
            ];

            if (!empty($marks)) {
                $item['marks'] = $marks;
            }

            return [$item];
        }

        if (!$node instanceof DOMElement) {
            return [];
        }

        $tag = strtolower($node->tagName);

        $newMarks = $marks;

        switch ($tag) {

            case 'strong':
            case 'b':

                $newMarks[] = [
                    'type' => 'strong'
                ];

                break;

            case 'em':
            case 'i':

                $newMarks[] = [
                    'type' => 'em'
                ];

                break;

            case 'u':

                $newMarks[] = [
                    'type' => 'underline'
                ];

                break;

            case 's':
            case 'strike':
            case 'del':

                $newMarks[] = [
                    'type' => 'strike'
                ];

                break;

            case 'code':

                $newMarks[] = [
                    'type' => 'code'
                ];

                break;

            case 'a':

                $href = $node->getAttribute('href');

                if ($href !== '') {
                    $newMarks[] = [
                        'type' => 'link',
                        'attrs' => [
                            'href' => $href,
                        ],
                    ];
                }

                break;
        }

        foreach ($node->childNodes as $child) {

            $children = $this->parseInline(
                $child,
                $newMarks
            );

            foreach ($children as $childContent) {
                $result[] = $childContent;
            }
        }

        return $result;
    }

    /**
     * Convert UL/OL.
     */
    protected function parseList(
        DOMElement $node,
        string $type
    ): array {

        $items = [];

        foreach ($node->childNodes as $child) {

            if (
                !$child instanceof DOMElement ||
                strtolower($child->tagName) !== 'li'
            ) {
                continue;
            }

            $items[] = [
                'type' => 'listItem',
                'content' => [
                    $this->paragraph(
                        $this->parseInline($child)
                    ),
                ],
            ];
        }

        return [
            'type' => $type,
            'content' => $items,
        ];
    }

    /**
     * Create paragraph node.
     */
    protected function paragraph(array $content): array
    {
        if (empty($content)) {
            $content = [
                $this->text('')
            ];
        }

        return [
            'type' => 'paragraph',
            'content' => $content,
        ];
    }

    /**
     * Create text node.
     */
    protected function text(string $text): array
    {
        return [
            'type' => 'text',
            'text' => $text,
        ];
    }
}