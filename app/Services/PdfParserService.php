<?php

namespace App\Services;

use Smalot\PdfParser\Parser;

class PdfParserService
{
    protected Parser $parser;

    public function __construct()
    {
        $this->parser = new Parser();
    }

    /**
     * Extrae todo el texto del PDF
     */
    public function extractText(string $pdfPath): string
    {
        $pdf = $this->parser->parseFile($pdfPath);
        return $pdf->getText();
    }

    /**
     * Extrae texto página por página
     */
    public function extractByPages(string $pdfPath): array
    {
        $pdf = $this->parser->parseFile($pdfPath);
        $pages = $pdf->getPages();

        $textByPage = [];
        foreach ($pages as $index => $page) {
            $textByPage[$index] = $page->getText();
        }

        return $textByPage;
    }
}
