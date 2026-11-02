{{--
    Modal konfirmasi aksi berbahaya (hapus, nonaktifkan). Mengirim form dengan metode yang dipilih.
    Satu modal untuk banyak baris: kirim URL lewat event.
      <x-admin.confirm name="hapus" title="Hapus Pengguna?" message="Tindakan ini tidak bisa dibatalkan." button="Hapus" />
      <button @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('...', $row) }}' })">Hapus</button>
    Merah hanya untuk peringatan/hapus (tone="danger", bawaan).
--}}
@props([
    'name',
    'title',
    'message' => null,
    'button' => 'Ya, Lanjutkan',
    'method' => 'DELETE',
    'action' => null,
    'tone' => 'danger',
])

<x-admin.modal :name="$name" :title="$title" max-width="max-w-md">
    <form method="POST" :action="payload.action || @js($action)" id="confirm-{{ $name }}">
        @csrf
        @if (strtoupper($method) !== 'POST')
            @method($method)
        @endif
        @if ($message)<p>{{ $message }}</p>@endif
        {{ $slot }}
    </form>

    <x-slot name="footer">
        <x-admin.button variant="secondary" x-on:click="$dispatch('close-modal')">Batal</x-admin.button>
        <x-admin.button :variant="$tone === 'danger' ? 'danger' : 'primary'" type="submit" form="confirm-{{ $name }}">{{ $button }}</x-admin.button>
    </x-slot>
</x-admin.modal>
