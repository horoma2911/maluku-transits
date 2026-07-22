<?php
/**
 * Professional PDF report generator (pure PHP, no external library).
 * Produces A4 reports with company header, styled tables, totals,
 * page numbers, and multi-page support.
 *
 * Usage:
 *   $pdf = new PdfReport('Expenses Report');
 *   $pdf->setColumns(['Ref', 'Category', 'Amount', 'Date', 'Trip', 'Status']);
 *   $pdf->addRow(['EXP-001', 'Fuel', '85000', '2024-07-15', 'TRP-001', 'approved']);
 *   $pdf->addTotal('Total', null, 'TZS 85,000');
 *   $pdf->output('expenses report.pdf');
 */

class PdfReport
{
    private string $title;
    private array $columns = [];
    private array $rows = [];
    private array $totals = [];
    private array $summary = [];

    private const PAGE_W = 595;
    private const PAGE_H = 842;
    private const MARGIN = 36;
    private const HEADER_H = 72;
    private const FOOTER_H = 36;
    private const ROW_H = 18;
    private const HEADER_ROW_H = 20;

    private float $usableW;
    private float $bodyStartY;
    private float $bodyEndY;
    private array $colWidths = [];
    private array $colAligns = [];

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

    public function addTotal(string $label, ?string $amountColKey, ?string $amountValue): void
    {
        $this->totals[] = ['label' => $label, 'amountColKey' => $amountColKey, 'amountValue' => $amountValue];
    }

    public function setSummary(array $summary): void
    {
        $this->summary = $summary;
    }

    private function escape(string $s): string
    {
        $s = preg_replace('/[^\x20-\x7E\xA0-\xFF]/', '', $s);
        $s = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
        return $s;
    }

    private function text(string $text, float $x, float $y, string $font = 'F2', float $size = 8): string
    {
        return "BT /{$font} {$size} Tf {$x} {$y} Td ({$this->escape($text)}) Tj ET";
    }

    private function rect(float $x, float $y, float $w, float $h, float $gray = 0.85): string
    {
        $r = number_format($gray, 2);
        return "q {$x} {$y} {$w} {$h} re {$r} g f Q";
    }

    private function line(float $x1, float $y1, float $x2, float $y2, float $gray = 0.5): string
    {
        return "q {$x1} {$y1} m {$x2} {$y2} l {$gray} G S Q";
    }

    private function calculateLayout(): void
    {
        $this->usableW = self::PAGE_W - 2 * self::MARGIN;
        $this->bodyStartY = self::PAGE_H - self::MARGIN - self::HEADER_H;
        $this->bodyEndY = self::MARGIN + self::FOOTER_H;

        $n = count($this->columns);
        if ($n === 0) return;

        $equalWidth = $this->usableW / $n;
        $this->colWidths = array_fill(0, $n, $equalWidth);
        $this->colAligns = array_fill(0, $n, 'left');

        $textAlignCols = ['Amount', 'Total', 'Share', 'Qty', 'Min', 'Price', 'Year', 'Trips', 'Total Trips'];
        foreach ($this->columns as $i => $col) {
            foreach ($textAlignCols as $ta) {
                if ($col === $ta) {
                    $this->colAligns[$i] = 'right';
                    break;
                }
            }
        }
    }

