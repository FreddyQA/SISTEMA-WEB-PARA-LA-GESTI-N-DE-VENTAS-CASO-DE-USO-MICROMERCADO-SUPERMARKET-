<?php

namespace App\Exports;

use App\Models\Pedido;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VentasExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithTitle,
    ShouldAutoSize,
    WithEvents
{
    protected ?string $inicio;
    protected ?string $fin;
    protected ?int    $idCliente;
    protected ?string $estado;
    protected float   $totalGeneral = 0;

    public function __construct(?string $inicio = null, ?string $fin = null, ?int $idCliente = null, ?string $estado = null)
    {
        $this->inicio    = $inicio;
        $this->fin       = $fin;
        $this->idCliente = $idCliente;
        $this->estado    = $estado;
    }

    public function collection()
    {
        $query = Pedido::with(['cliente', 'detalles.producto']);

        if ($this->inicio && $this->fin) {
            $query->whereBetween('fecha', [$this->inicio, $this->fin]);
        } elseif ($this->inicio) {
            $query->where('fecha', '>=', $this->inicio);
        } elseif ($this->fin) {
            $query->where('fecha', '<=', $this->fin);
        }

        if ($this->idCliente) {
            $query->where('idCliente', $this->idCliente);
        }

        if ($this->estado && $this->estado !== 'todos') {
            $query->where('estado', $this->estado);
        }

        $pedidos = $query->orderBy('fecha', 'desc')->get();

        $this->totalGeneral = (float) $pedidos->where('estado', '!=', 'anulado')->sum('total');

        return $pedidos->map(function ($p, $i) {
            return [
                'N°'        => $i + 1,
                'ID Pedido' => '#' . $p->idPedido,
                'Cliente'   => $p->cliente->nombre ?? '—',
                'Productos' => $p->detalles->map(fn ($d) => ($d->producto->nombre ?? 'Producto eliminado') . " (x{$d->cantidad})")->implode(', '),
                'Cantidad'  => $p->detalles->sum('cantidad'),
                'Estado'    => ucfirst($p->estado ?? 'completado'),
                'Total'     => 'Bs ' . number_format($p->total, 2),
                'Fecha'     => $p->fecha ? \Carbon\Carbon::parse($p->fecha)->format('d/m/Y') : '—',
            ];
        });
    }

    public function headings(): array
    {
        return ['N°', 'ID Pedido', 'Cliente', 'Productos', 'Cant. Total', 'Estado', 'Total (Bs)', 'Fecha'];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF10B981'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        if ($this->inicio && $this->fin) {
            return 'Ventas ' . substr($this->inicio, 5) . ' al ' . substr($this->fin, 5);
        }
        return 'Reporte de Ventas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet     = $event->sheet->getDelegate();
                $lastRow   = $sheet->getHighestRow() + 1;
                $total     = $this->totalGeneral;

                $sheet->setCellValue('F' . $lastRow, 'TOTAL GENERAL (Válidos):');
                $sheet->setCellValue('G' . $lastRow, 'Bs ' . number_format($total, 2));

                $sheet->getStyle('F' . $lastRow . ':G' . $lastRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFFDE68A'],
                    ],
                ]);
            },
        ];
    }
}
