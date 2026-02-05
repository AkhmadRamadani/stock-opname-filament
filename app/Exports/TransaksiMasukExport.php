<?php

namespace App\Exports;

use App\Models\TransaksiMasuk;
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
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class TransaksiMasukExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithTitle, WithEvents
{
    protected $startDate;
    protected $endDate;
    protected $supplier;
    protected $kategori;

    public function __construct($startDate, $endDate, $supplier = null, $kategori = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->supplier = $supplier;
        $this->kategori = $kategori;
    }

    public function query()
    {
        $query = TransaksiMasuk::with(['barang.kategori', 'userInput', 'userVerifikator'])
            ->whereBetween('tanggal_masuk', [$this->startDate, $this->endDate])
            ->where('status', 'verified');

        if ($this->supplier) {
            $query->where('supplier', 'like', '%' . $this->supplier . '%');
        }

        if ($this->kategori) {
            $query->whereHas('barang.kategori', function($q) {
                $q->where('id_kategori', $this->kategori);
            });
        }

        return $query->orderBy('tanggal_masuk', 'asc');
    }

    public function map($transaksi): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $transaksi->tanggal_masuk->format('d/m/Y'),
            $transaksi->kode_barang,
            $transaksi->barang->nama_barang ?? '-',
            $transaksi->barang->kategori->nama_kategori ?? '-',
            $transaksi->jumlah_masuk,
            $transaksi->barang->satuan ?? '-',
            $transaksi->harga_beli,
            $transaksi->jumlah_masuk * $transaksi->harga_beli, // Total
            $transaksi->supplier,
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
            'Harga Beli',
            'Total Nilai',
            'Supplier',
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
                    'startColor' => ['rgb' => '4472C4'],
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
        return 'Transaksi Masuk';
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
                $sheet->setCellValue('A1', 'LAPORAN TRANSAKSI BARANG MASUK');
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

                // Border untuk semua data
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A5:L' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                // Number format untuk kolom harga
                $sheet->getStyle('H6:I' . $lastRow)->getNumberFormat()
                    ->setFormatCode('#,##0');

                // Center alignment
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