<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::card>
            {{ $this->form }}
        </x-filament::card>

        {{-- Summary Cards --}}
        @php
            $summary = $this->getSummary();
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Total Barang</p>
                    <p class="text-3xl font-bold text-primary-600">{{ $summary['total_barang'] }}</p>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sudah Input</p>
                    <p class="text-3xl font-bold text-blue-600">{{ $summary['total_input'] }}</p>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Sesuai (OK)</p>
                    <p class="text-3xl font-bold text-success-600">{{ $summary['total_sesuai'] }}</p>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ada Selisih</p>
                    <p class="text-3xl font-bold text-danger-600">{{ $summary['total_selisih'] }}</p>
                </div>
            </x-filament::card>

            <x-filament::card>
                <div class="text-center">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Progress</p>
                    <p class="text-3xl font-bold text-warning-600">{{ $summary['progress'] }}%</p>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                        <div class="bg-warning-600 h-2.5 rounded-full" style="width: {{ $summary['progress'] }}%"></div>
                    </div>
                </div>
            </x-filament::card>
        </div>

        {{-- Info Alert --}}
        <x-filament::card class="bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-blue-900 dark:text-blue-100">Petunjuk Stock Opname:</h4>
                    <ul class="mt-2 text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                        <li>Input <strong>Stok Fisik</strong> sesuai hasil perhitungan fisik barang</li>
                        <li>Sistem akan otomatis menghitung <strong>Selisih</strong> (Stok Fisik - Stok Sistem)</li>
                        <li><strong>Hijau</strong> = Sesuai, <strong>Kuning</strong> = Lebih, <strong>Merah</strong> =
                            Kurang</li>
                        <li>Wajib isi <strong>Keterangan</strong> jika ada selisih</li>
                        <li>Gunakan <strong>Simpan Progress</strong> untuk menyimpan sementara</li>
                        <li><strong>Finalize</strong> untuk mengunci data (tidak bisa diubah lagi)</li>
                    </ul>
                </div>
            </div>
        </x-filament::card>

        {{-- Table --}}
        <x-filament::card>
            {{ $this->table }}
        </x-filament::card>

        {{-- Legend --}}
        <x-filament::card>
            <h4 class="font-semibold mb-3">Keterangan Warna:</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div
                    class="flex items-center gap-2 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                    <div class="w-4 h-4 bg-green-500 rounded"></div>
                    <span class="text-sm"><strong>Hijau:</strong> Stok Sesuai (Selisih = 0)</span>
                </div>
                <div
                    class="flex items-center gap-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                    <div class="w-4 h-4 bg-yellow-500 rounded"></div>
                    <span class="text-sm"><strong>Kuning:</strong> Stok Lebih (Selisih Positif)</span>
                </div>
                <div
                    class="flex items-center gap-2 p-3 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <div class="w-4 h-4 bg-red-500 rounded"></div>
                    <span class="text-sm"><strong>Merah:</strong> Stok Kurang (Selisih Negatif)</span>
                </div>
            </div>
        </x-filament::card>
    </div>
</x-filament-panels::page>
