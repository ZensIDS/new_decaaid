{{--
    Kolom form berlabel mengambang (floating label), terhubung ke Alpine.

    WAJIB berada di dalam elemen ber-x-data yang menyediakan:
        values  : objek nilai kolom (values.{name})
        errors  : objek pesan galat (errors.{name})
        show(k) : true bila kolom k sudah disentuh dan punya galat
        touch(k): menandai kolom k sudah disentuh
    (contoh: Alpine.data('contactForm') di pages/contact.blade.php)

    <x-ui.float-field name="name" :label="$page['name']" icon="user" required autocomplete="name" />
    <x-ui.float-field name="message" type="textarea" :label="..." icon="chat" required maxlength="1000" />
    type: text | email | tel | textarea
--}}
@props([
    'label',
    'name',
    'type' => 'text',
    'icon' => null,
    'required' => false,
])

@php
    $isArea = $type === 'textarea';
    $input = 'peer block w-full rounded-2xl border bg-slate-50/70 text-[0.95rem] text-ink placeholder-transparent transition duration-200 hover:bg-slate-50 focus:bg-white focus:outline-none focus:ring-4 '
        .($icon ? 'pl-12 ' : 'pl-4 ').'pr-4 '
        .($isArea ? 'min-h-[9.5rem] resize-y pb-3 pt-7' : 'pb-2.5 pt-6');
    $labelPos = $icon ? 'left-12' : 'left-4';
@endphp

<div class="relative">
    @if ($isArea)
        <textarea id="{{ $name }}" name="{{ $name }}" rows="5" placeholder=" "
                  x-model="values.{{ $name }}" @blur="touch('{{ $name }}')"
                  :aria-invalid="show('{{ $name }}') ? 'true' : 'false'"
                  :class="show('{{ $name }}') ? 'border-accent-500 focus:border-accent-500 focus:ring-accent-500/15' : 'border-slate-200 focus:border-brand-500 focus:ring-brand-500/15'"
                  @required($required)
                  {{ $attributes->merge(['class' => $input]) }}></textarea>
    @else
        <input id="{{ $name }}" type="{{ $type }}" name="{{ $name }}" placeholder=" "
               x-model="values.{{ $name }}" @blur="touch('{{ $name }}')"
               :aria-invalid="show('{{ $name }}') ? 'true' : 'false'"
               :class="show('{{ $name }}') ? 'border-accent-500 focus:border-accent-500 focus:ring-accent-500/15' : 'border-slate-200 focus:border-brand-500 focus:ring-brand-500/15'"
               @required($required)
               {{ $attributes->merge(['class' => $input]) }}>
    @endif

    {{-- Ikon (setelah input agar bisa memakai peer-focus) --}}
    @if ($icon)
        <span class="pointer-events-none absolute left-4 top-[1.05rem] text-slate-400 transition duration-200 peer-focus:text-brand-600" aria-hidden="true">
            <x-ui.icon :name="$icon" class="h-5 w-5" />
        </span>
    @endif

    <label for="{{ $name }}"
           class="pointer-events-none absolute {{ $labelPos }} top-[1.05rem] text-[0.95rem] text-slate-500 transition-all duration-200
                  peer-focus:top-2 peer-focus:text-xs peer-focus:font-semibold peer-focus:text-brand-700
                  peer-[:not(:placeholder-shown)]:top-2 peer-[:not(:placeholder-shown)]:text-xs peer-[:not(:placeholder-shown)]:font-semibold">
        {{ $label }}@if ($required)<span class="text-accent-500" aria-hidden="true"> *</span>@endif
    </label>

    <p x-show="show('{{ $name }}')" x-cloak x-text="errors.{{ $name }}" role="alert"
       class="mt-2 pl-1 text-xs font-semibold text-accent-600"></p>
</div>