<?php

namespace App\Exports;

use App\Models\Producto;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Font;

class ProductosExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected $productos;
    protected int   $totalStock       = 0;
    protected float $inventarioValor  = 0;
    protected int   $stockCritico     = 0;
    protected int   $stockAgotado     = 0;

    public function collection()
    {
        $this->productos = Producto::with('categoria')
            ->orderBy('idCategoria')
            ->orderBy('nombre')
            ->get();

        $this->totalStock      = (int) $this->productos->sum('stock');
        $this->inventarioValor = (float) $this->productos->sum(fn($p) => $p->precio * $p->stock);
        $this->stockCritico    = $this->productos->where('stock', '<=', 5)->where('stock', '>', 0)->count();
        $this->stockAgotado    = $this->productos->where('stock', 0)->count();

        return $this->productos->map(function ($p, $i) {
            $sku   = 'PRD-' . str_pad($p->idProducto, 5, '0', STR_PAD_LEFT);
            $valor = $p->precio * $p->stock;

            if ($p->stock == 0) {
                $estado = 'AGOTADO';
            } elseif ($p->stock <= 5) {
                $estado = 'CRÍTICO';
            } else {
                $estado = 'Disponible';
            }

            return [
                'N°'          => $i + 1,
                'SKU'         => $sku,
                'Nombre'      => $p->nombre,
                'Categoría'   => $p->categoria->nombre ?? '—',
                'Precio (Bs)' => (float) number_format($p->precio, 2, '.', ''),
                'Stock'       => (int) $p->stock,
                'Estado'      => $estado,
                'Valor Inv.'  => (float) number_format($valor, 2, '.', ''),
                'Registrado'  => $p->created_at ? $p->created_at->format('d/m/Y') : '—',
            ];
        });
    }

    public function headings(): array
    {
        return ['N°', 'SKU', 'Nombre del Producto', 'Categoría', 'Precio (Bs)', 'Stock', 'Estado Stock', 'Valor Inventario (Bs)', 'Fecha Registro'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row styling
            1 => [
                'font' => [
                    'bold'  => true,
                    'color' => ['argb' => 'FFFFFFFF'],
                    'size'  => 11,
                ],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E1B4B'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Inventario de Productos';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet   = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $dataEnd = $lastRow; // last data row index

                // ── Stripe alternating rows ──────────────────────────────────
                for ($row = 2; $row <= $dataEnd; $row++) {
                    $fill = ($row % 2 === 0) ? 'FFF8FAFC' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:I{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setARGB($fill);
                }

                // ── Color-code Estado column (G) ─────────────────────────────
                for ($row = 2; $row <= $dataEnd; $row++) {
                    $val = $sheet->getCell("G{$row}")->getValue();
                    if ($val === 'AGOTADO') {
                        $sheet->getStyle("G{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FFB91C1C']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEE2E2']],
                        ]);
                    } elseif ($val === 'CRÍTICO') {
                        $sheet->getStyle("G{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF92400E']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFEF3C7']],
                        ]);
                    } else {
                        $sheet->getStyle("G{$row}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['argb' => 'FF15803D']],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCFCE7']],
                        ]);
                    }
                }

                // ── Summary section (2 rows below data) ──────────────────────
                $summaryRow = $dataEnd + 2;

                $summaries = [
                    ['Total Productos',          $sheet->getHighestRow() - 1,               'FF1E40AF', 'FFDBEAFE'],
                    ['Total Stock (unidades)',   $this->totalStock,                          'FF15803D', 'FFD1FAE5'],
                    ['Valor Inventario (Bs)',    'Bs ' . number_format($this->inventarioValor, 2), 'FF92400E', 'FFFDE68A'],
                    ['Productos Críticos (≤5)',  $this->stockCritico,                        'FFB45309', 'FFFEF3C7'],
                    ['Productos Agotados',       $this->stockAgotado,                        'FFB91C1C', 'FFFEE2E2'],
                ];

                $col = 'A';
                foreach ($summaries as [$label, $value, $textColor, $bgColor]) {
                    $labelCol = $col;
                    $valCol   = chr(ord($col) + 1);

                    $sheet->setCellValue("{$labelCol}{$summaryRow}", $label);
                    $sheet->setCellValue("{$valCol}{$summaryRow}", $value);

                    $sheet->getStyle("{$labelCol}{$summaryRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => $textColor]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    ]);
                    $sheet->getStyle("{$valCol}{$summaryRow}")->applyFromArray([
                        'font' => ['bold' => true, 'color' => ['argb' => $textColor]],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    // Jump two columns for next pair
                    $col = chr(ord($valCol) + 2);
                    if ($col > 'I') break;
                }

                // ── Border on header ─────────────────────────────────────────
                $sheet->getStyle("A1:I1")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setARGB('FF4F46E5');

                // ── Row height on header ──────────────────────────────────────
                $sheet->getRowDimension(1)->setRowHeight(22);

                // ── Right-align numeric columns ───────────────────────────────
                $sheet->getStyle("E2:E{$dataEnd}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("F2:F{$dataEnd}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("H2:H{$dataEnd}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // ── Number format for price/value columns ─────────────────────
                $sheet->getStyle("E2:E{$dataEnd}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("H2:H{$dataEnd}")
                    ->getNumberFormat()->setFormatCode('#,##0.00');

                // ── Freeze header row ─────────────────────────────────────────
                $sheet->freezePane('A2');
            },
        ];
    }
}
