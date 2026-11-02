{{--
    Saklar on/off. Selalu mengirim 1 atau 0 (tidak ada kasus "tidak terkirim").
    <x-admin.toggle name="is_active" label="Akun aktif" description="Akun nonaktif tidak bisa masuk." :checked="$user->is_active" />
--}}
@props([
    'name',
    'label',
    'description' => null,
    'checked' => false,
])

<div x-data="{ on: @js((bool) old($name, $checked)) }" class="flex items-start gap-3">
    <input type="hidden" name="{{ $name }}" :value="on ? 1 : 0">

    <button type="button" role="switch" id="toggle-{{ $name }}"
            :aria-checked="on.toString()" aria-labelledby="toggle-{{ $name }}-label"
            @click="on = ! on"
            :class="on ? 'bg-[#1B3590]' : 'bg-slate-300'"
            class="relative mt-0.5 inline-flex h-6 w-11 shrink-0 rounded-full transition focus:outline-none focus-visible:ring-2 focus-visible:ring-[#1B3590] focus-visible:ring-offset-2">
        <span :class="on ? 'translate-x-5' : 'translate-x-0.5'"
              class="mt-0.5 inline-block h-5 w-5 rounded-full bg-white shadow transition"></span>
    </button>

    <div>
        <span id="toggle-{{ $name }}-label" class="block text-sm font-medium text-slate-700">{{ $label }}</span>
        @if ($description)
            <span class="block text-xs text-slate-500">{{ $description }}</span>
        @endif
    </div>
</div>
