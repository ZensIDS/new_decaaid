{{--
    Kolom form dengan label, petunjuk, dan pesan galat.
    <x-ui.field label="Nama" name="name" required />
    <x-ui.field label="Pesan" name="message" type="textarea" hint="..." error="..." />
    Atribut lain (placeholder, value, autocomplete, dll) diteruskan ke input.
--}}
@props([
    'label',
    'name',
    'type' => 'text',
    'hint' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $describedBy = trim(($hint ? $id.'-hint ' : '').($error ? $id.'-error' : ''));
    $classes = 'mt-1.5 block w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-ink placeholder:text-slate-400 transition focus:outline-none focus:ring-2 '
        .($error
            ? 'border-accent-500 focus:border-accent-500 focus:ring-accent-500/30'
            : 'border-slate-300 focus:border-brand-500 focus:ring-brand-500/30');
    $attrs = $attributes->except('id')->merge(['class' => $classes]);
@endphp

<div>
    <label for="{{ $id }}" class="block text-sm font-semibold text-ink">
        {{ $label }}
        @if ($required)<span class="text-accent-500" aria-hidden="true">*</span>@endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="5"
                  @required($required) @if ($error) aria-invalid="true" @endif
                  @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                  {{ $attrs }}>{{ $slot }}</textarea>
    @else
        <input id="{{ $id }}" type="{{ $type }}" name="{{ $name }}"
               @required($required) @if ($error) aria-invalid="true" @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
               {{ $attrs }}>
    @endif

    @if ($hint)
        <p id="{{ $id }}-hint" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
    @if ($error)
        <p id="{{ $id }}-error" class="mt-1.5 text-xs font-semibold text-accent-600">{{ $error }}</p>
    @endif
</div>
