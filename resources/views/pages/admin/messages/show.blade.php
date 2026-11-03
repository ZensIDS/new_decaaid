@extends('layouts.admin')

@section('title', 'Pesan dari ' . $message->name)

@php
    $subjectLabel = $subjects[$message->subject] ?? $message->subject;
    $reply = 'mailto:' . $message->email . '?subject=' . rawurlencode('Re: ' . $subjectLabel)
        . '&body=' . rawurlencode("\n\n---\nPada " . $message->created_at->format('d-m-Y H:i') . ', ' . $message->name . " menulis:\n" . $message->message);
@endphp

@section('content')
    <x-admin.page-header :title="'Pesan dari ' . $message->name" :description="$subjectLabel . ' · diterima ' . $message->created_at->translatedFormat('j F Y, H:i')"
                         :breadcrumbs="[['label' => 'Pesan Masuk', 'url' => route('admin.messages.index')], ['label' => 'Detail']]" />

    <div class="grid items-start gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <x-admin.form-section icon="mail" title="Isi Pesan" :description="'Subjek: ' . $subjectLabel">
                <div class="whitespace-pre-line break-words rounded-xl bg-slate-50 p-5 text-[15px] leading-7 text-slate-800 ring-1 ring-inset ring-slate-200">{{ $message->message }}</div>
            </x-admin.form-section>
        </div>

        <aside class="space-y-6 lg:sticky lg:top-24">
            <section class="overflow-hidden rounded-2xl bg-white shadow-[0_1px_2px_rgba(16,24,40,0.04),0_16px_40px_-20px_rgba(16,24,40,0.16)] ring-1 ring-slate-900/5">
                <div class="px-6 pb-6 pt-5">
                    <p class="text-base font-bold text-slate-900">{{ $message->name }}</p>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-start justify-between gap-3"><dt class="text-slate-500">Email</dt>
                            <dd class="break-all text-right font-medium text-slate-700"><a class="text-[#1B3590] hover:underline" href="mailto:{{ $message->email }}">{{ $message->email }}</a></dd></div>
                        <div class="flex items-start justify-between gap-3"><dt class="text-slate-500">Telepon</dt>
                            <dd class="text-right font-medium text-slate-700">{{ $message->phone ?: '-' }}</dd></div>
                        <div class="flex items-start justify-between gap-3"><dt class="text-slate-500">Bahasa</dt>
                            <dd class="font-medium text-slate-700">{{ strtoupper($message->locale) }}</dd></div>
                        <div class="flex items-start justify-between gap-3"><dt class="text-slate-500">Notifikasi email</dt>
                            <dd class="text-right font-medium {{ $message->notified ? 'text-emerald-700' : 'text-amber-700' }}">{{ $message->notified ? 'Terkirim' : 'Gagal (cek .env email)' }}</dd></div>
                        @if ($message->reader)
                            <div class="flex items-start justify-between gap-3"><dt class="text-slate-500">Dibaca oleh</dt>
                                <dd class="text-right font-medium text-slate-700">{{ $message->reader->name }}</dd></div>
                        @endif
                    </dl>
                </div>
                <div class="flex flex-col gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4">
                    <x-admin.button class="w-full" icon="mail" :href="$reply">Balas Lewat Email</x-admin.button>
                    <form method="POST" action="{{ route('admin.messages.toggle', $message) }}">
                        @csrf @method('PATCH')
                        <x-admin.button type="submit" variant="secondary" class="w-full">Tandai Belum Dibaca</x-admin.button>
                    </form>
                    <x-admin.button variant="ghost" class="w-full" :href="route('admin.messages.index')">Kembali</x-admin.button>
                </div>
            </section>

            <button type="button" @click="$dispatch('open-modal', { name: 'hapus', action: '{{ route('admin.messages.destroy', $message) }}' })"
                    class="flex w-full items-center justify-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-[#E5352E] hover:bg-red-50">
                <x-admin.icon name="trash" class="h-4 w-4" /> Hapus Pesan Ini
            </button>
        </aside>
    </div>

    <x-admin.confirm name="hapus" title="Hapus Pesan?" method="DELETE" button="Ya, Hapus"
                     message="Pesan dihapus permanen dari kotak masuk. Salinannya tetap ada di email info@decaa.id bila notifikasi terkirim." />
@endsection
