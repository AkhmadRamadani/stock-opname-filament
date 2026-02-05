<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-lg font-bold tracking-tight text-gray-950 dark:text-white">
                    Shortcut Input Transaksi
                </h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Akses cepat untuk input barang masuk dan keluar.
                </p>
            </div>

            <div class="flex gap-4">
                <x-filament::button tag="a" href="{{ $this->getMasukUrl() }}" icon="heroicon-m-arrow-down-circle"
                    color="primary">
                    Input Barang Masuk
                </x-filament::button>

                <x-filament::button tag="a" href="{{ $this->getKeluarUrl() }}" icon="heroicon-m-arrow-up-circle"
                    color="danger">
                    Input Barang Keluar
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
