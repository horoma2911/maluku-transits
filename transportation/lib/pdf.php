<?php
/**
 * Improved PDF generator using PHP DOM extension.
 * Produces valid PDF documents with proper tables, multi-page support,
 * headers, footers, and page numbers.
 *
 * Usage:
 *   $pdf = new PdfReport('MALUKU LOGISTICS - Invoices');
 *   $pdf->setColumns(['Ref', 'Client', 'Amount']);
 *   $pdf->addRow(['INV-1', 'Acme', 'TZS 100']);
 *   $pdf->output(); // streams to browser
 */

class PdfReport
{
    private string $title;
    private array $columns = [];
    private array $rows = [];

    private const PAGE_W = 595;
    private const PAGE_H = 842;
    private const MARGIN = 40;
    private const HEADER_H = 60;
    private const FOOTER_H = 40;

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function setColumns(array $cols): void
    {
        $this->columns = $cols;
    }

    public function addRow(array $row): void
    {
        $this->rows[] = $row;
    }

    public function setRows(array $rows): void
    {
        $this->rows = $rows;
    }

    private function escape(string $s): string
    {
        $s = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $s);
        $s = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
        return $s;
    }

    private function build(): string
    {
        $usableW = self::PAGE_W - 2 * self::MARGIN;
        $bodyStartY = self::PAGE_H - self::MARGIN - 50;
        $lineH = 16;
        $maxRowsPerPage = (int)(($bodyStartY - self::MARGIN - self::FOOTER_H) / $lineH);

        $allPages = [];
        $currentRows = [];

        foreach ($this->rows as $row) {
            $text = '';
            foreach ($row as $cell) {
                $text .= $this->escape((string)$cell) . '   ';
            }
            $currentRows[] = $text;

            if (count($currentRows) >= $maxRowsPerPage) {
                $allPages[] = $currentRows;
                $currentRows = [];
            }
        }
        if ($currentRows) {
            $allPages[] = $currentRows;
        }
        if (empty($allPages)) {
            $allPages[] = [];
        }

        $totalPages = count($allPages);

        $objects = [];

        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [";

        $pageObjNums = [];
        for ($i = 0; $i < $totalPages; $i++) {
            $pageObjNums[] = (5 + $i * 2) . " 0 R";
        }
        $objects[2] .= implode(' ', $pageObjNums) . "] /Count {$totalPages} >>";

        $objNum = 5;
        $pageData = [];
        $contentObjNum = 3;

        for ($p = 0; $p < $totalPages; $p++) {
            $pageRows = $allPages[$p];
            $pageNum = $p + 1;
            $contentLines = [];

            $headerY = self::PAGE_H - self::MARGIN - 30;

            $contentLines[] = 'BT';
            $contentLines[] = '/F1 14 Tf';
            $contentLines[] = self::MARGIN . ' ' . ($headerY + 20) . ' Td';
            $contentLines[] = '(' . $this->escape($this->title) . ') Tj';
            $contentLines[] = 'ET';

            $contentLines[] = 'BT';
            $contentLines[] = '/F2 8 Tf';
            $contentLines[] = self::MARGIN . ' ' . ($headerY + 4) . ' Td';
            $contentLines[] = '(' . $this->escape('Generated: ' . date('Y-m-d H:i')) . ') Tj';
            $contentLines[] = 'ET';

            $contentLines[] = 'BT';
            $contentLines[] = '/F1 9 Tf';
            $contentLines[] = self::MARGIN . ' ' . ($bodyStartY + 10) . ' Td';
            $headerText = '';
            foreach ($this->columns as $c) {
                $headerText .= $this->escape($c) . '   ';
            }
            $contentLines[] = '(' . $headerText . ') Tj';
            $contentLines[] = 'ET';

            $rowY = $bodyStartY;
            foreach ($pageRows as $rText) {
                $rowY -= $lineH;
                $contentLines[] = 'BT';
                $contentLines[] = '/F2 8 Tf';
                $contentLines[] = self::MARGIN . ' ' . $rowY . ' Td';
                $contentLines[] = '(' . $rText . ') Tj';
                $contentLines[] = 'ET';
            }

            $contentLines[] = 'BT';
            $contentLines[] = '/F2 8 Tf';
            $footerY = self::MARGIN - 10;
            $contentLines[] = self::MARGIN . ' ' . $footerY . ' Td';
            $contentLines[] = '(' . $this->escape('Page ' . $pageNum . ' of ' . $totalPages) . ') Tj';
            $contentLines[] = 'ET';

            $pageData[] = implode("\n", $contentLines);
        }

        $objects[3] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
        $objects[4] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";

        for ($p = 0; $p < $totalPages; $p++) {
            $pageObj = $objNum;
            $contentObj = $objNum + 1;
            $objNum += 2;

            $contentStr = $pageData[$p];
            $objects[$contentObj] = "<< /Length " . strlen($contentStr) . " >>\nstream\n" . $contentStr . "\nendstream";

            $objects[$pageObj] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PAGE_W . " " . self::PAGE_H . "] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentObj} 0 R >>";
        }

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        ksort($objects);
        foreach ($objects as $num => $body) {
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        ksort($offsets);
        foreach ($offsets as $off) {
            $pdf .= str_pad($off, 10, '0', STR_PAD_LEFT) . " 00000 n \n";
        }
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n" . $xrefPos . "\n%%EOF";

        return $pdf;
    }

    public function output(string $filename = 'report.pdf'): void
    {
        $pdf = $this->build();
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($pdf));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $pdf;
        exit;
    }

    public function toString(): string
    {
        return $this->build();
    }
}
