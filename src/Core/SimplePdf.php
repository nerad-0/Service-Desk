<?php

declare(strict_types=1);

namespace App\Core;

class SimplePdf
{
    private const WIDTH = 595;
    private const HEIGHT = 842;
    private const MARGIN_X = 48;
    private const START_Y = 792;
    private const MIN_Y = 54;

    public static function create(string $title, array $sections): string
    {
        $items = [
            ['text' => $title, 'font' => 'F2', 'size' => 16, 'space' => 10],
        ];

        foreach ($sections as $section) {
            $heading = (string)($section['heading'] ?? '');
            if ($heading !== '') {
                $items[] = ['text' => $heading, 'font' => 'F2', 'size' => 12, 'space' => 5];
            }

            foreach ($section['lines'] ?? [] as $line) {
                $text = trim((string)$line);
                if ($text === '') {
                    $items[] = ['text' => '', 'font' => 'F1', 'size' => 10, 'space' => 4];
                    continue;
                }

                foreach (self::wrap($text, 92) as $wrapped) {
                    $items[] = ['text' => $wrapped, 'font' => 'F1', 'size' => 10, 'space' => 2];
                }
            }

            $items[] = ['text' => '', 'font' => 'F1', 'size' => 10, 'space' => 7];
        }

        return self::render($items);
    }

    private static function render(array $items): string
    {
        $pages = [];
        $current = [];
        $y = self::START_Y;

        foreach ($items as $item) {
            $lineHeight = (int)ceil(((int)$item['size']) * 1.35) + (int)$item['space'];
            if ($y - $lineHeight < self::MIN_Y && $current !== []) {
                $pages[] = $current;
                $current = [];
                $y = self::START_Y;
            }

            if ((string)$item['text'] !== '') {
                $current[] = [
                    'text' => self::ascii((string)$item['text']),
                    'font' => (string)$item['font'],
                    'size' => (int)$item['size'],
                    'x' => self::MARGIN_X,
                    'y' => $y,
                ];
            }

            $y -= $lineHeight;
        }

        if ($current !== []) {
            $pages[] = $current;
        }

        return self::buildPdf($pages);
    }

    private static function buildPdf(array $pages): string
    {
        $objects = [];
        $fontRegularId = self::addObject($objects, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>');
        $fontBoldId = self::addObject($objects, '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>');
        $pageIds = [];

        foreach ($pages as $pageLines) {
            $stream = self::pageStream($pageLines);
            $contentId = self::addObject($objects, "<< /Length " . strlen($stream) . " >>\nstream\n" . $stream . "\nendstream");
            $pageIds[] = self::addObject(
                $objects,
                '<< /Type /Page /Parent PAGES_ID 0 R /MediaBox [0 0 ' . self::WIDTH . ' ' . self::HEIGHT . '] '
                . '/Resources << /Font << /F1 ' . $fontRegularId . ' 0 R /F2 ' . $fontBoldId . ' 0 R >> >> '
                . '/Contents ' . $contentId . ' 0 R >>'
            );
        }

        $pagesId = self::addObject(
            $objects,
            '<< /Type /Pages /Kids [' . implode(' ', array_map(fn (int $id): string => $id . ' 0 R', $pageIds)) . '] /Count ' . count($pageIds) . ' >>'
        );

        foreach ($pageIds as $pageId) {
            $objects[$pageId] = str_replace('PAGES_ID', (string)$pagesId, $objects[$pageId]);
        }

        $catalogId = self::addObject($objects, '<< /Type /Catalog /Pages ' . $pagesId . ' 0 R >>');

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $body) {
            $offsets[$id] = strlen($pdf);
            $pdf .= $id . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= count($objects); $id += 1) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$id]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . ' /Root ' . $catalogId . " 0 R >>\n";
        $pdf .= "startxref\n" . $xrefOffset . "\n%%EOF";

        return $pdf;
    }

    private static function pageStream(array $lines): string
    {
        $commands = [];

        foreach ($lines as $line) {
            $commands[] = 'BT';
            $commands[] = '/' . $line['font'] . ' ' . $line['size'] . ' Tf';
            $commands[] = '1 0 0 1 ' . $line['x'] . ' ' . $line['y'] . ' Tm';
            $commands[] = self::pdfString($line['text']) . ' Tj';
            $commands[] = 'ET';
        }

        return implode("\n", $commands);
    }

    private static function addObject(array &$objects, string $body): int
    {
        $id = count($objects) + 1;
        $objects[$id] = $body;
        return $id;
    }

    private static function wrap(string $text, int $width): array
    {
        $text = self::ascii($text);
        return explode("\n", wordwrap($text, $width, "\n", true));
    }

    private static function pdfString(string $text): string
    {
        return '(' . str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text) . ')';
    }

    private static function ascii(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = strtr($text, self::transliterationMap());
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) {
                $text = $converted;
            }
        }

        return trim((string)preg_replace('/[^\x09\x0A\x0D\x20-\x7E]/', '', $text));
    }

    private static function transliterationMap(): array
    {
        return [
            'á' => 'a', 'č' => 'c', 'ď' => 'd', 'é' => 'e', 'ě' => 'e', 'í' => 'i',
            'ň' => 'n', 'ó' => 'o', 'ř' => 'r', 'š' => 's', 'ť' => 't', 'ú' => 'u',
            'ů' => 'u', 'ý' => 'y', 'ž' => 'z',
            'Á' => 'A', 'Č' => 'C', 'Ď' => 'D', 'É' => 'E', 'Ě' => 'E', 'Í' => 'I',
            'Ň' => 'N', 'Ó' => 'O', 'Ř' => 'R', 'Š' => 'S', 'Ť' => 'T', 'Ú' => 'U',
            'Ů' => 'U', 'Ý' => 'Y', 'Ž' => 'Z',
        ];
    }
}
