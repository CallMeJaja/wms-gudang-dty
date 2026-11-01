{{--
    Tombol aksi baris tabel master data: Edit + Hapus (soft delete).
    Hanya tampil bila pengguna berhak (Policy). Variabel:
      $model   : instance model
      $routeBase: nama route dasar, mis. 'master.barang'
      $label   : label entitas untuk konfirmasi hapus
--}}
@can('update', $model)
    <a href="{{ route($routeBase.'.edit', $model) }}" class="btn btn-sm btn-outline-primary" title="Ubah">
        <i class="bi bi-pencil"></i>
    </a>
@endcan
@can('delete', $model)
    <form method="POST" action="{{ route($routeBase.'.destroy', $model) }}" class="d-inline"
          onsubmit="return confirm('Hapus {{ $label }} ini?');">
        @csrf
        @method('DELETE')
        <button class="btn btn-sm btn-outline-danger" type="submit" title="Hapus">
            <i class="bi bi-trash"></i>
        </button>
    </form>
@endcan
