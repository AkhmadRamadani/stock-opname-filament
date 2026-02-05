<?php

namespace App\Exports;

use App\Models\TransaksiKeluar;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class TransaksiKeluarExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $tujuan;
    protected $kategori;

    public function __construct($startDate, $endDate, $tujuan = null, $kategori = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->tujuan = $tujuan;
        $this->kategori = $kategori;
    }

    public function query()
    {
        $query = TransaksiKeluar::with(['barang.kategori', 'userInput', 'userVerifikator'])
            ->whereBetween('tanggal_keluar', [$this->startDate, $this->endDate])
            ->where('status', 'verified');

        if ($this->tujuan) {
            $query->where('tujuan', 'like', '%' . $this->tujuan . '%');
        }

        if ($this->kategori) {
            $query->whereHas('barang.kategori', function($q) {
                $q->where('id_kategori', $this->kategori);
            });
        }

        return $query->orderBy('tanggal_keluar', 'asc');
    }

    public function map($transaksi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $transaksi->tanggal_keluar->format('d/m/Y'),
            $transaksi->kode_barang,
            $transaksi->barang->nama_barang ?? '-',
            $transaksi->barang->kategori->nama_kategori ?? '-',
            $transaksi->jumlah_keluar,
            $transaksi->barang->satuan ?? '-',
            $transaksi->barang->harga_satuan ?? 0,
            $transaksi->jumlah_keluar * ($transaksi->barang->harga_satuan ?? 0), // Total nilai
            $transaksi->tujuan,
            $transaksi->keterangan ?? '-',
            $transaksi->userInput->nama_lengkap ?? '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Jumlah',
            'Satuan',
            'Harga Satuan',
            'Total Nilai',
            'Tujuan',
            'Keterangan',
            'Input By',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E74C3C'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Transaksi Keluar';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Insert header rows
                $sheet->insertNewRowBefore(1, 4);
                
                // Merge cells untuk header
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');
                $sheet->mergeCells('A3:L3');
                
                // Set header text
                $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI BARANG KELUAR');
                $sheet->setCellValue('A2', 'DPRD KOTA BATU');
                $sheet->setCellValue('A3', 'Periode: ' . date('d/m/Y', strtotime($this->startDate)) . ' s/d ' . date('d/m/Y', strtotime($this->endDate)));
                
                // Style header
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Auto width
                foreach (range('A', 'L') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                // Border
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Number format
                $sheet->getStyle('H6:I' . $lastRow)->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Alignment
                $sheet->getStyle('A6:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('B6:B' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F6:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Summary
                $summaryRow = $lastRow + 2;
                $sheet->setCellValue('H' . $summaryRow, 'TOTAL:');
                $sheet->setCellValue('I' . $summaryRow, '=SUM(I6:I' . $lastRow . ')');
                
                $sheet->getStyle('H' . $summaryRow . ':I' . $summaryRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFFF00'],
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_MEDIUM,
                        ],
                    ],
                ]);

                $sheet->getStyle('I' . $summaryRow)->getNumberFormat()
                    ->setFormatCode('#,##0');
            },
        ];
    }
}