    private function paginate(): array
    {
        $maxRowsPerPage = (int)(($this->bodyStartY - $this->bodyEndY) / self::ROW_H);
        $allPages = [];
        $currentRows = [];

        foreach ($this->rows as $row) {
            $currentRows[] = $row;
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

        return $allPages;
    }

    private function drawHeader(float $pageNum, int $totalPages, string $dateRange): array
    {
        $lines = [];
        $x = self::MARGIN;
        $y = self::PAGE_H - self::MARGIN;

        $lines[] = 'q';
        $lines[] = $this->rect($x, $y - 28, $this->usableW, 28, 0.92);
        $lines[] = 'Q';

        $lines[] = $this->text('MALUKU LOGISTICS', $x + 8, $y - 10, 'F1', 13);
        $lines[] = $this->text('Transport & General Supply', $x + 8, $y - 22, 'F2', 8);

        $titleY = $y - 50;
        $lines[] = $this->text($this->title, $x, $titleY, 'F1', 11);

        if ($dateRange !== '') {
            $lines[] = $this->text('Period: ' . $dateRange, $x + 200, $titleY, 'F2', 8);
        }
        $lines[] = $this->text('Generated: ' . date('Y-m-d H:i'), $x + 200, $titleY - 12, 'F2', 8);

        $lineY = $this->bodyStartY + 14;
        $lines[] = $this->line($x, $lineY, $x + $this->usableW, $lineY, 0.75);

        $headerText = '';
        foreach ($this->columns as $c) {
            $headerText .= $this->escape($c) . '   ';
        }
        $lines[] = $this->text($headerText, $x, $lineY - 10, 'F1', 8);

        $lines[] = $this->line($x, $lineY - 14, $x + $this->usableW, $lineY - 14, 0.75);

        return $lines;
    }

    private function drawFooter(float $pageNum, int $totalPages): array
    {
        $lines = [];
        $y = self::MARGIN - 16;
        $x = self::MARGIN;

        $lines[] = $this->line($x, $y + 12, $x + $this->usableW, $y + 12, 0.75);
        $lines[] = $this->text('MALUKU LOGISTICS - Confidential', $x, $y, 'F2', 7);
        $lines[] = $this->text('Page ' . $pageNum . ' of ' . $totalPages, $x + $this->usableW - 60, $y, 'F2', 7);

        return $lines;
    }

    private function drawRow(float $x, float $y, array $row, bool $isAlt, int $startCol): array
    {
        $lines = [];
        $cellX = $x;
        $text = '';

        for ($i = $startCol; $i < count($this->columns); $i++) {
            $cell = $row[$i] ?? '';
            $align = $this->colAligns[$i];
            $cw = $this->colWidths[$i];

            if ($isAlt) {
                $lines[] = $this->rect($cellX, $y - self::ROW_H + 2, $cw, self::ROW_H - 1, 0.96);
            }

            $display = is_array($cell) ? ($cell['value'] ?? '') : $cell;
            $display = $this->escape((string)$display);

            if ($align === 'right') {
                $text .= $this->text($display, $cellX + $cw - 4, $y - 11, 'F2', 7);
                $text .= ' B Tj';
            } else {
                $text .= $this->text($display, $cellX + 4, $y - 11, 'F2', 7);
            }

            $cellX += $cw;
        }

        $lines[] = $this->line($x, $y, $x + $this->usableW, $y, 0.25);
        return [$lines, $text];
    }

    private function drawTotalRow(float $x, float $y, array $total): array
    {
        $lines = [];
        $label = $this->escape($total['label']);
        $amount = $this->escape($total['amountValue'] ?? '');
        $colIdx = $total['amountColKey'] !== null ? array_search($total['amountColKey'], $this->columns, true) : count($this->columns) - 1;
        if ($colIdx === false) $colIdx = count($this->columns) - 1;

        $lines[] = $this->rect($x, $y - self::ROW_H + 2, $this->usableW, self::ROW_H - 1, 0.82);

        $labelX = $x + 4;
        $lines[] = $this->text($label, $labelX, $y - 11, 'F1', 8);

        $amountX = $x;
        for ($i = 0; $i <= $colIdx && $i < count($this->columns); $i++) {
            $amountX += $this->colWidths[$i];
        }
        $amountX -= ($this->colWidths[$colIdx] ?? 0);
        $lines[] = $this->text($amount, $amountX + ($this->colWidths[$colIdx] ?? 0) - 4, $y - 11, 'F1', 8);

        $lines[] = $this->line($x, $y, $x + $this->usableW, $y, 0.5);
        return $lines;
    }

    private function drawSummaryBox(float $x, float $y, array $summary): array
    {
        $lines = [];
        $boxW = $this->usableW;
        $lineH = 14;
        $boxH = count($summary) * $lineH + 14;

        $lines[] = 'q';
        $lines[] = $this->rect($x, $y - $boxH + 4, $boxW, $boxH, 0.94);
        $lines[] = 'Q';
        $lines[] = $this->line($x, $y - $boxH + 4, $x, $y + 4, 0.5);
        $lines[] = $this->line($x + $boxW, $y - $boxH + 4, $x + $boxW, $y + 4, 0.5);
        $lines[] = $this->line($x, $y + 4, $x + $boxW, $y + 4, 0.5);

        $lines[] = $this->text('Summary', $x + 8, $y - 6, 'F1', 9);
        $lines[] = $this->line($x, $y - 8, $x + $boxW, $y - 8, 0.4);

        $itemY = $y - 18;
        foreach ($summary as $item) {
            $lines[] = $this->text($this->escape($item['label'] ?? ''), $x + 8, $itemY, 'F2', 8);
            $val = $this->escape($item['value'] ?? '');
            $lines[] = $this->text($val, $x + $boxW - 60, $itemY, 'F2', 8);
            $itemY -= $lineH;
        }

        return [$lines, $boxH + 8];
    }

    public function build(): string
    {
        $this->calculateLayout();
        $allPages = $this->paginate();
        $totalPages = count($allPages);

        $objects = [];
        $objects[1] = "<< /Type /Catalog /Pages 2 0 R >>";
        $objects[2] = "<< /Type /Pages /Kids [";

        $pageObjNums = [];
        for ($i = 0; $i < $totalPages; $i++) {
            $pageObjNums[] = (5 + $i * 2) . " 0 R";
        }
        $objects[2] .= implode(' ', $pageObjNums) . "] /Count {$totalPages} >>";

        $fonts = [
            3 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>",
            4 => "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>",
        ];

        $x = self::MARGIN;
        $objNum = 5;

        for ($p = 0; $p < $totalPages; $p++) {
            $pageRows = $allPages[$p];
            $pageNum = $p + 1;
            $dateRange = '';

            if (!empty($_GET['from']) || !empty($_GET['to'])) {
                $from = clean_string($_GET['from'] ?? '');
                $to = clean_string($_GET['to'] ?? '');
                $dateRange = ($from ? $from : '...') . ' to ' . ($to ? $to : '...');
            }

            $headerLines = $this->drawHeader($pageNum, $totalPages, $dateRange);
            $footerLines = $this->drawFooter($pageNum, $totalPages);

            if (isset($_GET['from']) || isset($_GET['to'])) {
                $from = clean_string($_GET['from'] ?? '');
                $to = clean_string($_GET['to'] ?? '');
                $dateRange = ($from ? $from : '...') . ' to ' . ($to ? $to : '...');
            } else {
                $dateRange = 'All dates';
            }

            $summaryHeight = 0;
            $summaryLines = [];
            if (!empty($this->summary) && $p === $totalPages - 1) {
                $summaryBottom = self::MARGIN + self::FOOTER_H + 20;
                [$summaryLines, $summaryHeight] = $this->drawSummaryBox($x, $summaryBottom, $this->summary);
            }

            $contentLines = array_merge($headerLines, $footerLines, $summaryLines);

            $gridY = $this->bodyStartY - 4;
            foreach ($pageRows as $idx => $row) {
                $isAlt = ($idx % 2 === 1);
                [$rowLines] = $this->drawRow($x, $gridY, $row, $isAlt, 0);
                $contentLines = array_merge($contentLines, $rowLines);
                $gridY -= self::ROW_H;
            }

            foreach ($this->totals as $total) {
                $totalLines = $this->drawTotalRow($x, $gridY, $total);
                $contentLines = array_merge($contentLines, $totalLines);
                $gridY -= self::ROW_H;
            }

            $contentStr = implode("\n", $contentLines);
            $contentObj = $objNum + 1;

            $objects[$contentObj] = "<< /Length " . strlen($contentStr) . " >>\nstream\n" . $contentStr . "\nendstream";
            $objects[$objNum] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 " . self::PAGE_W . " " . self::PAGE_H . "] /Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents {$contentObj} 0 R >>";

            $objNum += 2;
        }

        $objects = array_merge($fonts, $objects);

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
