@php
    /** @var array<int, array{level: string, pesan: string}> $alerts */
    $jumlahBahaya = collect($alerts)->where('level', 'danger')->count();
    $jumlahPeringatan = collect($alerts)->where('level', 'warning')->count();
@endphp

<div style="grid-column: 1 / -1; width: 100%;">
    <x-filament::section
        heading="Perlu Perhatian"
        description="Indikator stok aktif, buffer Minggu, dan stok minimum barang."
        icon="heroicon-o-bell-alert"
    >
        @if (count($alerts) === 0)
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--gray-500);">
                <x-filament::badge color="success">Normal</x-filament::badge>
                <span>Tidak ada alert — semua indikator normal.</span>
            </div>
        @else
            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
                @if ($jumlahBahaya > 0)
                    <span style="flex: none; white-space: nowrap;">
                        <x-filament::badge color="danger">{{ $jumlahBahaya }} bahaya</x-filament::badge>
                    </span>
                @endif
                @if ($jumlahPeringatan > 0)
                    <span style="flex: none; white-space: nowrap;">
                        <x-filament::badge color="warning">{{ $jumlahPeringatan }} peringatan</x-filament::badge>
                    </span>
                @endif
            </div>

            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach ($alerts as $alert)
                    <div style="display: flex; align-items: flex-start; gap: 0.625rem; padding: 0.625rem 0.75rem; border-radius: 0.5rem; border: 1px solid {{ $alert['level'] === 'danger' ? 'var(--danger-200)' : 'var(--warning-200)' }}; background: {{ $alert['level'] === 'danger' ? 'var(--danger-50)' : 'var(--warning-50)' }};">
                        <span style="flex: none; white-space: nowrap;">
                            <x-filament::badge :color="$alert['level'] === 'danger' ? 'danger' : 'warning'">
                                {{ $alert['level'] === 'danger' ? 'Bahaya' : 'Peringatan' }}
                            </x-filament::badge>
                        </span>
                        <span style="font-size: 0.875rem; color: var(--gray-950); line-height: 1.35;">{{ $alert['pesan'] }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </x-filament::section>
</div>
