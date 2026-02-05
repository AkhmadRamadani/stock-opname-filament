<?php

namespace App\Exports;

use App\Models\Barang;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiKeluar;
use App\Models\LaporanStok;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StockOpnameExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithEvents
{
    protected $tanggal;
    protected $stockOpnameData;

    public function __construct($tanggal, $stockOpnameData = null)
    {
        $this->tanggal = $tanggal;
        $this->stockOpnameData = $stockOpnameData;
    }

    public function collection()
    {
        $barangs = Barang::with('kategori')->get();
        $data = new Collection();

        foreach ($barangs as $index => $barang) {
            // Ambil data dari laporan_stok untuk tanggal yang dipilih
            $laporan = LaporanStok::where('kode_barang', $barang->kode_barang)
                ->whereDate('tanggal', $this->tanggal)
                ->first();

            $stokSistem = $laporan->stok_akhir ?? 0;
            
            // Ambil stok fisik dari data yang sudah diinput (jika ada)
            $stokFisik = $this->stockOpnameData[$barang->kode_barang]['stok_fisik'] ?? '';
            $keterangan = $this->stockOpnameData[$barang->kode_barang]['keterangan'] ?? '';

            // Hitung selisih
            $selisih = $stokFisik !== '' ? ($stokFisik - $stokSistem) : '';

            $data->push([
                'no' => $index + 1,
                'kode_barang' => $barang->kode_barang,
                'nama_barang' => $barang->nama_barang,
                'kategori' => $barang->kategori->nama_kategori ?? '-',
                'satuan' => $barang->satuan,
                'stok_sistem' => $stokSistem,
                'stok_fisik' => $stokFisik,
                'selisih' => $selisih,
                'status' => $this->getStatus($selisih),
                'keterangan' => $keterangan,
            ]);
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Kategori',
            'Satuan',
            'Stok Sistem',
            'Stok Fisik',
            'Selisih',
            'Status',
            'Keterangan',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9D9D9'],
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
        return 'Stock Opname';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                foreach (range('A', 'J') as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }

                $sheet->insertNewRowBefore(1, 5);
                
                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');
                $sheet->mergeCells('A3:J3');
                
                $sheet->setCellValue('A1', 'LAPORAN STOCK OPNAME');
                $sheet->setCellValue('A2', 'DPRD KOTA BATU');
                $sheet->setCellValue('A3', 'Tanggal: ' . date('d/m/Y', strtotime($this->tanggal)));
                $sheet->setCellValue('A4', 'Tanggal Cetak: ' . now()->format('d/m/Y H:i'));
                
                $sheet->getStyle('A1:A3')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:J' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Conditional formatting untuk selisih
                for ($row = 7; $row <= $lastRow; $row++) {
                    $selisih = $sheet->getCell('H' . $row)->getValue();
                    
                    if ($selisih !== '' && $selisih !== null) {
                        if ($selisih == 0) {
                            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => '90EE90'],
                                ],
                            ]);
                        } elseif ($selisih > 0) {
                            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFFFE0'],
                                ],
                            ]);
                        } else {
                            $sheet->getStyle('A' . $row . ':J' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFB6C1'],
                                ],
                            ]);
                        }
                    }
                }

                $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F7:I' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Summary
                $summaryRow = $lastRow + 2;
                $sheet->setCellValue('A' . $summaryRow, 'SUMMARY:');
                $sheet->getStyle('A' . $summaryRow)->getFont()->setBold(true);
                
                $sheet->setCellValue('A' . ($summaryRow + 1), 'Total Barang:');
                $sheet->setCellValue('B' . ($summaryRow + 1), '=COUNTA(C7:C' . $lastRow . ')');
                
                $sheet->setCellValue('A' . ($summaryRow + 2), 'Total Sesuai:');
                $sheet->setCellValue('B' . ($summaryRow + 2), '=COUNTIF(H7:H' . $lastRow . ',0)');
                
                $sheet->setCellValue('A' . ($summaryRow + 3), 'Total Selisih:');
                $sheet->setCellValue('B' . ($summaryRow + 3), '=COUNTIF(H7:H' . $lastRow . ',"<>0")-COUNTBLANK(H7:H' . $lastRow . ')');
            },
        ];
    }

    private function getStatus($selisih)
    {
        if ($selisih === '' || $selisih === null) return '-';
        if ($selisih == 0) return 'OK';
        return $selisih > 0 ? 'Lebih' : 'Kurang';
    }
}