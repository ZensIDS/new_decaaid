{{--
    Kolom isian dengan label, petunjuk, dan pesan galat otomatis ($errors).
    <x-admin.input name="email" label="Email" type="email" :value="$user->email" required hint="..." />
    Kolom password tidak pernah diisi ulang (old/value diabaikan).
--}}
@props(['name', 'label' => null, 'type' => 'text', 'value' => null, 'hint' => null, 'required' => false])

@php
    // Nama berformat larik (mis. title[id]) dipetakan ke kunci titik (title.id) untuk old() dan galat.
    $key = str_replace(['[', ']'], ['.', ''], rtrim($name, '[]'));
    $id = $attributes->get('id', str_replace('.', '-', $key));
    $hasError = $errors->has($key);
    $current = $type === 'password' ? '' : old($key, $value);

    $classes =
        'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 px-4 py-3 text-sm text-slate-900 ring-1 ring-inset placeholder:text-slate-400 focus:ring-2 focus:ring-inset ' .
        ($hasError ? 'ring-[#E5352E] focus:ring-[#E5352E]' : 'ring-slate-200 focus:bg-white focus:ring-[#1B3590]');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}@if ($required)
                <span class="text-[#E5352E]" aria-hidden="true"> *</span>
            @endif
        </label>
    @endif

    <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ $current }}"
        @required($required) @if ($hasError) aria-invalid="true" @endif
        @if ($hasError || $hint) aria-describedby="{{ $id }}-note" @endif
        {{ $attributes->except('id')->merge(['class' => $classes]) }}>

    @if ($hasError)
        <p id="{{ $id }}-note" class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->first($key) }}</p>
    @elseif ($hint)
        <p id="{{ $id }}-note" class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>
