<x-filament::section heading="Database Accurate" description="Pilih perusahaan untuk mulai bekerja dengan data Accurate.">
    @php $databases = $this->getDatabases(); @endphp

    @if (empty($databases))
        <x-filament::empty-state>
            <x-slot name="heading">
                Belum ada database
            </x-slot>

            <x-slot name="description">
                Hubungkan Accurate lalu pilih perusahaan untuk mulai.
            </x-slot>

            <x-slot name="footer">
                <x-filament::button
                    :href="route('accurate.connect')"
                    tag="a"
                    color="primary"
                    icon="heroicon-o-link"
                    x-on:click.prevent="window.location = '{{ route('accurate.connect') }}'"
                    color="primary"
                >
                    Hubungkan Accurate
                </x-filament::button>
            </x-slot>
        </x-filament::empty-state>
    @else
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-2">
            @php $currentDbId = $this->getCurrentDatabaseId(); @endphp
            @foreach ($databases as $db)
                @php $isActive = ($currentDbId && (string) $currentDbId === (string) $db['id']); @endphp
                <x-filament::section :heading="$db['alias'] ?? 'Unknown'">
                    <x-slot name="afterHeader">
                        @if ($isActive)
                            <x-filament::button
                                size="xs"
                                color="success"
                                disabled
                            >
                                Active
                            </x-filament::button>
                        @else
                            <x-filament::button
                                wire:click="openDatabase('{{ $db['id'] }}', '{{ addslashes($db['alias']) }}')"
                                size="xs"
                                color="danger"
                                icon="heroicon-o-arrow-right-end-on-rectangle"
                            >
                                Open DB
                            </x-filament::button>
                        @endif
                    </x-slot>

                    {{-- Logo --}}
                    <div class="flex items-center gap-3 pb-4">
                        <div class="flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-gray-100 dark:bg-white/5">
                            @if ($db['logoUrl'] ?? false)
                                <img
                                    src="{{ $db['logoUrl'] }}"
                                    alt="{{ $db['alias'] }}"
                                    class="size-full object-cover"
                                    loading="lazy"
                                    onerror="this.remove()"
                                />
                            @else
                                <x-filament::icon
                                    icon="heroicon-o-building-office"
                                    class="size-5 text-gray-400 dark:text-gray-500"
                                />
                            @endif
                        </div>

                        <div class="flex flex-wrap gap-1">
                            <x-filament::badge
                                :color="$db['admin'] ? 'success' : 'gray'"
                                size="sm"
                            >
                                {{ $db['dataAccessType'] ?? 'N/A' }}
                            </x-filament::badge>
                            @if ($db['sample'] ?? false)
                                <x-filament::badge color="warning" size="sm">Sample</x-filament::badge>
                            @endif
                            @if ($db['expired'] ?? false)
                                <x-filament::badge color="danger" size="sm">Expired</x-filament::badge>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-gray-100 pt-3 dark:border-white/5">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Lisensi sampai {{ $db['licenseEnd'] ?? '-' }}
                        </span>
                    </div>
                </x-filament::section>
            @endforeach
        </div>
    @endif
</x-filament::section>
