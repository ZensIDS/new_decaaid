{{--
    Kolom password dengan tombol Buat Otomatis, Salin, Tampilkan. Memakai Alpine.
    <x-admin.password-field name="password" label="Password sementara" hint="..." required />
--}}
@props(['name', 'label', 'hint' => null, 'required' => false])

<div x-data="{
        pw: '', show: false, copied: false,
        gen() {
            const set = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
            let s = '';
            do {
                const a = new Uint32Array(14);
                crypto.getRandomValues(a);
                s = Array.from(a, n => set[n % set.length]).join('');
            } while (! /[a-zA-Z]/.test(s) || ! /\d/.test(s));
            this.pw = s; this.show = true; this.copied = false;
        },
        async copy() {
            try { await navigator.clipboard.writeText(this.pw); this.copied = true; setTimeout(() => this.copied = false, 2500); } catch (e) {}
        },
     }">
    <x-admin.input :name="$name" type="password" :label="$label" :hint="$hint" :required="$required" autocomplete="new-password"
                   x-model="pw" x-bind:type="show ? 'text' : 'password'" />
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <x-admin.button size="sm" variant="secondary" icon="key" x-on:click="gen()">Buat Otomatis</x-admin.button>
        <x-admin.button size="sm" variant="ghost" x-show="pw" x-cloak x-on:click="copy()"><span x-text="copied ? 'Tersalin' : 'Salin'"></span></x-admin.button>
        <x-admin.button size="sm" variant="ghost" x-show="pw" x-cloak x-on:click="show = ! show"><span x-text="show ? 'Sembunyikan' : 'Tampilkan'"></span></x-admin.button>
    </div>
</div>