{{--
    <x-admin.select name="role" label="Peran" :options="['admin' => 'Admin', 'editor' => 'Editor']" :value="$user->role" />
--}}
@props([
    'name',
    'label' => null,
    'options' => [],
    'value' => null,
    'placeholder' => null,
    'hint' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $current = (string) old($name, $value);

    $classes = 'mt-1.5 block w-full rounded-xl border-0 bg-slate-50 py-3 pl-4 pr-9 text-sm text-slate-900 ring-1 ring-inset focus:ring-2 focus:ring-inset '
        .($hasError ? 'ring-[#E5352E] focus:ring-[#E5352E]' : 'ring-slate-200 focus:bg-white focus:ring-[#1B3590]');
@endphp

<div>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700">
            {{ $label }}@if ($required)<span class="text-[#E5352E]" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <select id="{{ $id }}" name="{{ $name }}" @required($required)
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->except('id')->merge(['class' => $classes]) }}>
        @if ($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach ($options as $optValue => $optLabel)
            <option value="{{ $optValue }}" @selected((string) $optValue === $current)>{{ $optLabel }}</option>
        @endforeach
    </select>

    @if ($hasError)
        <p class="mt-1.5 text-sm text-[#E5352E]">{{ $errors->first($name) }}</p>
    @elseif ($hint)
        <p class="mt-1.5 text-xs text-slate-500">{{ $hint }}</p>
    @endif
</div>