<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Notifications\Notification;
use App\Models\Barang;
use App\Models\TransaksiMasuk;
use App\Models\TransaksiKeluar;
use App\Models\LaporanStok;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Action;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StockOpnameExport;
use Filament\Forms\Components\DatePicker;

class StockOpnameInput extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string $view = 'filament.pages.stock-opname-input';

    protected static ?string $navigationLabel = 'Input Stock Opname';

    protected static ?int $navigationSort = 5;

    public $tanggal;
    public $stockOpnameData = [];

    public function mount(): void
    {
        $this->tanggal = now()->format('Y-m-d');
        $this->form->fill(['tanggal' => $this->tanggal]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Filter Periode')
                ->schema([
                    DatePicker::make('tanggal')
                        ->label('Tanggal Stock Opname')
                        ->reactive()
                        ->required()
                        ->afterStateUpdated(fn($state) => $this->tanggal = $state),
                ]),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('no')
                    ->label('No')
                    ->rowIndex(),

                TextColumn::make('kode_barang')
                    ->label('Kode Barang')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nama_barang')
                    ->label('Nama Barang')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('kategori.nama_kategori')
                    ->label('Kategori')
                    ->searchable(),

                TextColumn::make('satuan')
                    ->label('Satuan')
                    ->alignCenter(),

                TextColumn::make('stok_sistem')
                    ->label('Stok Sistem')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        return $this->getStokSistem($record->kode_barang);
                    })
                    ->badge()
                    ->color('info'),

                TextInputColumn::make('stok_fisik')
                    ->label('Stok Fisik')
                    ->type('number')
                    ->rules(['nullable', 'integer', 'min:0'])
                    ->extraAttributes(['class' => 'text-center'])
                    ->updateStateUsing(function ($record, $state) {
                        $this->stockOpnameData[$record->kode_barang]['stok_fisik'] = $state;
                    }),

                TextColumn::make('selisih')
                    ->label('Selisih')
                    ->alignCenter()
                    ->getStateUsing(function ($record) {
                        $stokSistem = $this->getStokSistem($record->kode_barang);
                        $stokFisik = $this->stockOpnameData[$record->kode_barang]['stok_fisik'] ?? null;

                        if ($stokFisik === null || $stokFisik === '') {
                            return '-';
                        }

                        return $stokFisik - $stokSistem;
                    })
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state === '-' || $state === null => 'secondary',
                        $state == 0 => 'success',
                        $state > 0 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        $stokSistem = $this->getStokSistem($record->kode_barang);
                        $stokFisik = $this->stockOpnameData[$record->kode_barang]['stok_fisik'] ?? null;

                        if ($stokFisik === null || $stokFisik === '') {
                            return 'Belum Input';
                        }

                        $selisih = $stokFisik - $stokSistem;

                        if ($selisih == 0) {
                            return 'OK';
                        } elseif ($selisih > 0) {
                            return 'Lebih';
                        } else {
                            return 'Kurang';
                        }
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'OK' => 'success',
                        'Lebih' => 'warning',
                        'Kurang' => 'danger',
                        default => 'secondary',
                    }),

                TextInputColumn::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Isi jika ada selisih')
                    ->updateStateUsing(function ($record, $state) {
                        $this->stockOpnameData[$record->kode_barang]['keterangan'] = $state;
                    }),
            ])
            ->filters([
                //
            ])
            ->paginated([10, 25, 50, 100]);
    }

    protected function getTableQuery(): Builder
    {
        return Barang::with('kategori')->orderBy('kode_barang');
    }

    protected function getStokSistem($kodeBarang)
    {
        // Get stock from LaporanStok for the selected date
        $laporan = LaporanStok::where('kode_barang', $kodeBarang)
            ->whereDate('tanggal', $this->tanggal)
            ->first();

        return $laporan->stok_akhir ?? 0;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('saveProgress')
                ->label('Simpan Progress')
                ->icon('heroicon-o-bookmark')
                ->color('warning')
                ->action(function () {
                    // Save to session with date as key
                    session(['stock_opname_' . $this->tanggal => $this->stockOpnameData]);

                    Notification::make()
                        ->title('Progress Tersimpan')
                        ->success()
                        ->send();
                }),

            Action::make('finalize')
                ->label('Finalize Stock Opname')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Finalize Stock Opname')
                ->modalDescription('Apakah Anda yakin ingin finalize stock opname? Data akan dikunci dan tidak bisa diubah.')
                ->action(function () {
                    $this->finalizeStockOpname();

                    Notification::make()
                        ->title('Stock Opname Berhasil Difinalisasi')
                        ->success()
                        ->send();
                }),

            Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->action(function () {
                    return Excel::download(
                        new StockOpnameExport($this->tanggal, $this->tanggal, $this->stockOpnameData),
                        "Stock_Opname_" . date('d-m-Y', strtotime($this->tanggal)) . ".xlsx"
                    );
                }),
        ];
    }

    protected function finalizeStockOpname()
    {
        $barangs = Barang::all();

        foreach ($barangs as $barang) {
            $stokSistem = $this->getStokSistem($barang->kode_barang);
            $stokFisik = $this->stockOpnameData[$barang->kode_barang]['stok_fisik'] ?? $stokSistem;
            $keterangan = $this->stockOpnameData[$barang->kode_barang]['keterangan'] ?? null;

            // Calculate stok_awal from previous day's stok_akhir
            $stokAwal = $this->getStokAwal($barang->kode_barang);

            // Calculate total masuk and keluar for this date
            $totalMasuk = $this->getTotalMasuk($barang->kode_barang);
            $totalKeluar = $this->getTotalKeluar($barang->kode_barang);

            // Update or create laporan stok for this date
            LaporanStok::updateOrCreate(
                [
                    'kode_barang' => $barang->kode_barang,
                    'tanggal' => $this->tanggal,
                ],
                [
                    'stok_awal' => $stokAwal,
                    'total_masuk' => $totalMasuk,
                    'total_keluar' => $totalKeluar,
                    'stok_akhir' => $stokFisik, // Use physical stock as final stock
                    'status' => 'published',
                    'keterangan' => $keterangan,
                ]
            );
        }

        // Clear session
        session()->forget('stock_opname_' . $this->tanggal);
    }

    protected function getStokAwal($kodeBarang)
    {
        // Get stock from previous day
        $tanggalSebelumnya = date('Y-m-d', strtotime($this->tanggal . ' -1 day'));

        $laporanSebelumnya = LaporanStok::where('kode_barang', $kodeBarang)
            ->whereDate('tanggal', $tanggalSebelumnya)
            ->first();

        return $laporanSebelumnya->stok_akhir ?? 0;
    }

    protected function getTotalMasuk($kodeBarang)
    {
        // Get total incoming transactions for this specific date
        return TransaksiMasuk::where('kode_barang', $kodeBarang)
            ->where('status', 'verified')
            ->whereDate('tanggal_masuk', $this->tanggal)
            ->sum('jumlah_masuk');
    }

    protected function getTotalKeluar($kodeBarang)
    {
        // Get total outgoing transactions for this specific date
        return TransaksiKeluar::where('kode_barang', $kodeBarang)
            ->where('status', 'verified')
            ->whereDate('tanggal_keluar', $this->tanggal)
            ->sum('jumlah_keluar');
    }

    public function getSummary()
    {
        $totalBarang = Barang::count();
        $totalInput = count(array_filter($this->stockOpnameData, fn($item) => isset($item['stok_fisik']) && $item['stok_fisik'] !== ''));
        $totalSesuai = 0;
        $totalSelisih = 0;

        foreach ($this->stockOpnameData as $kode => $data) {
            if (isset($data['stok_fisik']) && $data['stok_fisik'] !== '') {
                $stokSistem = $this->getStokSistem($kode);
                $selisih = $data['stok_fisik'] - $stokSistem;

                if ($selisih == 0) {
                    $totalSesuai++;
                } else {
                    $totalSelisih++;
                }
            }
        }

        return [
            'total_barang' => $totalBarang,
            'total_input' => $totalInput,
            'total_sesuai' => $totalSesuai,
            'total_selisih' => $totalSelisih,
            'progress' => $totalBarang > 0 ? round(($totalInput / $totalBarang) * 100, 2) : 0,
        ];
    }
}
