{{-- Badge status dokumen (F3). Variabel: $status (App\Enums\StatusDokumen) --}}
@php
    $warna = match ($status) {
        App\Enums\StatusDokumen::Draft => 'secondary',
        App\Enums\StatusDokumen::Posted => 'success',
        App\Enums\StatusDokumen::Issued => 'primary',
        App\Enums\StatusDokumen::Void, App\Enums\StatusDokumen::Cancelled => 'danger',
    };
@endphp
<span class="badge text-bg-{{ $warna }}">{{ $status->label() }}</span>
